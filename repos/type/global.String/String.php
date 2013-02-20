<?
class String extends Type
{
	protected $maxLength = 0;
	
	public function __construct ($table, $field)
	{
		parent::__construct ($table, $field);
		
		$this->setBind (TRUE);
		
		$this->setBindType (PDO::PARAM_STR);
		
		if (array_key_exists ('value-default', $field))
			$this->setValue (self::validate ($field ['value-default']));
		
		if (array_key_exists ('max-length', $field))
			$this->setMaxLength ($field ['max-length']);
	}
	
	public function setValue ($value)
	{
		$this->value = (string) $value;
	}
	
	public function setMaxLength ($maxLength)
	{
		$this->maxLength = $maxLength;
	}
	
	public function getMaxLength ()
	{
		return $this->maxLength;
	}
	
	public function isEmpty ()
	{
		if (trim ($this->getValue ()) == '')
			return TRUE;
		
		return FALSE;
	}
	
	public static function validate ($str)
	{
		$pattern = "/((text|font)-?)?(family|face|size)[ ]*[:=][ ]*(('[^'>]*')|(\"[^\">]*\")|([^ >]*)|([^;>\"']*;))/i";
		
		$str = preg_replace ($pattern,'', $str);
		
		$pattern = "/class[ ]*[:=][ ]*(('[^'>]*')|(\"[^\">]*\")|([^ >]*))/i";
		
		$str = preg_replace ($pattern,'class="font"', $str);	
		
		$str = preg_replace ('/<[ ]*font[ ]*>/i','<font class="font">', $str);	
	
		$str = preg_replace ('/<[ ]*H[0-9]+/i', '<span class="font"', $str);
		$str = preg_replace ('/<[ ]*/[ ]*H[0-9]+/i', '</span', $str);
		$str = stripslashes ($str);
	
		return $str;
	}
	
	public static function limit ($text, $max)
	{
		if($max <= 3 || strlen ($text) <= $max)
			return $text;
		
		$text = substr (strip_tags ($text), 0, $max - 2);
		
		$pos1 = strrpos ($text, ' ');
		$pos2 = strrpos ($text, ',');
		$pos3 = strrpos ($text, ';');
		$pos4 = strrpos ($text, '.');
		
		$pos = min ($pos1, $pos2, $pos3, $pos4);
		
		if ($pos !== false)
			$text = substr ($text, 0, $pos);
		
		return $text .'...';
	}
	
	public static function purify ($text)
	{
		if (!class_exists ('HTMLPurifier', FALSE))
			return $text;
		
		set_error_handler ('logPhpError');
		
		$config = HTMLPurifier_Config::createDefault ();
		
		$config->set ('Core.Encoding', 'UTF-8');
		$config->set ('HTML.Doctype', 'XHTML 1.0 Transitional');
		$config->set ('Attr.EnableID', TRUE);
		$config->set ('Attr.AllowedFrameTargets', '_blank');
		
		if (!Instance::singleton ()->onDebugMode ())
		{
			$path = Instance::singleton ()->getCachePath () .'purifier';
			
			if (!file_exists ($path) && !@mkdir ($path, 0777))
				throw new Exception ('Impossível criar diretório ['. $path .'].');
			
			$config->set ('Cache.SerializerPath', $path);
		}
		else
			$config->set ('Cache.DefinitionImpl', NULL);
		
		$purifier = new HTMLPurifier ($config);
		
		restore_error_handler ();
		
		return $purifier->purify ($text);
	}
}
?>