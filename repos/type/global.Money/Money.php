<?php
class Money extends Double
{
	protected $currency = 'R$';
	
	public function __construct ($table, $field)
	{
		parent::__construct ($table, $field);
		
		if (array_key_exists ('value-default', $field))
			$this->setValue ($field ['value-default']);
		
		if (array_key_exists ('currency', $field))
			$this->setCurrency ($field ['currency']);
	}
	
	public function setValue ($value)
	{
		$this->value = (float) $value;
	}
	
	public function setCurrency ($value)
	{
		$this->currency = $value;
	}
	
	public function getCurrency ()
	{
		return $this->currency;
	}
}
?>