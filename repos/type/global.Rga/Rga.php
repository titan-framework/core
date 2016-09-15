<?php
class Rga extends Phrase
{
	public function __construct ($table, $field)
	{
		parent::__construct ($table, $field);
		
		if (array_key_exists ('value-default', $field))
			$this->setValue ($field ['value-default']);
	}
	
	public function setValue ($value)
	{
		$this->value = (string) Integer::validate ($value);
	}
	
	public static function format ($rga)
	{
		$rga = Integer::validate ($rga);
		
		$tam = strlen($rga);
	
		$aux = substr ($rga, 0, 4);
		
		if($tam > 4)
			$aux .= '.'. substr ($rga, 4, 4);
			
		if($tam > 8)	
			$aux .= '.'. substr ($rga, 8, 3);
		
		if($tam > 11)
			$aux .= '-'. substr ($rga, 11, 1);
		
		return $aux;
	}
}
?>