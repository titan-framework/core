<?php
/**
 * This class load XML definitions files and instanciate a view artefact.
 * View artefact is itens list (list of arrays), and a item is fields list
 * (list of Type object).
 *
 * The choosed methodology assure only one fields list. If do have N objects
 * as result from query, this database objects will be loaded in View class on
 * demand.
 *
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @category class
 * @package core
 * @subpackage form
 * @copyright 2005-2017 Titan Framework
 * @license http://www.titanframework.com/license/ BSD License (3 Clause)
 * @see Form, Search
 * @todo Create Item class for array replace.
 */
class View
{
	protected $file = '';

	protected $primary = '';

	protected $table = '';

	protected $paginate = 0;

	protected $sortable = FALSE;

	protected $order = array ();

	public $fields = array ();

	protected $sth = NULL;

	protected $icons = array ();

	protected $disabledIcons = array ();

	protected $default = FALSE;

	protected $itemId = 0;

	protected $page = 0;

	protected $sql = FALSE;

	protected $where = '';

	protected $total = NULL;

	public function __construct ()
	{
		$section = Business::singleton ()->getSection (Section::TCURRENT);

		$action = Business::singleton ()->getAction (Action::TCURRENT);

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

			if (!isset ($array ['view'][0]))
				throw new Exception ('A tag &lt;view&gt;&lt;/view&gt; não foi encontrada no XML ['. $fileName .']!');

			xmlCache ($cacheFile, $array);
		}

		if (!array_key_exists ('view', $array))
			throw new Exception ('Invalid XML View file [section/'. $section->getName () .'/].');

		$array = $array ['view'][0];

		$this->file = $fileName;

		if (array_key_exists ('table', $array))
			$this->table = $array ['table'];

		if (array_key_exists ('primary', $array))
			$this->primary = $array ['primary'];

		if (array_key_exists ('paginate', $array))
			$this->paginate = $array ['paginate'];

		$this->sortable = array_key_exists ('sortable', $array) && strtoupper ($array ['sortable']) == 'TRUE' ? TRUE : FALSE;

		$user = User::singleton ();

		if (array_key_exists ('field', $array) && is_array ($array ['field']))
			foreach ($array ['field'] as $trash => $field)
				if ($obj = Type::factory ($this->getTable (), $field))
				{
					while ($perm = $obj->getRestrict ())
						if (!$user->hasPermission ($perm))
							continue 2;

					$this->fields [$obj->getAssign ()] = $obj;
				}

		if (isset ($_GET['order']) && trim ($_GET['order']) != '')
			$this->order [] = array (trim ($_GET['order']), (isset ($_GET['invert']) && $_GET['invert'] == 1 ? TRUE : FALSE));
		elseif (array_key_exists ('order', $array))
			foreach ($array ['order'] as $trash => $order)
			{
				if (!array_key_exists ('id', $order))
					continue;

				if (!array_key_exists (trim ($order ['id']), $this->fields))
					continue;

				$this->order [] = array (trim ($order ['id']), (array_key_exists ('invert', $order) && strtoupper ($order ['invert']) == 'TRUE' ? TRUE : FALSE));
			}

		if (array_key_exists ('icon', $array))
			foreach ($array ['icon'] as $trash => $icon)
			{
				if (array_key_exists ('id', $icon) && trim ($icon ['id']) != '')
					$this->icons [$icon ['id']] = $icon;
				else
					$this->icons [] = $icon;

				if (array_key_exists ('default', $icon) && strtoupper ($icon ['default']) == 'TRUE')
					$this->default = key ($this->icons);
			}

