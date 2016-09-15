<?php
/**
 * VersionView.php
 *
 * This class extends and especializate View class for use in Titan Version
 * Control.
 *
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @category class
 * @package core
 * @subpackage version
 * @copyright Creative Commons Attribution No Derivatives (CC-BY-ND)
 * @see VersionForm, VersionSearch, View
 */
class VersionView extends View
{
	protected $vTable = '';
	
	protected $vPrimary = '';
	
	public function __construct ()
	{
		$section = Business::singleton ()->getSection (Section::TCURRENT);
		
		$action = Business::singleton ()->getAction (Action::TCURRENT);
		
		$args = func_get_args ();
		
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
		
		$array = $array ['view'][0];
		
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
		
		if (array_key_exists ('paginate', $array))
			$this->paginate = $array ['paginate'];
		
		$this->sortable = array_key_exists ('sortable', $array) && strtoupper ($array ['sortable']) == 'TRUE' ? TRUE : FALSE;
		
		$user = User::singleton ();
		
		$vControl = array (	'_tvc_version' 	=> FALSE,
							'_tvc_date' 	=> FALSE);
		
		if (array_key_exists ('field', $array) && is_array ($array ['field']))
			foreach ($array ['field'] as $trash => $field)
				if ($obj = Type::factory ($this->getTable (), $field))
				{
					while ($perm = $obj->getRestrict ())
						if (!$user->hasPermission ($perm))
							continue 2;
					
					if (array_key_exists ($obj->getColumn (), $vControl))
						$vControl [$obj->getColumn ()] = TRUE;
					
					$this->fields [$obj->getAssign ()] = $obj;
				}
		
		$vFields = array (	'_tvc_version' 	=> array ('type' => 'Integer', 'column' => '_tvc_version', 'id' => '_VERSION_', 'label' => 'Revisão'),
							'_tvc_date'		=> array ('type' => 'Date', 'column' => '_tvc_date', 'label' => 'Data', 'id' => '_VERSION_DATE_', 'show-time' => 'true'));
		
		$aux = array ();
		foreach ($vControl as $key => $exists)
			if (!$exists)
				$aux [$vFields [$key]['id']] = Type::factory ($this->getTable (), $vFields [$key]);
		
		$this->fields = array_merge ($aux, $this->fields);
		
		if (isset ($_GET['order']) && trim ($_GET['order']) != '')
			$this->order [] = array (trim ($_GET['order']), (isset ($_GET['invert']) && $_GET['invert'] == 1 ? TRUE : FALSE));
		elseif (array_key_exists ('_VERSION_', $this->fields))
			$this->order [] = array ('_VERSION_', TRUE);
		elseif (array_key_exists ('order', $array))
			foreach ($array ['order'] as $trash => $order)
			{
				if (!array_key_exists ('id', $order))
					continue;
				
				if (!array_key_exists (trim ($order ['id']), $this->fields))
					continue;
				
				$this->order [] = array (trim ($order ['id']), (array_key_exists ('invert', $order) && strtoupper ($order ['invert']) == 'TRUE' ? TRUE : FALSE));
			}
		
		$valid = array ('revertRevision', 'viewRevision');
		
		if (array_key_exists ('icon', $array))
			foreach ($array ['icon'] as $trash => $icon)
			{
				if (!array_key_exists ('function', $icon) || !in_array ($icon ['function'], $valid))
					continue;
				
				$this->icons [] = Icon::factory ($icon, $this);
				
				if (array_key_exists ('default', $icon) && strtoupper ($icon ['default']) == 'TRUE')
					$this->default = key (current ($this->icons));
			}
		
		if (!sizeof ($this->icons))
		{
			$this->icons [] = Icon::factory (array ('section' => $section->getName (), 
													'label' => 'Ver Revisão', 
													'image' => 'view.gif', 
													'function' => 'viewRevision'), $this);
												
			$this->icons [] = Icon::factory (array ('section' => $section->getName (), 
													'label' => 'Reverter Para Revisão', 
													'image' => 'revert.gif', 
													'function' => 'revertRevision'), $this);
		}
		
		reset ($this->fields);
		reset ($this->icons);
	}
	
	public function getVersionedTable ()
	{
		return $this->vTable;
	}
	
	public function getVersionedPrimary ()
	{
		return $this->vPrimary;
	}
}
?>