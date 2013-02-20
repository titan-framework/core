<?
class xCity
{
	public function loadCity ($stateId)
	{
		$str  = "var cityIds = new Array ();";
		$str .= "var cityNames = new Array ();";
		
		try
		{
			$db = Database::singleton ();

			$sth = $db->prepare ("SELECT * FROM _city WHERE _state = '". $stateId ."' ORDER BY _name");
			
			$sth->execute ();
			
			$count = 0;
			
			$ids = array ();
			$names = array ();
			
			while ($obj = $sth->fetch (PDO::FETCH_OBJ))
			{
				$ids [] = $obj->_id;
				$names [] = addslashes ($obj->_name);
			}
			
			$str  = "var cityIds = new Array ('". implode ("','", $ids) ."');";
			$str .= "var cityNames = new Array ('". implode ("','", $names) ."');";
		}
		catch (PDOException $e)
		{
			toLog ($e->getMessage ());
		}
		catch (Exception $e)
		{
			toLog ($e->getMessage ());
		}
		
		return $str;
	}
	
	public function showMessages ()
	{
		$message = Message::singleton ();

		if (!is_object ($message) || !$message->has ())
			return FALSE;

		$str = '';
		while ($msg = $message->get ())
			$str .= $msg;

		$msgs = &XOAD_HTML::getElementById ('labelMessage');

		$msgs->innerHTML = '<div id="idMessage">'. $str .'</div>';

		$message->clear ();

		return TRUE;
	}

	public function delay ()
	{
		sleep (1);
	}

	public function xoadGetMeta()
	{
		$methods = get_class_methods ($this);
		
		XOAD_Client::mapMethods ($this, $methods);

		XOAD_Client::publicMethods ($this, $methods);
		
		XOAD_Client::privateMethods ($this, array ());
	}
}
?>