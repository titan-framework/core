<?php
class Shopping
{
	static private $shopping = FALSE;
	
	static private $active = NULL;
	
	static private $currencies = array ('BRL' => 'R$',
										'USD' => 'US$',
										'EUR' => '€');
	
	private $currency = 'BRL';
	
	private $gateways = array ();
	
	private $defaultGateway = NULL;
	
	static private $driversEnabled = array ('PagSeguro');
	
	private final function __construct ()
	{
		$array = Instance::singleton ()->getShopping ();
		
		if (array_key_exists ('currency', $array) && array_key_exists (trim ($array ['currency']), self::$currencies))
			$this->currency = trim ($array ['currency']);
		
		if (!array_key_exists ('xml-path', $array))
			throw new Exception ('Not located [xml-path] attribute on &lt;shopping&gt;&lt;/shopping&gt; tag in file [configure/titan.xml]!');
		
		$file = $array ['xml-path'];
		
		$cacheFile = Instance::singleton ()->getCachePath () .'parsed/'. fileName ($file) .'_'. md5_file ($file) .'.php';
		
		if (file_exists ($cacheFile))
			$array = include $cacheFile;
		else
		{
			$xml = new Xml ($file);
			
			$array = $xml->getArray ();
			
			$array = $array ['pay-mapping'][0];
			
			xmlCache ($cacheFile, $array);
		}
		
		if (array_key_exists ('gateway', $array))
			foreach ($array ['gateway'] as $trash => $gw)
			{
				if (!array_key_exists ('driver', $gw) || !in_array ($gw ['driver'], self::$driversEnabled) || !array_key_exists ('user-view', $gw) || trim ($gw ['user-view']) == '' ||
					!array_key_exists ('account', $gw) || trim ($gw ['account']) == '' || !array_key_exists ('active', $gw) || strtoupper (trim ($gw ['active'])) != 'TRUE')
					continue;
				
				$this->gateways [$gw ['driver'] .'#'. $gw ['account']] = $gw;
				
				if (array_key_exists ('default', $gw) && strtoupper (trim ($gw ['default'])) == 'TRUE')
					$this->defaultGateway = $gw ['driver'] .'#'. $gw ['account'];
			}
		
		reset ($this->gateways);
		
		if (is_null ($this->defaultGateway) && sizeof ($this->gateways))
			$this->defaultGateway = key ($this->gateways);
	}
	
	static public function singleton ()
	{
		if (self::$shopping !== FALSE)
			return self::$shopping;
		
		$class = __CLASS__;
		
		self::$shopping = new $class ();
		
		return self::$shopping;
	}
	
	public function getGateways ()
	{
		return $this->gateways;
	}
	
	public function getGateway ($id)
	{
		if (!array_key_exists ($id, $this->gateways))
			return NULL;
		
		return $this->gateways [$id];
	}
	
	public function getShoppingCartItems ($userId)
	{
		if (!is_integer ($userId))
			return array ();
		
		$sql = "SELECT _id, _description, _quantity, _value FROM _shopping WHERE _payment IS NULL AND _value > 0 AND _user = :user";
		
		$sth = Database::singleton ()->prepare ($sql);
		
		$sth->execute (array (':user' => $userId));
		
		$array = array ();
		
		while ($obj = $sth->fetch (PDO::FETCH_OBJ))
		{
			$array [$obj->_id]['_DESCRIPTION_'] = $obj->_description;
			$array [$obj->_id]['_QUANTITY_'] = (int) $obj->_quantity;
			$array [$obj->_id]['_VALUE_'] = (float) $obj->_value;
		}
		
		return $array;
	}
	
	public function getNumberOfItemsInShoppingCart ($userId)
	{
		$sql = "SELECT COUNT(*) AS n FROM _shopping WHERE _payment IS NULL AND _value > 0 AND _user = :user";
		
		$sth = Database::singleton ()->prepare ($sql);
		
		$sth->execute (array (':user' => $userId));
		
		$obj = $sth->fetch (PDO::FETCH_OBJ);
		
		echo (int) $obj->n;
	}
	
