<?
class Phone extends String
{
	public function __construct ($table, $field)
	{
		parent::__construct ($table, $field);
	}
	
	public function setValue ($value)
	{
		$this->value = substr ((string) Integer::validate ($value), 0, 10);
	}
	
	public static function format ($phone)
	{
		$phone = Integer::validate ($phone);
		
		if (strlen ($phone) > 6)
			return '('. substr ($phone,  0, 2) .') '. substr ($phone,  2, 4) .'-'. substr ($phone,  6, 4);
		
		if (strlen ($phone) > 2)
			return '('. substr ($phone,  0, 2) .') '. substr ($phone,  2, 4);
		
		return $phone;
	}
}
?>