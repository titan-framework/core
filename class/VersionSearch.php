<?
/**
 * VersionSearch.php
 *
 * This class extends and especializate Search class for use in Titan Version
 * Control.
 *
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @category class
 * @package core
 * @subpackage version
 * @copyright Creative Commons Attribution No Derivatives (CC-BY-ND)
 * @see VersionForm, VersionView, Search
 */
class VersionSearch extends Search
{
	protected $vTable = '';
	
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
			
			if (!isset ($array ['search'][0]))
				throw new Exception ('A tag &lt;search&gt;&lt;/search&gt; não foi encontrada no XML ['. $fileName .']!');
			
			xmlCache ($cacheFile, $array);
		}
		
		$array = $array ['search'][0];
		
		$this->file = $fileName;
		
		if (array_key_exists ('table', $array))
		{
			$this->table = Version::singleton ()->vcTable ($array ['table']);
			
			$this->vTable = $array ['table'];
		}
		
		if (array_key_exists ('father', $array) && isset ($_GET['itemId']) && $_GET['itemId'])
			$this->father = array ($array ['father'], $_GET['itemId']);
		
		$search = Instance::singleton ()->getSearch ();
		
		if (array_key_exists ('hash', $search))
			$this->hash = $search ['hash'];
		
		if (array_key_exists ('timeout', $search))
			$this->timeout = $search ['timeout'];
		
		global $section, $action;
		
		$vControl = array ('_tvc_version' => FALSE);
		
		if (array_key_exists ('field', $array) && is_array ($array ['field']))
			foreach ($array ['field'] as $trash => $field)
				if ($obj = Type::factory ($this->getTable (), $field))
				{
					if (array_key_exists ($obj->getColumn (), $vControl))
						$vControl [$obj->getColumn ()] = TRUE;
					
					$this->fields [$obj->getAssign ()] = $obj;
				}
		
		$vFields = array ('_tvc_version' => array ('type' => 'Integer', 'column' => '_tvc_version', 'id' => '_VERSION_', 'label' => 'Revisão'));
		
		foreach ($vControl as $key => $exists)
			if (!$exists)
				$this->fields [$vFields [$key]['id']] = Type::factory ($this->getTable (), $vFields [$key]);
		
		reset ($this->fields);
	}
}
?>