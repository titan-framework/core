<?php
class Passport extends Phrase
{
	public function __construct ($table, $field)
	{
		parent::__construct ($table, $field);
	}
	
	public function isEmpty ()
	{
		return is_null ($this->getValue ()) || trim ($this->getValue ()) == '' || !(int) $this->getValue ();
	}
}
?>