<?php
class Document extends Collection
{
	protected $terms = array ();
	
	protected $replace = array ();
	
	protected $link = '';
	
	protected $linkColumn = '';
	
	protected $relation = '';
	
	public function __construct ($table, $field)
	{
		parent::__construct ($table, $field);
		
		if (!array_key_exists ('document', $field) || !is_array ($field ['document']))
			throw new Exception (__ ('Invalid syntax for field of type Document!'));
		
		if (array_key_exists ('relation', $field) && trim ($field ['relation']) != '')
			$this->relation = $field ['relation'];
		else
			$this->relation = $table .'_doc';
		
		foreach ($field ['document'] as $trash => $term)
		{
			if (!array_key_exists ('id', $term) || !array_key_exists ('label', $term) || !array_key_exists ('simple', $term) || !array_key_exists ('template', $term))
				continue;
			
			$this->terms [$term ['id']] = new DocumentItem ($term, $this);
		}
		
		if (array_key_exists ('replace', $field) && is_array ($field ['replace']) && array_key_exists ('link-table', $field) && array_key_exists ('link-column', $field))
		{
			$this->link = $field ['link-table'];
			
			$this->linkColumn = $field ['link-column'];
			
			foreach ($field ['replace'] as $trash => $replace)
				if (array_key_exists ('tag', $replace) && array_key_exists ('column', $replace))
					$this->replace [$replace ['tag']] = $replace ['column'];
		}
	}
	
	public function getRelation ()
	{
		return $this->relation;
	}
	
	public function getReplace ()
	{
		return $this->replace;
	}
	
	public function docExists ($id)
	{
		return array_key_exists ($id, $this->terms);
	}
	
	public function getDocument ($id = NULL)
	{
		if (!is_null ($id) && array_key_exists ($id, $this->terms))
			return $this->terms [$id];
		
		$term = each ($this->terms);
		
		while ($term !== FALSE)
			return $term ['value'];
		
		reset ($this->terms);
		
		return NULL;
	}
	
	public function initiate ()
	{
		global $itemId;
		
		if (!isset ($itemId) || !is_numeric ($itemId) || !(int) $itemId)
			throw new Exception ('Attention! You must save the form before you can enter items on this field.');
		
		$db = Database::singleton ();
		
		$sth = $db->prepare ("SELECT COUNT(*) FROM ". $this->getRelation () ." WHERE _id = :id AND _relation = :relation");
		
		while ($term = $this->getDocument ())
		{
			if (!$term->isAutoCreated ())
				continue;
			
			$sth->bindParam (':id', $term->getId (), PDO::PARAM_STR, 128);
			$sth->bindParam (':relation', $itemId, PDO::PARAM_INT);
			
			$sth->execute ();
			
			if ((int) $sth->fetchColumn ())
				continue;
			
			$term->genInitialContent ();
		}
		
		reset ($this->terms);
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
	
	public function setAuto ($id, $auto = TRUE)
	{
		if (!isset ($this->terms [$id]) || !is_object ($this->terms [$id]))
			return;
		
		$this->terms [$id]->setAuto ($auto);
	}
	
	static public function register ($relation, $id, $itemId, $version, $template, $label)
	{
		if (!array_key_exists ('_TERM_REGISTER_', $_SESSION))
			$_SESSION ['_TERM_REGISTER_'] = array ();
		
		$assign = self::genAssign ($relation, $id, $itemId, $version);
		
		if (!array_key_exists ($assign, $_SESSION ['_TERM_REGISTER_']))
			$_SESSION ['_TERM_REGISTER_'][$assign] = array ('_RELATION_' => $relation,
															'_ID_' => $id,
															'_FATHER_' => $itemId,
															'_VERSION_' => $version,
															'_TEMPLATE_' => $template,
															'_LABEL_' => $label);
		
		return $assign;
	}
	
	static private function genAssign ($relation, $id, $itemId, $version)
	{
		return md5 ($relation . Security::singleton ()->getHash () . $id . Security::singleton ()->getHash () . $itemId . Security::singleton ()->getHash () . $version);
	}
	
	static public function genAuth ($assign)
	{
		$valid = '123456789ABCDEFHJKLMNPQRSTUVWXYZ';
		
		$i = 0;
		
		$auth = '';
		while ($i < strlen ($assign))
			$auth .= $valid [hexdec ($assign [$i++] . $assign [$i++]) % 32];
		
		return $auth;
	}
	
	public function copy ($itemId, $newId)
	{
		if (!(int) $itemId || !(int) $newId || $itemId == $newId)
			throw new Exception (__ ('Impossible to copy field [[1]]! Data losted.', $this->getLabel () .' ('. $this->getColumn () .')'));
		
		$sql = "INSERT INTO ". $this->getRelation () ." 
				(_id, _relation, _version, _content, _file, _hash, _user, _auth, _validate)
				SELECT _id, '". $newId ."' AS _relation, _version, _content, _file, _hash, '". User::singleton ()->getId () ."' AS _user, _auth, _validate FROM ". $this->getRelation () ."
				WHERE _relation = '". $itemId ."'";
		
		Database::singleton ()->exec ($sql);
		
		return TRUE;
	}
}

class DocumentItem
{
	private $term = NULL;
	
