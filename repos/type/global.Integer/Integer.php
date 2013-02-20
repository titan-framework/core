<?
class Integer extends Type
{
	public function __construct ($table, $field)
	{
		parent::__construct ($table, $field);
		
		$this->setBind (TRUE);
		
		$this->setBindType (PDO::PARAM_INT);
		
		if (array_key_exists ('value-default', $field))
			$this->setValue (self::validate ($field ['value-default']));
		
		if (array_key_exists ('value', $field))
			$this->setValue (self::validate ($field ['value']));
	}
	
	public function setValue ($value)
	{
		$this->value = (int) $value;
	}
	
	public static function validate ($str)
	{
		return preg_replace ('/[^0-9]/i', '', $str);
	}
	
	public function isEmpty ()
	{
		return is_null ($this->getValue ()) || !is_numeric ($this->getValue ());
	}
}
?>