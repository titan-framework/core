<?
class Message
{
	static private $message = FALSE;
	
	private $array = array ();
	
	private $cont = 0;
	
	const TEXT = 0;
	const HTML = 1;
	
	const WARNING = 0;
	const MESSAGE = 1;
	
	private final function __construct ()
	{
		$this->load ();
	}
	
	static public function singleton ()
	{
		if (self::$message !== FALSE)
			return self::$message;
		
		$class = __CLASS__;
		
		self::$message = new $class ();
		
		return self::$message;
	}
	
	public function save ()
	{	
		$_SESSION['CACHE_MESSAGES'] = serialize ($this->array);
	}
	
	public function load ()
	{
		if (isset ($_SESSION['CACHE_MESSAGES']))
			$this->array = unserialize ($_SESSION['CACHE_MESSAGES']);
	}
	
	public function addMessage ($message)
	{
		if (trim ($message) != '')
			$this->array [] = array (self::MESSAGE, $message);
	}
	
	public function addWarning ($warning)
	{
		if (trim ($warning) != '')
			$this->array [] = array (self::WARNING, $warning);
	}
	
	public function get ($type = self::HTML)
	{
		if (!array_key_exists ($this->cont, $this->array))
			return NULL;
		
		$key = $this->cont++;
		
		if ($type == self::TEXT)
			return $this->array [$key][1];
		
		if ($this->array [$key][0] == self::MESSAGE)
			return '<div class="cMessage">'. $this->array [$key][1] .'</div>';
		
		return '<div class="cError">'. $this->array [$key][1] .'<a class="cReport" href="#" onclick="JavaScript: bugReport (\''. str_replace (array ("'", '"'), '', strip_tags ($this->array [$key][1])) .'\');">'. __ ('Technical issue?') .'</a></div>';
	}
	
	public function has ()
	{
		return sizeof ($this->array);
	}
	
	public function clear ()
	{
		$this->array = array ();
		
		unset ($_SESSION['CACHE_MESSAGES']);
	}
}
?>