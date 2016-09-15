<?php
/**
 * VersionForm.php
 *
 * This class extends and especializate Form class for use in Titan Version
 * Control.
 *
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @category class
 * @package core
 * @subpackage version
 * @copyright Creative Commons Attribution No Derivatives (CC-BY-ND)
 * @see VersionView, VersionSearch, Form
 */
class VersionForm extends Form
{
	public function __construct ($files)
	{
		$section = Business::singleton ()->getSection (Section::TCURRENT);
		
		$action = Business::singleton ()->getAction (Action::TCURRENT);
		
		$fileName = FALSE;
		
		if (!is_array ($files))
			$files = func_get_args();
		
		foreach ($files as $trash => $file)
		{
			if (!file_exists ('section/'. $section->getName () .'/'. $file))
				continue;
			
			$fileName = $file;
			
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
		
		$this->assign = md5 ($section->getName () .'.'. $action->getName () .'.'. $fileName);
		
		$this->file = $fileName;
		
		if (array_key_exists ('table', $array))
		{
			$this->vTable = $array ['table'];
			$this->table = Version::singleton ()->vcTable ($array ['table']);
		}
		
		if (array_key_exists ('primary', $array))
		{
			$this->vPrimary = $array ['primary'];
			$this->primary = '_tvc_version';
		}
		
		if (array_key_exists ('go-to', $array) && is_array ($array ['go-to']))
			foreach ($array ['go-to'] as $trash => $go)
			{
				if (!array_key_exists ('flag', $go) || !array_key_exists ('action', $go))
					continue;
				
				$this->go [$go ['flag']] = $go ['action'];
			}
		
		$user = User::singleton ();
		
		$groupId = 0;
		
		$this->groupsInfo [$groupId] = array ();
		
		if (array_key_exists ('field', $array) && is_array ($array ['field']))
			foreach ($array ['field'] as $trash => $field)
				if ($obj = Type::factory ($this->getTable (), $field))
				{
					while ($perm = $obj->getRestrict ())
						if (!$user->hasPermission ($perm))
							continue 2;
					
					if (!$obj->isLoadable () || !$obj->isSavable ())
						continue;
					
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
							while ($perm = $obj->getRestrict ())
								if (!$user->hasPermission ($perm))
									continue 2;
							
							if (!$obj->isLoadable () || !$obj->isSavable ())
								continue;
							
							$this->fields [$obj->getAssign ()] = $obj;
							$this->groups [$groupId][] = $obj->getAssign ();
						}
			}
		
		reset ($this->fields);
		reset ($this->groupsInfo);
		reset ($this->groups);
	}
	
	public function getVersionedTable ()
	{
		return $this->vTable;
	}
	
	public function getVersionedPrimary ()
	{
		return $this->vPrimary;
	}
	
	public function load ($id, $version)
	{
		if ($this->isLoaded ())
			return TRUE;
		
		$this->setId ($id);
		
		$fields = array ();
		foreach ($this->fields as $assign => $field)
			if ($field->isLoadable ())
				$fields [] = Database::toSql ($field);
		
		if (!sizeof ($fields))
		{
			reset ($this->fields);
			
			return TRUE;
		}
		
		$sql = "SELECT ". implode (', ', $fields) ." FROM ". $this->getTable () ." WHERE ". $this->getPrimary () ." = '". $version ."' AND ". $this->getVersionedPrimary () ." = '". $id ."'";
		
		//throw new Exception ($sql);
		
		$db = Database::singleton ();
		
		$sth = $db->prepare ($sql);
		
		$sth->execute ();
		
		$obj = $sth->fetch (PDO::FETCH_OBJ);
		
		if (!$obj)
			return FALSE;
		
		foreach ($this->fields as $assign => $field)
			if ($field->isLoadable ())
				$this->fields [$assign] = Database::fromDb ($field, $obj);
		
		reset ($this->fields);
		
		return TRUE;
	}
	
	public function revert ()
	{
		//throw new Exception (print_r ($this->fields, TRUE));
		$fields = array ();
		$values = array ();
		foreach ($this->fields as $key => $field)
			if (!$field->isReadOnly () && $field->isSavable ())
			{
				$field->setTable ($this->getVersionedTable ());
				$fields [] = $field->getColumn ();
				$values [] = Database::toValue ($field);
			}
		
		reset ($this->fields);
		
		$user = User::singleton ();
		
		$itemId = $this->getId ();
		
		//throw new Exception ($itemId);
		
		if (is_numeric ($itemId) && !(int) $itemId)
			throw new Exception ('O ID da tupla que deve ser atualizada não esta setado!');
		
		$sql = "SELECT COUNT(*) AS total FROM ". $this->getVersionedTable () ." WHERE ". $this->getVersionedPrimary () ." = '". $itemId ."'";
		
		$db = Database::singleton ();
		
		$sth = $db->prepare ($sql);
		
		$sth->execute ();
		
		$obj = $sth->fetch (PDO::FETCH_OBJ);
		
		// array_push ($fields, '_user', '_update');
		// array_push ($values, $user->getId (), 'NOW()');
		
		$aux = array ();
		foreach ($fields as $key => $field)
			$aux [] = $field ." = ". $values [$key];
		
		try
		{
			if ((int) $obj->total)
				$sql = "UPDATE ". $this->getVersionedTable () ." SET ". implode (", ", $aux) .", _user = ". $user->getId () .", _update = NOW() WHERE ". $this->getVersionedPrimary () ." = '". $itemId ."'";
			else
				$sql = "INSERT INTO ". $this->getVersionedTable () ." (". $this->getVersionedPrimary () .", ". implode (", ", $fields) .", _user) VALUES (". $itemId .", ". implode (", ", $values) .", ". $user->getId () .")";
			
			//throw new Exception ($sql);
			
			$sth = $db->prepare ($sql);
			
			$sth->execute ();
		}
		catch (PDOException $e)
		{
			if ((int) $obj->total)
				$sql = "UPDATE ". $this->getVersionedTable () ." SET ". implode (", ", $aux) ." WHERE ". $this->getVersionedPrimary () ." = '". $itemId ."'";
			else
				$sql = "INSERT INTO ". $this->getVersionedTable () ." (". $this->getVersionedPrimary () .", ". implode (", ", $fields) .") VALUES (". $itemId .", ". implode (", ", $values) .")";
			
			//throw new Exception ($sql);
			
			$sth = $db->prepare ($sql);
			
			if (!$sth->execute ())
				return FALSE;
		}
		
		return TRUE;
	}
}
?>