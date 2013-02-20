<?
class Float extends Type
{
	protected $precision = 2;
	
	public function __construct ($table, $field)
	{
		parent::__construct ($table, $field);
		
		$this->setBind (TRUE);
		
		$this->setBindType (PDO::PARAM_STR);
		
		if (array_key_exists ('value-default', $field))
			$this->setValue (self::validate ($field ['value-default']));

		if (array_key_exists ('precision', $field))
			$this->setPrecision (Integer::validate ($field ['precision']));
	}
	
	public function setValue ($value)
	{
		$this->value = (float) $value;
	}

	public function setPrecision ($precision)
	{
		$this->precision = (int) $precision;
	}
	
	public function getPrecision()
	{
		return $this->precision;
	}

	public static function validate ($str)
	{
		return preg_replace ('/[^0-9.]/i', '', $str);
	}
}
?>