		reset ($this->fields);
		reset ($this->icons);
	}

	public function getPaginate()
	{
		return $this->paginate;
	}

	public function setPaginate ($paginate)
	{
		$this->paginate = (int) $paginate;
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

	public function getSql ()
	{
		return $this->sql;
	}

	public function getOrder ()
	{
		$aux = array ();
		foreach ($this->order as $trash => $order)
			$aux [] = Database::toOrder ($this->fields [$order [0]]) . ($order [1] ? ' DESC' : '');

		return $aux;
	}

	public function isSortable ()
	{
		return $this->sortable;
	}

	public function getDefaultIcon ()
	{
		if (!sizeof ($this->icons))
			return NULL;

		if ($this->default === FALSE || !array_key_exists ($this->default, $this->icons))
			$key = key ($this->icons);
		else
			$key = $this->default;

		if (is_object ($this->icons [$key]))
			return $this->icons [$key];

		$this->icons [$key] = Icon::factory ($this->icons [$key], $this);

		return $this->icons [$key];
	}

	public function load ($where = '', $page = 0, $sql = FALSE)
	{
		if (!$page) $page = isset ($_GET['page']) && $_GET['page'] ? $_GET['page'] : 1;

		if ($sql === FALSE)
		{
			$fields = array ();
			foreach ($this->fields as $assign => $field)
				if ($field->isLoadable ())
					$fields [] = Database::toSql ($field);

			if (isset ($_GET['order']) && trim ($_GET['order']) != '')
				$order = Database::toOrder ($this->fields [$_GET['order']]) . (isset ($_GET['invert']) && $_GET['invert'] ? ' DESC' : '');
			elseif ($this->sortable && !is_a ($this, 'VersionView'))
				$order = "_order";
			else
				$order = implode (", ", $this->getOrder ());

			$sql = "SELECT ". $this->getTable () .".". $this->getPrimary () .", ". (sizeof ($fields) ? implode (", ", $fields) : "*") ." FROM ". $this->getTable () . (trim ($where) != '' ? " WHERE ". $where : "") . (trim ($order) != '' ? " ORDER BY ". $order : "");

			reset ($this->fields);
		}

		if ($this->paginate && $page)
			$sql .= " LIMIT ". $this->paginate ." OFFSET ". ($this->paginate * ($page - 1));

		// throw new Exception ($sql);

		$db = Database::singleton ();

		$this->sth = $db->prepare ($sql);

		$this->sth->execute ();

		$this->page = $page;

		$this->sql = $sql;

		$this->where = $where;

		return TRUE;
	}

	public function getField ($assign = FALSE)
	{
		if ($assign !== FALSE)
			if (array_key_exists ($assign, $this->fields))
				return $this->fields [$assign];
			else
				return current ($this->fields);

		$field = each ($this->fields);

		if ($field !== FALSE)
			return $field ['value'];

		reset ($this->fields);

		return NULL;
	}

	public function getLink ($onlyLink = FALSE)
	{
		if ($onlyLink)
			return method_exists ($this->getDefaultIcon (), 'getSection') ? Instance::singleton ()->getUrl () . 'titan.php?target=body&amp;toSection='. $this->getDefaultIcon ()->getSection () .'&amp;toAction='. $this->getDefaultIcon ()->getAction () .'&amp;itemId='. $this->itemId : '';

		$field = each ($this->fields);

		if ($field !== FALSE)
		{
			$field = $field ['value'];

			$icon = $this->getDefaultIcon ();

			if ($this->default === FALSE || !is_object ($icon) ||
				(array_key_exists ($icon->getId (), $this->disabledIcons) && is_array ($this->disabledIcons [$icon->getId ()]) && in_array ($this->getId (), $this->disabledIcons [$icon->getId ()])))
				return (string) self::toList ($field, $this->getId ());

			return '<a '. $this->getDefaultIcon ()->makeLink ($this->getId ()) .'>'. self::toList ($field, $this->getId ()) .'</a>';
		}

		reset ($this->fields);

		return NULL;
	}

	public function getItem ()
	{
		if ($this->sth === NULL)
			return NULL;

		$obj = $this->sth->fetch (PDO::FETCH_OBJ);

		if (!$obj)
			return NULL;

		$primary = $this->getPrimary ();

		$this->itemId = $obj->$primary;

		foreach ($this->fields as $assign => $field)
			if ($field->isLoadable ())
				$this->fields [$assign] = Database::fromDb ($field, $obj);
			else
				$this->fields [$assign]->load ($this->itemId);

		reset ($this->fields);

		return $obj;
	}

	public function getIcon ()
	{
		$icon = each ($this->icons);

		if ($icon !== FALSE)
		{
			if (!is_object ($icon ['value']))
				$this->icons [$icon ['key']] = Icon::factory ($icon ['value'], $this);

			$id = $this->icons [$icon ['key']]->getId ();

			if (array_key_exists ($id, $this->disabledIcons) && is_array ($this->disabledIcons [$id]) && in_array ($this->getId (), $this->disabledIcons [$id]))
				return $this->icons [$icon ['key']]->makeIcon (0, TRUE);

			return $this->icons [$icon ['key']]->makeIcon ($this->getId ());
		}

		reset ($this->icons);

		return NULL;
	}

	public function disableIcons ($id, $items)
	{
		if (!is_string ($id) || trim ($id) == '' || !is_array ($items) || !(int) sizeof ($items))
			return FALSE;

		$this->disabledIcons [$id] = $items;
	}

	public function removeIcon ($id)
	{
		unset ($this->icons [$id]);
	}

	public function getTotal ($where = '', $sql = FALSE)
	{
		if ($this->total !== NULL)
			return $this->total;

		if (trim ($where) == '') $where = $this->where;

		if ($sql === FALSE)
			$sql = "SELECT COUNT(*) AS total FROM ". $this->getTable () . (trim ($where) != '' ? " WHERE ". $where : "");

		//throw new Exception ($sql);

		$db = Database::singleton ();

		$sth = $db->prepare ($sql);

		$sth->execute ();

		$obj = $sth->fetch (PDO::FETCH_OBJ);

		if (!$obj)
			return '<li class="disabled">&laquo;' . __('Previous') . '</li><li class="selected">1</li><li class="disabled">' . __('Next') . ' &raquo;</li>';

		$this->total = $obj->total;

		return $this->total;
	}

	public function pageMenu ($where = '', $page = 0, $sql = FALSE)
	{
		if (!$this->paginate)
			return '';

		if (!$page) $page = $this->page;

		$order = isset ($_GET['order']) ? $_GET['order'] : '';

		$invert = isset ($_GET['invert']) ? $_GET['invert'] : 0;

		if (trim ($where) == '') $where = $this->where;

		if ($this->total === NULL)
		{
			if ($sql === FALSE)
				$sql = "SELECT COUNT(*) AS total FROM ". $this->getTable () . (trim ($where) != '' ? " WHERE ". $where : "");

			//throw new Exception ($sql);

			$db = Database::singleton ();

			$sth = $db->prepare ($sql);

			$sth->execute ();

			$obj = $sth->fetch (PDO::FETCH_OBJ);

			if (!$obj)
				return '<li class="disabled">&laquo; ' . __('Previous') . '</li><li class="selected">1</li><li class="disabled">' . __('Next') . ' &raquo;</li>';

			$this->total = $obj->total;
		}

		if (!$this->total)
			return '<li class="disabled">&laquo; ' . __('Previous') . '</li><li class="selected">1</li><li class="disabled">' . __('Next') .  ' &raquo;</li>';

		$numberOfPages = ceil ($this->total / $this->paginate);

		global $section, $action, $itemId;

		$pageIniti = $page - 6;
		$pageFinal = $page + 6;

		if ($pageIniti < 1)
			$pageFinal -= $pageIniti;

		if ($pageFinal > $numberOfPages)
		{
			$pageIniti -= ($pageFinal - $numberOfPages);
			$pageFinal = $numberOfPages;
		}

		if ($pageIniti < 1)
			$pageIniti = 1;

		$str = '';
		for ($i = $pageIniti ; $i <= $pageFinal ; $i++)
			if ($i == $page)
				$str .= '<li class="selected">'. $i .'</li>';
			else
				$str .= '<li class="enabled" onclick="JavaScript: document.location=\'titan.php?target=body&amp;toSection='. $section->getName () .'&amp;toAction='. $action->getName () .'&itemId='. $itemId .'&page='. $i .'&order='. $order .'&invert='. $invert .'\';">'. $i .'</li>';

		if ($page == 1)
			$previous = '<li class="disabled">&laquo; ' . __('Previous') . '</li>';
		else
			$previous = '<li class="enabled" onclick="JavaScript: document.location=\'titan.php?target=body&amp;toSection='. $section->getName () .'&amp;toAction='. $action->getName () .'&itemId='. $itemId .'&page='. ($page - 1) .'&order='. $order .'&invert='. $invert .'\';">&laquo; ' . __('Previous') . '</li>';

		if ($page == $i - 1)
			$next = '<li class="disabled">' . __('Next') . ' &raquo;</li>';
		else
			$next = '<li class="enabled" onclick="JavaScript: document.location=\'titan.php?target=body&amp;toSection='. $section->getName () .'&amp;toAction='. $action->getName () .'&itemId='. $itemId .'&page='. ($page + 1) .'&order='. $order .'&invert='. $invert .'\';">' . __('Next') . ' &raquo;</li>';

		if ($page == 1)
			$first = '<li class="disabled">' . __('First') . '</li>';
		else
			$first = '<li class="enabled" onclick="JavaScript: document.location=\'titan.php?target=body&amp;toSection='. $section->getName () .'&amp;toAction='. $action->getName () .'&itemId='. $itemId .'&page=1&order='. $order .'&invert='. $invert .'\';">' . __('First') . '</li>';

		if ($page == $numberOfPages)
			$last = '<li class="disabled">' . __('Last') . '</li>';
		else
			$last = '<li class="enabled" onclick="JavaScript: document.location=\'titan.php?target=body&amp;toSection='. $section->getName () .'&amp;toAction='. $action->getName () .'&itemId='. $itemId .'&page='. $numberOfPages .'&order='. $order .'&invert='. $invert .'\';">' . __('Last') . '</li>';

		return $previous . $first . $str . $last . $next;
	}

	public static function toLabel ($field, $useOrder = TRUE)
	{
		global $section, $action, $itemId;

		if (!$useOrder)
			return $field->getLabel ();

		$order = isset ($_GET['order']) && $field->getAssign () == $_GET['order'] ? TRUE : FALSE;

		$invert = isset ($_GET['invert']) && $_GET['invert'] ? TRUE : FALSE;

		$page = isset ($_GET['page']) ? $_GET['page'] : 0;

		if (!$order)
			return '<a class="labelView" href="titan.php?target=body&amp;toSection='. $section->getName () .'&amp;toAction='. $action->getName () .'&itemId='. $itemId .'&page='. $page .'&order='. $field->getAssign () .'&invert=0">'. $field->getLabel () .'</a>';

		if ($invert)
			return '<a class="labelView" href="titan.php?target=body&amp;toSection='. $section->getName () .'&amp;toAction='. $action->getName () .'&itemId='. $itemId .'&page='. $page .'&order='. $field->getAssign () .'&invert=0">'. $field->getLabel () .'</a> <img src="titan.php?target=loadFile&amp;file=interface/image/arrow.up.gif" border="0" style="vertical-align: middle;" />';

		return '<a class="labelView" href="titan.php?target=body&amp;toSection='. $section->getName () .'&amp;toAction='. $action->getName () .'&itemId='. $itemId .'&page='. $page .'&order='. $field->getAssign () .'&invert=1">'. $field->getLabel () .'</a> <img src="titan.php?target=loadFile&amp;file=interface/image/arrow.down.gif" border="0" style="vertical-align: middle;" />';
	}

	public static function toList ($field, $itemId = NULL)
	{
		if (!is_object ($field))
			return $field;

		$instance = Instance::singleton ();

		$fieldId = 'field_'. $field->getAssign ();

		$db = Database::singleton ();

		$type = get_class ($field);

		do
		{
			$file = $instance->getTypePath ($type) .'toList.php';

			if (file_exists ($file))
				return include $file;

			$type = get_parent_class ($type);

		} while ($type != 'Type' && $type !== FALSE);

		$type = get_class ($field);

		do
		{
			$file = $instance->getTypePath ($type) .'toHtml.php';

			if (file_exists ($file))
				return include $file;

			$type = get_parent_class ($type);

		} while ($type != 'Type' && $type !== FALSE);

		return is_null ($field->getValue ()) ? '&nbsp;' : $field->getValue ();
	}
}
?>
