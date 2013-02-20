<?
class Facade
{
	static private $facade = FALSE;
	
	private final function __construct ()
	{
		
	}
	
	static public function singleton ($path)
	{
		if (self::$facade !== FALSE)
			return self::$facade;
		
		$class = __CLASS__;
		
		self::$facade = new $class ($path);
		
		return self::$facade;
	}
	
}
?>