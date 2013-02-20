<?
class Fck extends String
{
	public function __construct ($table, $field)
	{
		parent::__construct ($table, $field);
		
		if (array_key_exists ('value-default', $field))
			$this->setValue ($field ['value-default']);
	}
}
?>