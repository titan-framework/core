<?
class Graph
{
	private $file = '';
	
	private $primary = '';
	
	private $table = '';
	
	public $fields = array ();
	
	protected $groups = array ();
	
	protected $groupsInfo = array ();
	
	private $sth = NULL;
	
	private $default = FALSE;
	
	private $sql = FALSE;
	
	private $where = '';
	
	private $total = NULL;
	
	private $type = 'PIE';
	
	const JPGRAPH = '__JPGRAPH__';
	const GOOGLE  = '__GOOGLE__';
	
	public function __construct ()
	{
		global $section, $action;
		
		$args = func_get_args();
		
		$fileName = FALSE;
		
		if ($action->getXmlPath () !== FALSE && trim ($action->getXmlPath ()) != '')
			array_unshift ($args, $action->getXmlPath ());
		
		foreach ($args as $trash => $arg)
		{
			if (!file_exists ('section/'. $section->getName () .'/'. $arg))
				continue;
			
			$fileName = $arg;
			
			break;
		}
		
		if ($fileName === FALSE)
			throw new Exception ('Arquivo XML não encontrado em [section/'. $section->getName () .'/].');
		
		$file = 'section/'. $section->getName () .'/'. $fileName;
		
		$cacheFile = Instance::singleton ()->getCachePath () .'parsed/'. fileName ($file) .'_'. md5_file ($file) .'.php';
		
		if (file_exists ($cacheFile))
			$array = include $cacheFile;
		else
		{
			$xml = new Xml ($file);
			
			$array = $xml->getArray ();
			
			if (!isset ($array ['form'][0]))
				throw new Exception ('A tag &lt;form&gt;&lt;/form&gt; não foi encontrada no XML ['. $fileName .']!');
			
			xmlCache ($cacheFile, $array);
		}
		
		$array = $array ['form'][0];
		
		$this->file = $fileName;
		
		if (array_key_exists ('table', $array))
			$this->table = $array ['table'];
		
		if (array_key_exists ('primary', $array))
			$this->primary = $array ['primary'];
		
		$user = User::singleton ();
		
		$groupId = 0;
		
		$this->groupsInfo [$groupId] = array ();
		
		if (array_key_exists ('field', $array) && is_array ($array ['field']))
			foreach ($array ['field'] as $trash => $field)
				if ($obj = Type::factory ($this->getTable (), $field))
				{
					if (!$obj->forGraph ())
						continue;
					
					while ($perm = $obj->getRestrict ())
						if (!$user->hasPermission ($perm))
							continue 2;
					
					$this->fields [$obj->getAssign ()] = $obj;
					$this->groups [$groupId][] = $obj->getAssign ();
				}
		
		if (array_key_exists ('group', $array) && is_array ($array ['group']))
			foreach ($array ['group'] as $trash => $group)
			{
				$groupId++;
				
				if (array_key_exists ('label', $group))
					$label = $group ['label'];
				else
					$label = '';
				
				if (array_key_exists ('display', $group))
					$display = $group ['display'];
				else
					$display = 'visible';
				
				$this->groupsInfo [$groupId] = array ($groupId, $label, $display);
				
				if (array_key_exists ('field', $group) && is_array ($group ['field']))
					foreach ($group ['field'] as $trash => $field)
						if ($obj = Type::factory ($this->getTable (), $field))
						{
							if (!$obj->forGraph ())
								continue;
							
							while ($perm = $obj->getRestrict ())
								if (!$user->hasPermission ($perm))
									continue 2;
							
							$this->fields [$obj->getAssign ()] = $obj;
							$this->groups [$groupId][] = $obj->getAssign ();
						}
			}
		
		reset ($this->fields);
		reset ($this->groupsInfo);
		reset ($this->groups);
	}
	
	public function setType ($type)
	{
		$valid = array ('PIE', 'BAR');
		
		if (!in_array ($type, $valid))
			return FALSE;
		
		$this->type = $type;
		
		return TRUE;	
	}
	
	public function getType ()
	{
		return $this->type;
	}
	
	public function getFile ()
	{
		return $this->file;
	}
	
	public function getTable ()
	{
		return $this->table;
	}
	
	public function setTable ($table)
	{
		$this->table = $table;
	}
	
	public function getPrimary ()
	{
		return $this->primary;
	}
	
	public function getFields ()
	{
		return $this->fields;
	}
	
	public function getId ()
	{
		return $this->itemId;
	}
	
	public function getSth ()
	{
		return $this->sth;
	}
	
	public function getGroup ()
	{
		$group = each ($this->groupsInfo);
		
		if ($group !== FALSE)
			return new Group ($group ['value']);
		
		reset ($this->groupsInfo);
		
		return NULL;
	}
	
	public function getGraph ($assign = FALSE, $group = FALSE, $where = '', $type = self::JPGRAPH)
	{
		if ($assign !== FALSE)
		{
			if (array_key_exists ($assign, $this->fields))
				return $this->makeGraph ($this->fields [$assign], $where, $type);
			
			return NULL;
		}
		
		$field = each ($this->fields);
		
		while ($field !== FALSE)
		{
			if ($group === FALSE || (array_key_exists ($group, $this->groups) && in_array ($field ['value']->getAssign (), $this->groups [$group])))	
				return $this->makeGraph ($field ['value'], $where, $type);
			
			$field = each ($this->fields);
		}
		
		reset ($this->fields);
		
		return NULL;
	}
	
	private function makeGraph ($field, $where, $type = self::JPGRAPH)
	{
		$db = Database::singleton ();
		
		$column = $field->getColumn ();
		$title = $field->getLabel ();
		
		$unique = '_result_'. randomHash (12);
		
		$sql = "SELECT ". $field->getTable () .".". $column .", count(". $field->getTable () .".". $column .") AS ". $unique ." FROM ". $this->getTable () . (trim ($where) != '' ? " WHERE ". $where : "") . " GROUP BY ". $field->getTable () .".". $column;
		
		$sth = $db->prepare ($sql);
		
		$sth->execute ();
		
		$pieces = array ();
		$legends = array ();
		
		while ($obj = $sth->fetch (PDO::FETCH_OBJ))
		{
			if (!is_numeric ($obj->$column) && trim ($obj->$column) == '')
				continue;
			
			$field = Database::fromDb ($field, $obj);
			
			$legends [] = Form::toHtml ($field);
			$pieces [] = $obj->$unique;
		}
		
		switch ($type)
		{
			case self::GOOGLE:
				$total = array_sum ($pieces);
				
				$percent = array ();
				
				foreach ($pieces as $key => $value)
				{
					$percent [$key] = number_format ($value * 100 / $total, 1);
					$legends [$key] = urlencode (removeAccents ($legends [$key]) .' ('. $percent [$key] .'%)');
				}
				
				return 'http://chart.apis.google.com/chart?cht=p3&chd=t:'. implode (',', $percent) .'&chs=800x200&chl='. implode ('|', $legends) .'&chdl='. implode ('|', $pieces) .'&chtt='. urlencode (removeAccents ($title));
			
			default:
				foreach ($legends as $key => $value)
					$legends [$key] = urlencode ($value);
				
				return 'titan.php?target=graph&type='. $this->getType () .'&pieces[]='. implode ('&pieces[]=', $pieces) . '&title='. $title .'&legends[]='. implode ('&legends[]=', $legends);
		}
	}
}
?>