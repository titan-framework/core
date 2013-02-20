<?
class State extends Select
{
	protected $cityId =	FALSE;
	
	public function __construct ($table, $field)
	{
		$this->setLink ('_state');
		
		$this->setLinkColumn ('_uf');
		
		$this->setLinkView ('_name');
		
		parent::__construct ($table, $field);
		
		$this->setBindType (PDO::PARAM_STR);
		
		if (array_key_exists ('city-id', $field))
			$this->setCityId ($field ['city-id']);
	}	
	
	public function getCityId ()
	{
		return $this->cityId;
	}
		
	public function setCityId ($cityId)
	{
		$this->cityId = $cityId;
	}
}
?>