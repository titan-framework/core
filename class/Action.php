<?php
/**
 * This class contains logic mapping for specific action.
 *
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @category class
 * @package core
 * @subpackage business
 * @copyright 2005-2017 Titan Framework
 * @license http://www.titanframework.com/license/ BSD License (3 Clause)
 * @see Instance, Business, Section
 */
class Action
{
	private $label = '';

	private $name = '';

	private $engine = '';

	private $path = '';

	private $description = '';

	private $warning = '';

	private $doc = '';

	private $indexTo = '';

	private $xmlPath = FALSE;

	private $menu = array ();

	private $freeAccess = FALSE;

	const TDEFAULT  = '__DEFAULT_ACTION__';
	const TREGISTER = '__REGISTER_ACTION__';
	const TMODIFY  	= '__MODIFY_ACTION__';
	const TRSS      = '__RSS_ACTION__';
	const TJOB      = '__JOB_ACTION__';
	const TCURRENT  = '__CURRENT_ACTION__';
	const TSCRIPT	= '__SCRIPT_ACTION__';
	const TLUCENE	= '__LUCENE_ACTION__';
	const TAPI		= '__API_ACTION__';

	const PREPARE	= '.prepare.php';
	const VIEW		= '.php';
	const COMMIT	= '.commit.php';

	public function __construct ($input, $defaultPath)
	{
		if (!is_array ($input))
			throw new Exception ('Input to action mapping is not array!');

		if (array_key_exists ('label', $input))
			$this->setLabel ($input ['label']);

		if (array_key_exists ('name', $input) && trim ($input ['name']) != '')
			$this->setName ($input ['name']);
		else
			throw new Exception ('All actions (&lt;action /&gt;) of configuration files [<b>config.inc.xml</b>] must be a property [<b>name</b>] with non-empty value!');

		if (array_key_exists ('engine', $input))
			$this->setEngine ($input ['engine']);
		else
			$this->setEngine ($input ['name']);

		if (array_key_exists ('path', $input))
			$this->setPath ($input ['path']);
		else
			$this->setPath ($defaultPath);

		if (array_key_exists ('description', $input))
			$this->setDescription ($input ['description']);

		if (array_key_exists ('warning', $input))
			$this->setWarning ($input ['warning']);

		if (array_key_exists ('doc', $input))
			$this->setDoc ($input ['doc']);

		if (array_key_exists ('index-to', $input))
			$this->setIndex ($input ['index-to']);

		if (array_key_exists ('xml-path', $input))
			$this->setXmlPath ($input ['xml-path']);

		if (array_key_exists ('menu', $input))
		{
			if (!is_array ($input ['menu']))
				$input ['menu'] = array ($input ['menu']);

			$this->menu = $input ['menu'];
		}
	}

	public function generateMenu ()
	{
		Menu::singleton ($this->menu);
	}

	public function getMenu ()
	{
		return $this->menu;
	}

	public function setLabel ($label)
	{
		$this->label = translate ($label);
	}

	public function getLabel ()
	{
		return $this->label;
	}

	public function setName ($name)
	{
		$this->name = $name;
	}

	public function getName ()
	{
		return $this->name;
	}

	public function setEngine ($engine)
	{
		$this->engine = $engine;
	}

	public function getEngine ()
	{
		return $this->engine;
	}

	public function setDescription ($description)
	{
		$this->description = translate ($description);
	}

	public function getDescription ()
	{
		return $this->description;
	}

	public function setWarning ($warning)
	{
		$this->warning = translate ($warning);
	}

	public function getWarning ()
	{
		return $this->warning;
	}

	public function setDoc ($doc)
	{
		$this->doc = translate ($doc);
	}

	public function getDoc ()
	{
		return $this->doc;
	}

	public function setXmlPath ($xml)
	{
		$this->xmlPath = $xml;
	}

	public function getXmlPath ()
	{
		return $this->xmlPath;
	}

	public function setPath ($path)
	{
		$this->path = $path;
	}

	public function getPath ()
	{
		return $this->path;
	}

	public function getFullPathTo ($suffix)
	{
		return $this->getPath () . $this->getEngine () . $suffix;
	}

	public function setIndex ($index)
	{
		$this->indexTo = $index;
	}

	public function getIndex ()
	{
		return $this->indexTo;
	}

	public function setFreeAccess ($free)
	{
		$this->freeAccess = $free;
	}

	public function freeAccess ()
	{
		return $this->freeAccess;
	}
}
