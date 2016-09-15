<?php
class Time extends Type
{
	protected $value = array (-1, -1, -1);
	
	public function __construct ($table, $field)
	{
		parent::__construct ($table, $field);
		
		$this->setBind (TRUE);
		
		$this->setBindType (PDO::PARAM_STR);
		
		if (array_key_exists ('value-default', $field))
			$this->setValue (explode (':', $field ['value-default']));
		
		if (array_key_exists ('value', $field))
			$this->setValue (explode (':', $field ['value']));
	}
	
	public function setValue ($value)
	{
		if (!is_array ($value))
		{
			$value = explode (':', $value);
			
			array_walk ($value, array ($this, 'toInteger'));
		}
		
		$this->value = $value;
	}
	
	public function isEmpty ()
	{
		return array_sum ($this->getValue ()) < 0;
	}
	
	public function __toString ()
	{
		$aux = $this->getValue ();
		
		array_walk ($aux, array ($this, 'toString'));
		
		return implode (':', $aux);
	}
	
	public static function toInteger (&$item, $key)
	{
		$item = (int) $item;
	}
	
	public static function toString (&$item, $key)
	{
		$item = $item < 10 ? '0'. (string) $item : (string) $item;
	}
}
?>