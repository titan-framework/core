<?
class Url extends String
{
	private $prefix = '';
	
	public function __construct ($table, $field)
	{
		parent::__construct ($table, $field);
		
		if (array_key_exists ('prefix', $field))
			$this->prefix = trim ($field ['prefix']);
	}
	
	public function getPrefix ()
	{
		return $this->prefix;
	}
	
	public function isEmpty ()
	{
		return trim ($this->getValue ()) == '' || trim ($this->getValue ()) == $this->getPrefix ();
	}
}
?>