<?php
class Phrase extends Type
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
		$config->set ('HTML.Doctype', 'HTML 4.01 Transitional');
		$config->set ('CSS.AllowTricky', TRUE);
		$config->set ('Attr.EnableID', TRUE);
		$config->set ('Attr.AllowedFrameTargets', '_blank');
		$config->set ('Output.FlashCompat', TRUE);
		$config->set ('HTML.SafeEmbed', TRUE);
		$config->set ('HTML.SafeObject', TRUE);
		$config->set ('HTML.DefinitionID', 'html5-definitions');
		$config->set ('HTML.DefinitionRev', 1);
		
		if (!Instance::singleton ()->onDebugMode ())
		{
			$path = Instance::singleton ()->getCachePath () .'purifier';
			
			if (!file_exists ($path) && !@mkdir ($path, 0777))
				throw new Exception ('Impossível criar diretório ['. $path .'].');
			
			$config->set ('Cache.SerializerPath', $path);
		}
		else
			$config->set ('Cache.DefinitionImpl', NULL);
		
		if ($def = $config->maybeGetRawHTMLDefinition ())
		{
			$def->addElement ('section', 'Block', 'Flow', 'Common');
			$def->addElement ('nav',     'Block', 'Flow', 'Common');
			$def->addElement ('article', 'Block', 'Flow', 'Common');
			$def->addElement ('aside',   'Block', 'Flow', 'Common');
			$def->addElement ('header',  'Block', 'Flow', 'Common');
			$def->addElement ('footer',  'Block', 'Flow', 'Common');
			
			$def->addElement ('address', 'Block', 'Flow', 'Common');
			$def->addElement ('hgroup', 'Block', 'Required: h1 | h2 | h3 | h4 | h5 | h6', 'Common');
			
			$def->addElement ('figure', 'Block', 'Optional: (figcaption, Flow) | (Flow, figcaption) | Flow', 'Common');
			$def->addElement ('figcaption', 'Inline', 'Flow', 'Common');
			
			$def->addElement ('video', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', array (
				'src' => 'URI',
				'type' => 'Text',
				'width' => 'Length',
				'height' => 'Length',
				'poster' => 'URI',
				'preload' => 'Enum#auto,metadata,none',
				'controls' => 'Text',
			));
			
			$def->addElement ('audio', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', array (
				'src' => 'URI',
				'type' => 'Text',
				'width' => 'Length',
				'height' => 'Length',
				'poster' => 'URI',
				'preload' => 'Enum#auto,metadata,none',
				'controls' => 'Text',
			));
			
			$def->addElement ('source', 'Block', 'Flow', 'Common', array (
				'src' => 'URI',
				'type' => 'Text',
			));
			
			$def->addElement ('s',    'Inline', 'Inline', 'Common');
			$def->addElement ('var',  'Inline', 'Inline', 'Common');
			$def->addElement ('sub',  'Inline', 'Inline', 'Common');
			$def->addElement ('sup',  'Inline', 'Inline', 'Common');
			$def->addElement ('mark', 'Inline', 'Inline', 'Common');
			$def->addElement ('wbr',  'Inline', 'Empty', 'Core');
			
			$def->addElement ('ins', 'Block', 'Flow', 'Common', array('cite' => 'URI', 'datetime' => 'CDATA'));
			$def->addElement ('del', 'Block', 'Flow', 'Common', array('cite' => 'URI', 'datetime' => 'CDATA'));
			
			$def->addAttribute ('table', 'height', 'Text');
			$def->addAttribute ('td', 'border', 'Text');
			$def->addAttribute ('th', 'border', 'Text');
			$def->addAttribute ('tr', 'width', 'Text');
			$def->addAttribute ('tr', 'height', 'Text');
			$def->addAttribute ('tr', 'border', 'Text');
		}
		
		$purifier = new HTMLPurifier ($config);
		
		restore_error_handler ();
		
		return $purifier->purify ($text);
	}
}
?>