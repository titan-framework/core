<?
class Select extends Type
{
	protected $link = '';
	
	protected $linkColumn = '';
	
	protected $linkApi = '';
	
	protected $linkView = '';
	
	protected $columns = array ();
	
	protected $where = '';
	
	protected $forGraph = TRUE;
	
	protected $search = FALSE;
	
	protected $fastSearch = FALSE;
	
	protected $value = NULL;
	
	protected $linkColor = '';
	
	public function __construct ($table, $field)
	{
		parent::__construct ($table, $field);
		
		$this->setBind (TRUE);
		
		$this->setBindType (PDO::PARAM_INT);
		
		if (array_key_exists ('link-table', $field))
			$this->setLink ($field ['link-table']);
			
		if (array_key_exists ('link-column', $field))
			$this->setLinkColumn ($field ['link-column']);
		
		if (array_key_exists ('link-view', $field))
			$this->setLinkView ($field ['link-view']);
		
		if (array_key_exists ('link-color', $field))
			$this->setLinkColor ($field ['link-color']);
		
		if (array_key_exists ('link-api', $field) && trim ($field ['link-api']) != '')
			$this->setLinkApi ($field ['link-api']);
		else
			$this->setLinkApi ($this->getLinkColumn ());
		
		if (array_key_exists ('search', $field))
			$this->setSearch ($field ['search']);
		
		if (array_key_exists ('fast-search', $field))
			$this->fastSearch = strtoupper (trim ($field ['fast-search'])) == 'TRUE' ? TRUE : FALSE;
	}
	
	public function getLink ()
	{
		return $this->link;
	}
	
	public function setLink ($link)
	{
		$this->link = $link;
	}
	
	public function getLinkTable ()
	{
		return $this->getLink ();
	}
	
	public function setLinkTable ($link)
	{
		$this->setLink ($link);
	}
	
	public function getLinkColumn ()
	{
		return $this->linkColumn;
	}
	
	public function setLinkColumn ($linkColumn)
	{
		$this->linkColumn = $linkColumn;
	}
	
	public function setLinkView ($linkView)
	{
		$reg = '/\[(.*?)\]/s';

		preg_match_all ($reg, $linkView, $match);
		
		if (is_array ($match) && array_key_exists (1, $match) && sizeof ($match [1]))
		{
			$this->columns = $match [1];
			
			$this->linkView = $linkView;
		}
		else
		{
			$this->columns = array ($linkView);
			
			$this->linkView = '['. $linkView .']';
		}
	}
	
	public function getLinkView ()
	{
		return $this->linkView;
	}
	
	public function getColumnsView ()
	{
		return $this->columns;
	}
	
	public function getLinkColor ()
	{
		return $this->linkColor;
	}
	
	public function setLinkColor ($linkColor)
	{
		$this->linkColor = $linkColor;
	}
	
	public function getLinkApi ()
	{
		return $this->linkApi;
	}
	
	public function setLinkApi ($linkApi)
	{
		$this->linkApi = $linkApi;
	}
	
	public function setWhere ($where)
	{
		$this->where = $where;
	}
	
	public function getWhere ()
	{
		return $this->where;
	}
	
	public function setSearch ($path)
	{
		if (trim ($path) != '')
			$this->search = $path;
	}
	
	public function useSearch ()
	{
		return $this->search !== FALSE;
	}
	
	public function setFastSearch ($fs)
	{
		if (is_bool ($fs))
			$this->fastSearch = $fs;
	}
	
	public function useFastSearch ()
	{
		return $this->fastSearch;
	}
	
	public function getSearch ()
	{
		return $this->search;
	}
	
	public function makeView ($obj)
	{
		$columns = $this->getColumnsView ();
		
		$text = $this->getLinkView ();
		
		foreach ($columns as $key => $column)
			$text = str_replace ('['. $column .']', $obj->$column, $text);
		
		return $text;
	}
	
	public function setValue ($value)
	{
		if (is_null ($value) || (is_numeric ($value) && (int) $value === 0) || (is_string ($value) && $value === ''))
			$this->value = NULL;
		else
			$this->value = $value;
	}
	
	public function isEmpty ()
	{
		return is_null ($this->getValue ()) || (is_numeric ($this->getValue ()) && (int) $this->getValue () === 0) || (is_string ($this->getValue ()) && $this->getValue () === '');
	}
}
?>