<?
class Url extends String
{
	protected $prefix = '';
	
	public function __construct ($table, $field)
	{
		parent::__construct ($table, $field);
		
		if (array_key_exists ('prefix', $field))
			$this->setPrefix ($field ['prefix']);
	}
	
	public function getPrefix ()
	{
		return $this->prefix;
	}
	
	public function setPrefix ($prefix)
	{
		$prefix = str_replace ('[default]', Instance::singleton ()->getUrl (), trim ($prefix));
		
		$this->prefix = $prefix;
	}
	
	public function isEmpty ()
	{
		return trim ($this->getValue ()) == '' || trim ($this->getValue ()) == $this->getPrefix ();
	}
}
?>