<?
class Email extends String
{
	public function __construct ($table, $field)
	{
		parent::__construct ($table, $field);
	}
	
	public function setValue ($value)
	{
		$this->value = strtolower ($value);
	}
	
	public function isValid ()
	{
		if ($this->isEmpty ())
			return TRUE;
		
		set_error_handler ('logPhpError');
		
		@include_once 'Zend/Validate/EmailAddress.php';
		
		if (!class_exists ('Zend_Validate_EmailAddress', FALSE))
			return TRUE;
		
		$validator = new Zend_Validate_EmailAddress (array ('allow' => Zend_Validate_Hostname::ALLOW_DNS, 'mx' => TRUE));
		
		restore_error_handler ();
		
		if ($validator->isValid ($this->getValue ()))
			return TRUE;
		
		foreach ($validator->getMessages () as $trash => $message)
			toLog ($message);
		
		return FALSE;
	}
}
?>