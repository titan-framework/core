<?
class Cpf extends String
{
	public function __construct ($table, $field)
	{
		parent::__construct ($table, $field);
	}
	
	public function isValid ()
	{
		if ($this->isEmpty ())
			return TRUE;
		
		$cpf = (string) $this->getValue ();
		
		if (strlen ($cpf) != 11 || !is_numeric ($cpf))
			return FALSE;
		
		if ($cpf == str_pad ('', 11, $cpf [0]))
			return FALSE;
		
		$soma = 0;
		
		for ($i = 0 ; $i < 9 ; $i++)
			$soma += ((10 - $i) * $cpf [$i]);
		
		$d1 = ($soma % 11);
		
		$d1 = $d1 < 2 ? 0 : 11 - $d1;
		
		if ($d1 != (int) $cpf [9])
			return FALSE;
		
		$soma = 0;
		
		for ($i = 0 ; $i < 10 ; $i++)
			$soma += ((11 - $i) * $cpf [$i]);
		
		$d2 = ($soma % 11);
		
		$d2 = $d2 < 2 ? 0 : 11 - $d2;
		
		if ($d2 != (int) $cpf [10])
			return FALSE;
		
		return TRUE;
	}
	
	public function setValue ($value)
	{
		$this->value = substr ((string) Integer::validate ($value), 0, 11);
	}
	
	public static function format ($cpf)
	{
		$cpf = Integer::validate ($cpf);
		
		$tam = strlen ($cpf);
	
		$aux = substr ($cpf, 0, 3);
		
		if($tam > 3)
			$aux .= '.'. substr ($cpf, 3, 3);
			
		if($tam > 6)	
			$aux .= '.'. substr ($cpf, 6, 3);
		
		if($tam > 9)
			$aux .= '-'. substr ($cpf, 9, 2);
		
		return $aux;
	}
	
	public function isEmpty ()
	{
		return is_null ($this->getValue ()) || trim ($this->getValue ()) == '' || !(int) $this->getValue ();
	}
}
?>