	private $id = NULL;
	
	private $label = '';
	
	private $template = '';
	
	private $path = '';
	
	private $simple = '';
	
	private $auto = FALSE;
	
	private $validate = FALSE;
	
	public function __construct ($input, &$term)
	{
		$this->term =& $term;
		
		$this->id = $input ['id'];
		
		$this->label = translate ($input ['label']);
		
		$this->simple = $input ['simple'];
		
		$this->template = trim ($input ['template']);
		
		if (array_key_exists ('path', $input) && trim ($input ['path']) != '' && is_dir ($input ['path']))
			$this->path = trim ($input ['path']);
		else
			$this->path = Instance::singleton ()->getTypePath ('Document') .'template/';
		
		if (array_key_exists ('auto', $input))
			$this->auto = (strtoupper ($input ['auto']) == 'TRUE' ? TRUE : FALSE);
		
		if (array_key_exists ('validate', $input))
			$this->validate = (strtoupper ($input ['validate']) == 'TRUE' ? TRUE : FALSE);
	}
	
	public function getId ()
	{
		return $this->id;
	}
	
	public function getLabel ()
	{
		return $this->label;
	}
	
	public function getSimple ()
	{
		return $this->simple;
	}
	
	public function getTemplate ()
	{
		return $this->template;
	}
	
	public function getPath ()
	{
		return $this->path;
	}
	
	public function isAutoCreated ()
	{
		return $this->auto;
	}
	
	public function isValidatable ()
	{
		return $this->validate;
	}
	
	public function setAuto ($auto)
	{
		if (!is_bool ($auto))
			return;
		
		$this->auto = (bool) $auto;
	}
	
	public function getDocument ()
	{
		return $this->term;
	}
	
	public function genInitialContent ()
	{
		if (!is_object ($this->term))
			return FALSE;
		
		global $itemId;
		
		$simple = new DocumentForm ($this->getPath () . $this->getTemplate ());
		
		$simple->loadSimple ($this->getSimple ());
		
		$fields = $this->getReplaced ($simple, $itemId);
		
		$content = base64_encode (serialize ($fields));
		
		$sth = Database::singleton ()->prepare ("INSERT INTO ". $this->getDocument ()->getRelation () ." (_id, _content, _relation, _validate) VALUES ('". $this->getId () ."', '". $content ."', '". $itemId ."', ". ($this->isValidatable () ? "B'1'" : "B'0'") .")");
		
		return $sth->execute ();
	}
	
	public function getReplaced (&$form, $itemId)
	{
		$db = Database::singleton ();
		
		$sql = "SELECT * FROM ". $this->getDocument ()->getLink () ." WHERE ". $this->getDocument ()->getLinkColumn () ." = '". $itemId ."'";
		
		$sth = $db->prepare ($sql);
		
		$sth->execute ();
		
		$obj = $sth->fetch (PDO::FETCH_OBJ);
		
		if (!$obj)
			return FALSE;
		
		$replace = array ();
		foreach ($this->getDocument ()->getReplace () as $tag => $column)
			if (isset ($obj->$column))
				$replace [$tag] = $obj->$column;
			else
				unset ($replace [$tag]);
		
		$kReplace = array_keys ($replace);
		$vReplace = array_values ($replace);
		
		$fields = array ();
		
		while ($field =& $form->getField ())
		{
			$value = $field->getValue ();
			
			if (!is_string ($value))
			{
				$fields [$field->getAssign ()] = $value;
				
				continue;
			}
			
			$value = str_replace ($kReplace, $vReplace, $value);
			
			$field->setValue ($value);
			
			$fields [$field->getAssign ()] = $value;
		}
		
		return $fields;
	}
}

class DocumentForm extends Form
{	
	public function __construct ($template)
	{
		$file = $template .'.xml';
		
		$cacheFile = Instance::singleton ()->getCachePath () .'parsed/'. fileName ($file) .'_'. md5_file ($file) .'.php';
		
		if (file_exists ($cacheFile))
			$array = include $cacheFile;
		else
		{
			$xml = new Xml ($file);
			
			$array = $xml->getArray ();
			
			if (!isset ($array ['form'][0]))
				throw new Exception ('A tag &lt;form&gt;&lt;/form&gt; não foi encontrada no XML ['. $file .']!');
			
			xmlCache ($cacheFile, $array);
		}
		
		if (!array_key_exists ('form', $array))
			throw new Exception ('Invalid XML Form file ['. $file .'].');
		
		$array = $array ['form'][0];
		
		$this->template = $template;
		
		$groupId = 0;
		
		$this->groupsInfo [$groupId] = array ();
		
		if (array_key_exists ('field', $array) && is_array ($array ['field']))
			foreach ($array ['field'] as $trash => $field)
				if ($obj = Type::factory ($this->getTable (), $field))
				{	
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
							$this->fields [$obj->getAssign ()] = $obj;
							$this->groups [$groupId][] = $obj->getAssign ();
						}
			}
		
