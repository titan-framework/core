<?php
class ViewLog extends View
{
	public function __construct ($file)
	{
		parent::__construct ($file);
	}

	public function getLink ($onlyLink = FALSE)
	{
		if ($onlyLink)
			return $this->getDefaultIcon ()->makeLink ($this->getId ());

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

	public function load ($where = '', $page = 0, $sql = FALSE)
	{
		if (!$page) $page = isset ($_GET['page']) && $_GET['page'] ? $_GET['page'] : 1;

		if ($sql === FALSE)
		{
			$fields = array ();
			foreach ($this->fields as $assign => $field)
				$fields [] = Log::toSql ($field);

			if (isset ($_GET['order']) && trim ($_GET['order']) != '')
				$order = Log::toOrder ($this->fields [$_GET['order']]) . (isset ($_GET['invert']) && $_GET['invert'] ? ' DESC' : '');
			elseif ($this->sortable)
				$order = "_order";
			else
				$order = implode (", ", $this->getOrder ());

			$sql = "SELECT ". $this->getTable () .".". $this->getPrimary () .", ". (sizeof ($fields) ? implode (", ", $fields) : "*") ." FROM ". $this->getTable () . (trim ($where) != '' ? " WHERE ". $where : "") . (trim ($order) != '' ? " ORDER BY ". $order : "");

			reset ($this->fields);
		}

		if ($this->paginate && $page)
			$sql .= " LIMIT ". $this->paginate ." OFFSET ". ($this->paginate * ($page - 1));

		//throw new Exception ($sql);

		if (!Log::singleton ()->isActive ())
			throw new Exception ('O Sistema de Log de Atividades está inativo!');

		$db = Log::singleton ()->getDb ();

		$this->sth = $db->prepare ($sql);

		$this->sth->execute ();

		$this->page = $page;

		$this->sql = $sql;

		$this->where = $where;

		return TRUE;
	}

	public function getTotal ($where = '', $sql = FALSE)
	{
		if ($this->total !== NULL)
			return $this->total;

		if (trim ($where) == '') $where = $this->where;

		if ($sql === FALSE)
			$sql = "SELECT COUNT(*) AS total FROM ". $this->getTable () . (trim ($where) != '' ? " WHERE ". $where : "");

		//throw new Exception ($sql);

		$db = Log::singleton ()->getDb ();

		$sth = $db->prepare ($sql);

		$sth->execute ();

		$obj = $sth->fetch (PDO::FETCH_OBJ);

		if (!$obj)
			return '<li class="disabled">&laquo; Anterior</li><li class="selected">1</li><li class="disabled">Próximo &raquo;</li>';

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

			$db = Log::singleton ()->getDb ();

			$sth = $db->prepare ($sql);

			$sth->execute ();

			$obj = $sth->fetch (PDO::FETCH_OBJ);

			if (!$obj)
				return '<li class="disabled">&laquo; Anterior</li><li class="selected">1</li><li class="disabled">Próximo &raquo;</li>';

			$this->total = $obj->total;
		}

		if (!$this->total)
			return '<li class="disabled">&laquo; Anterior</li><li class="selected">1</li><li class="disabled">Próximo &raquo;</li>';

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
			$previous = '<li class="disabled">&laquo; Anterior</li>';
		else
			$previous = '<li class="enabled" onclick="JavaScript: document.location=\'titan.php?target=body&amp;toSection='. $section->getName () .'&amp;toAction='. $action->getName () .'&itemId='. $itemId .'&page='. ($page - 1) .'&order='. $order .'&invert='. $invert .'\';">&laquo; Anterior</li>';

		if ($page == $i - 1)
			$next = '<li class="disabled">Próximo &raquo;</li>';
		else
			$next = '<li class="enabled" onclick="JavaScript: document.location=\'titan.php?target=body&amp;toSection='. $section->getName () .'&amp;toAction='. $action->getName () .'&itemId='. $itemId .'&page='. ($page + 1) .'&order='. $order .'&invert='. $invert .'\';">Próximo &raquo;</li>';

		if ($page == 1)
			$first = '<li class="disabled">Primeira</li>';
		else
			$first = '<li class="enabled" onclick="JavaScript: document.location=\'titan.php?target=body&amp;toSection='. $section->getName () .'&amp;toAction='. $action->getName () .'&itemId='. $itemId .'&page=1&order='. $order .'&invert='. $invert .'\';">Primeira</li>';

		if ($page == $numberOfPages)
			$last = '<li class="disabled">Última</li>';
		else
			$last = '<li class="enabled" onclick="JavaScript: document.location=\'titan.php?target=body&amp;toSection='. $section->getName () .'&amp;toAction='. $action->getName () .'&itemId='. $itemId .'&page='. $numberOfPages .'&order='. $order .'&invert='. $invert .'\';">Última</li>';

		return $previous . $first . $str . $last . $next;
	}
}

class SearchLog extends Search
{
	public function __construct ($file)
	{
		parent::__construct ($file);
	}

	public function makeWhere ()
	{
		$aux = array ();
		foreach ($this->fields as $trash => $field)
			if (!$field->isEmpty ())
				$aux [] = self::toWhere ($field);

		reset ($this->fields);

		//throw new Exception (implode (' AND ', $aux));

		return implode (' AND ', $aux);
	}

	public static function toWhere ($field)
	{
		if (!is_object ($field))
			return $field;

		$instance = Instance::singleton ();

		$type = get_class ($field);

		do
		{
			$file = $instance->getTypePath ($type) .'toWhere.SQLite.php';

			if (file_exists ($file))
				return include $file;

			$type = get_parent_class ($type);

		} while ($type != 'Type' && $type !== FALSE);

		return Search::toWhere ($field);
	}
}

class FormLog extends Form
{
	public function __construct ($file)
	{
		parent::__construct ($file);
	}
}
?>
