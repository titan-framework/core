<?
class Schedule
{
	static private $schedule = FALSE;
	
	private $array = array ();
	
	private final function __construct ()
	{
		$instance = Instance::singleton ();
		
		$fromXml = $instance->getSchedule ();
		
		$this->array = array ('hash' => '');
		
		foreach ($this->array as $key => $trash)
			if (array_key_exists ($key, $fromXml) && trim ($fromXml [$key]) != '')
				if (is_bool ($this->array [$key]))
					$this->array [$key] = strtoupper ($fromXml [$key]) == 'TRUE' ? TRUE : FALSE;
				else
					$this->array [$key] = trim ($fromXml [$key]);
	}
	
	static public function singleton ()
	{
		if (self::$schedule !== FALSE)
			return self::$schedule;
		
		$class = __CLASS__;
		
		self::$schedule = new $class ();
		
		return self::$schedule;
	}
	
	public function getHash ()
	{
		return $this->array ['hash'];
	}
	
	
	public static function run ($job)
	{
		$job = str_replace (array ('..', '/', '\\', DIRECTORY_SEPARATOR, ' '), '', trim ($job));
		
		if (empty ($job))
			die ('Invalid job specified!');
		
		$_START = time ();
		
		ob_start ();
		
		set_error_handler ('logPhpError');
		
		try
		{
			echo "Start runnig job... \n";
			
			self::runBySection ($job);
			
			echo "All jobs section ran! \n";
			
			$script = Instance::singleton ()->getCorePath () .'job/'. $job .'.php';
			
			if (file_exists ($script))
			{
				include $script;
				
				echo "Success to run [". $script ."]! \n";
			}
			
			echo "All done!";
		}
		catch (Exception $e)
		{
			echo $e->getMessage ();
		}
		catch (PDOException $e)
		{
			echo $e->getMessage ();
		}
		
		restore_error_handler ();
		
		$buffer = ob_get_clean ();
		
		$_END = time ();
		
		$path = Instance::singleton ()->getCachePath () .'job/';
	
		if (!file_exists ($path) && !@mkdir ($path, 0777))
			toLog ('Impossible to create folder ['. $path .'].');
	
		$fd = fopen ($path . $job .'.'. date ('Ymd'), 'a');
	
		if (!$fd)
			throw new Exception ('Impossible to open/create LOG file. Verify permissions on folder ['. $path .']!');
	
		if (!fwrite ($fd, "At ". date ('d-m-Y H:i:s') ." ran [". $_SERVER['REQUEST_URI'] ."] by [". $_SERVER['REMOTE_ADDR'] ."] in ". number_format ($_END - $_START, 0, '', '.') ." seconds:\n". $buffer ."\n\n"))
			throw new Exception ('Impossible to write in LOG file. Verify permissions on folder and file ['. $path .']!');
	
		fclose ($fd);
	}
	
	private static function runBySection ($job, $father = '')
	{
		$business = Business::singleton ();
		
		$children = $business->getChildren ($father);
		
		foreach ($children as $name => $trash)
		{
			$section = Business::singleton ()->getSection ($name);
			
			$script = $section->getCompPath () .'_job/'. $job .'.php';
			
			if (file_exists ($script))
			{
				echo "Starting ". $job ." schedule job for component [". $section->getCompPath () ."] referenced by section ". $section->getLabel ()  ." [". $section->getName ()  ."]... \n";
				
				Business::singleton ()->setCurrent ($name, '_job');
				
				include $script;
				
				echo "Success to run [". $script ."]! \n";
			}
			
			self::runBySection ($job, $name);
		}
	}
}
?>