	public function getCurrency ()
	{
		return $this->currency;
	}
	
	public function getCurrencySymbol ()
	{
		return self::$currencies [$this->currency];
	}
	
	public function deleteByOwner ($id, $user)
	{
		try
		{
			$sth = Database::singleton ()->prepare ("DELETE FROM _shopping WHERE _id = :id AND _payment IS NULL AND _user = :user");
			
			$sth->bindParam (':id', $id, PDO::PARAM_INT);
			$sth->bindParam (':user', $user, PDO::PARAM_INT);
			
			$sth->execute ();
		}
		catch (PDOException $e)
		{
			toLog ($e->getMessage ());
			
			return FALSE;
		}
		
		return TRUE;
	}
	
	public static function add ($relation, $table, $description, $value, $quantity, $gateway = NULL)
	{
		if (!self::isActive ())
			return FALSE;
		
		return self::singleton ()->register ($relation, $table, $description, $value, $quantity, $gateway, User::singleton ()->getId ());
	}
	
	private function register ($relation, $table, $description, $value, $quantity, $gateway, $user)
	{
		if (!is_integer ($relation) || trim ((string) $table) == '' || trim ((string) $description) == '' || 
			!is_integer ($quantity) || !is_integer ($user) || (!is_integer ($value) && !is_float ($value)) || 
			$relation <= 0 || $value < 0 || $quantity <= 0 || $user <= 0)
			return FALSE;
		
		$db = Database::singleton ();
		
		$sth = $db->prepare ("SELECT s._id FROM ". $table ." r JOIN _shopping s ON r._shopping = s._id WHERE r._relation = :id AND s._user = :user AND s._payment IS NULL");
		
		$sth->execute (array (':id' => $relation, ':user' => $user));
		
		$shopId = $sth->fetchColumn ();
		
		if (!empty ($shopId))
		{
			if (!is_integer ($shopId) || $shopId <= 0)
				return FALSE;
			
			try
			{
				$sth = $db->prepare ("UPDATE _shopping SET _quantity = _quantity + 1, _update = NOW() WHERE _id = :id AND _user = :user AND _payment IS NULL");
				
				$sth->execute (array (':id' => $shopId, ':user' => $user));
				
				return TRUE;
			}
			catch (PDOException $e)
			{
				toLog ($e->getMessage ());
				
				return FALSE;
			}
		}
		
		if (is_null ($gateway) || empty ($gateway))
			$gateway = $this->defaultGateway;
		
		$gw = $this->getGateway ($gateway);
		
		if (!is_array ($gw))
			return FALSE;
		
		$shopId = $db->nextId ('_shopping', '_id');
		
		$sth1 = $db->prepare ("INSERT INTO _shopping (_id, _user, _description, _quantity, _value, _account, _driver) VALUES (:i, :u, :d, :q, :v, :ag, :dg)");
		
		$sth2 = $db->prepare ("INSERT INTO ". $table ." (_shopping, _relation) VALUES (:s, :r)");
		
		try
		{
			$db->beginTransaction ();
			
			$sth1->execute (array (':i' => $shopId, ':u' => $user, ':d' => $description, ':q' => $quantity, ':v' => $value, ':ag' => $gw ['account'], ':dg' => $gw ['driver']));
			
			$sth2->execute (array (':s' => $shopId, ':r' => $relation));
			
			$db->commit ();
			
			return TRUE;
		}
		catch (PDOException $e)
		{
			$db->rollBack ();
			
			toLog ($e->getMessage ());
		}
		
		return FALSE;
	}
	
	public static function remove ($template, $assign)
	{
		if (!self::isActive ())
			return FALSE;
	}
	
	public static function isActive ()
	{
		if (is_null (self::$active))
			self::$active = sizeof (Instance::singleton ()->getShopping ()) && Database::tableExists ('_payment') && Database::tableExists ('_shopping');
		
		return self::$active;
	}
}
?>