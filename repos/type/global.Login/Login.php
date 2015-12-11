<?
class Login extends Phrase
{
	public function __construct ($table, $field)
	{
		parent::__construct ($table, $field);
	}
	
	public function setValue ($value)
	{
		$this->value = self::validate ($value);
	}
	
	public static function validate ($str)
	{
		return preg_replace ('/[^0-9a-z_\-\.]/i', '', $str);
	}
}
?>