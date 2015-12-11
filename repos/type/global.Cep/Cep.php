<?
class Cep extends Phrase
{
	public function __construct ($table, $field)
	{
		parent::__construct ($table, $field);
	}
	
	public function setValue ($value)
	{
		$this->value = substr ((string) Integer::validate ($value), 0, 8);
	}
	
	public static function format ($cep)
	{
		$cep = Integer::validate ($cep);
		
		if (strlen ($cep) > 5)
			return substr ($cep,  0, 2) .'.'. substr ($cep,  2, 3) .'-'. substr ($cep,  5, 3);
		
		if (strlen ($cep) > 2)
			return substr ($cep,  0, 2) .'.'. substr ($cep,  2, 3);
		
		return $cep;
	}
}
?>