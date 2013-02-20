<?
class Cascade extends Select
{
	protected $father = 'father';
	
	protected $value = NULL;
	
	public function __construct ($table, $field)
	{
		parent::__construct ($table, $field);
		
		if (array_key_exists ('link-father', $field) && trim ($field ['link-father']) != '')
			$this->father = trim ($field ['link-father']);
	}
	
	public function getFatherColumn ()
	{
		return $this->father;
	}
	
	public function isEmpty ()
	{
		return is_null ($this->getValue ());
	}
	
	public function setValue ($value)
	{
		if (!is_null ($value) && trim ($value) != '' && (!is_numeric ($value) || (int) $value !== 0))
			$this->value = $value;
		else
			$this->value = NULL;
	}
}
?>