		reset ($this->fields);
		reset ($this->groupsInfo);
		reset ($this->groups);
	}
	
	public function loadSimple ($id)
	{
		if ($this->isLoaded ())
			return TRUE;
		
		$db = Database::singleton ();
		
		$sth = $db->prepare ("SELECT _content FROM _simple WHERE _id = '". $id ."'");
		
		$sth->execute ();
		
		$obj = $sth->fetch (PDO::FETCH_OBJ);
		
		if (!$obj)
			return TRUE;
		
		if (trim ($obj->_content) == '')
			return TRUE;
		
		$fields = unserialize (base64_decode ($obj->_content));
		
		foreach ($fields as $assign => $value)
			if (array_key_exists ($assign, $this->fields))
				$this->fields [$assign]->setValue ($value);
		
		$this->setLoad ();
		
		return TRUE;
	}
	
	public function load ($relation, $id, $itemId, $version = FALSE)
	{
		if ($this->isLoaded ())
			return TRUE;
		
		$db = Database::singleton ();
		
		if ($version === FALSE || !is_numeric ($version))
		{
			$sql = "SELECT MAX(_version) AS v FROM ". $relation ." WHERE _id = '". $id ."' AND _relation = '". $itemId ."'";
			
			$sth = $db->prepare ($sql);
			
			$sth->execute ();
			
			$obj = $sth->fetch (PDO::FETCH_OBJ);
			
			if (is_null ($obj->v))
				throw new Exception (__ ('This term does not exists!'));
			
			$version = (int) $obj->v;
		}
		
		$sth = $db->prepare ("SELECT _content FROM ". $relation ." WHERE _id = '". $id ."' AND _relation = '". $itemId ."' AND _version = '". $version ."'");
		
		$sth->execute ();
		
		$obj = $sth->fetch (PDO::FETCH_OBJ);
		
		if (!$obj)
			return TRUE;
		
		if (trim ($obj->_content) == '')
			return TRUE;
		
		$fields = unserialize (base64_decode ($obj->_content));
		
		foreach ($fields as $assign => $value)
			if (array_key_exists ($assign, $this->fields))
				$this->fields [$assign]->setValue ($value);
		
		$this->setLoad ();
		
		return TRUE;
	}
	
	public function save ($relation, $itemId, $id, $validate)
	{
		$fields = array ();
		
		foreach ($this->fields as $key => $field)
			$fields [$field->getAssign ()] = $field->getValue ();
		
		reset ($this->fields);
		
		$content = base64_encode (serialize ($fields));
		
		try
		{
			$db = Database::singleton ();
			
			$db->beginTransaction ();
			
			$sql = "SELECT MAX(_version) AS v FROM ". $relation ." WHERE _id = '". $id ."' AND _relation = '". $itemId ."'";
			
			$sth = $db->prepare ($sql);
			
			$sth->execute ();
			
			$obj = $sth->fetch (PDO::FETCH_OBJ);
			
			$version = !$obj || is_null ($obj->v) ? 1 : ((int) $obj->v) + 1;
			
			$sql = "INSERT INTO ". $relation ." 
					(_id, _relation, _version, _content, _create, _user, _validate)
					VALUES (
					'". $id ."', '". $itemId ."', '". $version ."', 
					'". $content ."', now(), '". User::singleton ()->getId () ."', ". ((int) $validate ? "B'1'" : "B'0'") .")";
			
			//throw new Exception ($sql);
			
			$db->exec ($sql);
			
			$db->commit ();
			
			return $version;
		}
		catch (PDOException $e)
		{
			$db->rollBack ();
			
			return 0;
		}
	}
}
?>