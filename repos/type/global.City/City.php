<?
class City extends Select
{
	protected $stateId = NULL;
	
	public function __construct ($table, $field)
	{
		$this->setLink ('_city');
		
		$this->setLinkColumn ('_id');
		
		$this->setLinkView ('_name');
		
		if (array_key_exists ('uf', $field))
			$this->setState ($field ['uf']);
		
		parent::__construct ($table, $field);
	}
	
	public function setState ($state)
	{	
		$this->stateId = substr ((string) $state, 0, 2);
	}
	
	public function getState ()
	{
		return $this->stateId;
	}
}
?>