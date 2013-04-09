<?
class Boolean extends Integer
{
	protected $forGraph = TRUE;
	
	protected $question = FALSE;
	
	public function __construct ($table, $field)
	{
		parent::__construct ($table, $field);
		
		$this->setBindType (PDO::PARAM_STR);
		
		if (array_key_exists ('value', $field))
			$this->setValue ($field ['value']);
		
		if (array_key_exists ('question', $field))
			$this->question = strtoupper (trim ($field ['question'])) == 'TRUE' ? TRUE : FALSE;
	}
	
	public function setValue ($value)
	{
		if (is_bool ($value))
			$this->value = $value;
		elseif (!is_numeric ($value))
			$this->value = strtoupper ($value) == 'TRUE' ? TRUE : FALSE;
		elseif ((int) $value < 0)
			$this->value = NULL;
		else
			$this->value = (int) $value ? TRUE : FALSE;
	}
	
	public function isQuestion ()
	{
		return $this->question;
	}
	
	public function isEmpty ()
	{
		return is_null ($this->value);
	}
}
?>