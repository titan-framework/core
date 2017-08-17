<?php
class CheckBox extends Enum
{
	protected $forGraph = FALSE;

	protected $value = array ();

	public function __construct ($table, $field)
	{
		$this->setSortable (FALSE);
		
		parent::__construct ($table, $field);
	}

	public function setValue ($value)
	{
		if (is_array ($value))
			$this->value = $value;
	}

	public function isEmpty ()
	{
		return sizeof ($this->value) ? FALSE : TRUE;
	}
}
?>
