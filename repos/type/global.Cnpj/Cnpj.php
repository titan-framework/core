<?php
class Cnpj extends Phrase
{
	public function __construct ($table, $field)
	{
		parent::__construct ($table, $field);
	}
	
	public function isValid ()
	{
		if ($this->isEmpty ())
			return TRUE;
		
		$cnpj = (string) $this->getValue ();
		
		if (strlen ($cnpj) != 15 || !is_numeric ($cnpj))
			return FALSE;
		
		if ($cnpj == str_pad ('', 15, $cnpj [0]))
			return FALSE;
		
		$table = '76543298765432';
		
		$sum = 0;
		
		for ($i = 0 ; $i < 14 ; $i++)
			$sum += (int) $cnpj [$i] * (int) $table [$i];
		
		$mod = ($sum % 11);
		
		$mod = $mod < 2 ? 0 : 11 - $mod;
		
		if ($mod != (int) $cnpj [14])
			return FALSE;
		
		return TRUE;
	}
	
	public function setValue ($value)
	{
		$this->value = substr ((string) Integer::validate ($value), 0, 15);
	}
	
	public static function format ($cnpj)
	{
		$cnpj = Integer::validate ($cnpj);
		
		$tam = strlen($cnpj);
	
		$aux = substr ($cnpj, 0, 3);
		
		if($tam > 3)
			$aux .= '.'. substr ($cnpj, 3, 3);
			
		if($tam > 6)	
			$aux .= '.'. substr ($cnpj, 6, 3);
		
		if($tam > 9)
			$aux .= '/'. substr ($cnpj, 9, 4);
	
		if($tam > 13)
			$aux .= '-'. substr ($cnpj, 13, 2);
		
		return $aux;
	}
	
	public function isEmpty ()
	{
		return is_null ($this->getValue ()) || trim ($this->getValue ()) == '' || !(int) $this->getValue ();
	}
}
?>