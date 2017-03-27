<?php
/**
 * This class contains logic mapping for a specific section.
 *
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @category class
 * @package core
 * @subpackage business
 * @copyright 2005-2017 Titan Framework
 * @license http://www.titanframework.com/license/ BSD License (3 Clause)
 * @see Instance, Business, Action
 */
class Section
{
	private $actions = array ();

	private $permissions = array ();

	private $directives = array ();

	private $actionDefault = '';

	private $currentAction = '';

	private $label = '';

	private $name = '';

	private $component = FALSE;

	private $compPath = '';

	private $father = '';

	private $description = '';

	private $doc = '';

	private $adminAccess = FALSE;

	private $fake = FALSE;

	private $hidden = FALSE;

	private $icon = '';

	const TDEFAULT = '__DEFAULT_SECTION__';
	const TCURRENT = '__CURRENT_SECTION__';

	public function __construct ($input)
	{
		if (!is_array ($input))
			throw new Exception ('Section mapping parameter is not array!');

		if (array_key_exists ('label', $input))
			$this->setLabel ($input ['label']);

		if (array_key_exists ('name', $input))
			$this->setName ($input ['name']);

		if (array_key_exists ('component', $input) && trim ($input ['component']) != '')
			$this->setComponent ($input ['component']);
		else
			$this->fake = TRUE;

		if (array_key_exists ('father', $input))
			$this->setFather ($input ['father']);

		if (array_key_exists ('description', $input))
			$this->setDescription ($input ['description']);

		if (array_key_exists ('doc', $input))
			$this->setDoc ($input ['doc']);

		if (array_key_exists ('admin', $input) && strtoupper ($input ['admin']) == 'TRUE')
			$this->adminAccess = TRUE;

		if (array_key_exists ('hidden', $input) && strtoupper ($input ['hidden']) == 'TRUE')
			$this->hidden = TRUE;

		if (array_key_exists ('icon', $input))
			$this->setIcon ($input ['icon']);

		if (!$this->isFake ())
		{
			$file = 'section/'. $this->getName () .'/config.inc.xml';

			if (!file_exists ($file))
				throw new Exception ('XML file ['. $file .'] not found!');

			$cacheFile = Instance::singleton ()->getCachePath () .'parsed/'. fileName ($file) .'_'. md5_file ($file) .'.php';

			if (file_exists ($cacheFile))
				$array = include $cacheFile;
			else
			{
				$xml = new Xml ($file);

				$array = $xml->getArray ();

				$array = $array ['action-mapping'][0];

				xmlCache ($cacheFile, $array);
			}

			if (array_key_exists ('action', $array))
			{
				if (!is_array ($array ['action']))
					$array ['action'] = array ($array ['action']);

				foreach ($array ['action'] as $key => $action)
				{
					if (!array_key_exists ('name', $action))
						continue;

					if (array_key_exists ('default', $action) && strtoupper ($action ['default']) == 'TRUE')
						$this->actionDefault = $action ['name'];

					$this->actions [$action ['name']] = $action;
				}

				reset ($this->actions);
			}

			if (array_key_exists ('permission', $array))
			{
				if (!is_array ($array ['permission']))
					$array ['permission'] = array ($array ['permission']);

				foreach ($array ['permission'] as $key => $permission)
				{
					if (!array_key_exists ('name', $permission) || trim ($permission ['name']) == '')
						continue;

					if (!array_key_exists ('label', $permission) || trim ($permission ['label']) == '')
						$permission ['label'] = $permission ['name'];

					$this->permissions [$permission ['name']] = $permission;
				}

				reset ($this->permissions);
			}

			if (array_key_exists ('directive', $array))
			{
				if (!is_array ($array ['directive']))
					$array ['directive'] = array ($array ['directive']);

				foreach ($array ['directive'] as $key => $directive)
				{
					if (!array_key_exists ('name', $directive) || trim ($directive ['name']) == '')
						continue;

					if (!array_key_exists ('value', $directive))
						$directive ['value'] = $directive [0];

					$this->directives [$directive ['name']] = $directive ['value'];
				}

				reset ($this->directives);
			}
		}
	}

	public function getAction ($name = FALSE, $force = FALSE)
	{
		if ($name === FALSE)
		{
			$action = each ($this->actions);

			if ($action !== FALSE)
			{
				if (is_object ($action ['value']))
					return $action ['value'];

				$obj = new Action ($action ['value'], $this->getCompPath ());

				$this->actions [$action ['value']['name']] = $obj;

				return $obj;
			}

			reset ($this->actions);

			return NULL;
		}

		switch ($name)
		{
			case Action::TDEFAULT:
				if (trim ($this->actionDefault) == '' || !array_key_exists ($this->actionDefault, $this->actions))
					if ($force)
						throw new Exception (__ ('The requested action do not exists! Maybe its necessary define in configuration file of section [config.inc.xml].'));
					else
						return current ($this->actions);

				if (is_object ($this->actions [$this->actionDefault]))
					return $this->actions [$this->actionDefault];

				$obj = new Action ($this->actions [$this->actionDefault], $this->getCompPath ());

				$this->actions [$this->actionDefault] = $obj;

				return $obj;

			case Action::TCURRENT:
				if (trim ($this->currentAction) == '')
					if (!sizeof ($this->actions))
						return new Action (array ('name' => 'none'), $this->getCompPath ());
					else
						return current ($this->actions);

				if (is_object ($this->actions [$this->currentAction]))
					return $this->actions [$this->currentAction];

				$obj = new Action ($this->actions [$this->currentAction], $this->getCompPath ());

				$this->actions [$this->currentAction] = $obj;

				return $obj;

			case Action::TRSS:
				if (array_key_exists ('_rss', $this->actions) && is_object ($this->actions ['_rss']))
					return $this->actions ['_rss'];

				$obj = new Action (array ('label' => __ ('Feed RSS'), 'name' => '_rss'), $this->getCompPath ());

				$obj->setFreeAccess (TRUE);

				$this->actions ['_rss'] = $obj;

				return $obj;

			case Action::TREGISTER:
				if (array_key_exists ('_register', $this->actions) && is_object ($this->actions ['_register']))
					return $this->actions ['_register'];

				$obj = new Action (array ('label' => __ ('User Register'), 'name' => '_register'), $this->getCompPath ());

				$obj->setFreeAccess (TRUE);

				$this->actions ['_register'] = $obj;

				return $obj;

			case Action::TMODIFY:
				if (array_key_exists ('_modify', $this->actions) && is_object ($this->actions ['_modify']))
					return $this->actions ['_modify'];

				$obj = new Action (array ('label' => __ ('Update Personal Data'), 'name' => '_modify'), $this->getCompPath ());

				$obj->setFreeAccess (TRUE);

				$this->actions ['_modify'] = $obj;

				return $obj;

			case Action::TSCRIPT:
				if (array_key_exists ('_script', $this->actions) && is_object ($this->actions ['_script']))
					return $this->actions ['_script'];

				$obj = new Action (array ('label' => __ ('Script'), 'name' => '_script'), $this->getCompPath ());

				$obj->setFreeAccess (TRUE);

				$this->actions ['_script'] = $obj;

				return $obj;

			case Action::TJOB:
				if (array_key_exists ('_job', $this->actions) && is_object ($this->actions ['_job']))
					return $this->actions ['_job'];

				$obj = new Action (array ('label' => __ ('Job Scheduler'), 'name' => '_job'), $this->getCompPath ());

				$obj->setFreeAccess (TRUE);

				$this->actions ['_job'] = $obj;

				return $obj;

			case Action::TLUCENE:
				if (array_key_exists ('_lucene', $this->actions) && is_object ($this->actions ['_lucene']))
					return $this->actions ['_lucene'];

				$obj = new Action (array ('label' => __ ('Global Search'), 'name' => '_lucene'), $this->getCompPath ());

				$obj->setFreeAccess (TRUE);

				$this->actions ['_lucene'] = $obj;

				return $obj;

			case Action::TAPI:
				if (array_key_exists ('_api', $this->actions) && is_object ($this->actions ['_api']))
					return $this->actions ['_api'];

				$obj = new Action (array ('label' => 'API', 'name' => '_api'), $this->getCompPath ());

				$obj->setFreeAccess (TRUE);

				$this->actions ['_api'] = $obj;

				return $obj;
		}

		if (array_key_exists ($name, $this->actions))
		{
			if (is_object ($this->actions [$name]))
				return $this->actions [$name];

			$obj = new Action ($this->actions [$name], $this->getCompPath ());

			$this->actions [$name] = $obj;

			return $obj;
		}

		if ($force)
			throw new Exception (__ ('The requested action do not exists! Maybe its necessary define in configuration file of section [config.inc.xml].'));

		return $this->getAction (Action::TDEFAULT);
	}

	public function actionExists ($name)
	{
		return array_key_exists ($name, $this->actions);
	}

	public function setCurrent ($action = FALSE)
	{
		if ($action === FALSE)
			$action = isset ($_GET ['toAction']) ? $_GET ['toAction'] : FALSE;

		if ($action === FALSE || (!is_string ($action) && !is_integer ($action)) || !is_array ($this->actions) || !array_key_exists ($action, $this->actions))
			$action = $this->getAction (Action::TDEFAULT)->getName ();
		elseif (!$this->getAction ($action)->freeAccess () && !User::singleton ()->accessAction ($action, $this->getName ()))
		{
			Message::singleton ()->addWarning ('You do not have the necessary permissions to access this action!');

			$action = $this->getAction (Action::TDEFAULT)->getName ();
		}

		$this->currentAction = $action;
	}

	public function getPermission ($name = FALSE)
	{
		if ($name === FALSE)
		{
			$permission = each ($this->permissions);

			if ($permission !== FALSE)
				return $permission ['value'];

			reset ($this->permissions);

			return NULL;
		}

		if (array_key_exists ($name, $this->permissions))
			return $this->permissions [$name];

		return NULL;
	}

	public function getDirective ($name = FALSE)
	{
		if ($name === FALSE)
		{
			$directive = each ($this->directives);

			if ($directive !== FALSE)
				return $directive ['value'];

			reset ($this->directives);

			return NULL;
		}

		if (array_key_exists ($name, $this->directives))
			return $this->directives [$name];

		return NULL;
	}

	public function adminAccessible ()
	{
		return $this->adminAccess;
	}

	public function setLabel ($label)
	{
		$this->label = $label;
	}

	public function getLabel ()
	{
		$this->label = translate ($this->label);

		return translate ($this->label);
	}

	public function setName ($name)
	{
		$this->name = $name;
	}

	public function getName ()
	{
		return $this->name;
	}

	public function setComponent ($component)
	{
		$this->component = $component;

		$alternative = Instance::singleton ()->getReposPath () .'component/'. $component .'/';

		if (file_exists ($component) && is_dir ($component))
			$this->compPath = $component;
		elseif (file_exists ($alternative) && is_dir ($alternative))
			$this->compPath = $alternative;
		else
			throw new Exception ('Not found a valid path to component ['. $component .']. Verify if its name is correct in [business.xml].');
	}

	public function getComponent ()
	{
		return $this->component;
	}

	public function getCompPath ()
	{
		return $this->compPath;
	}

	public function getComponentPath ()
	{
		return $this->getCompPath ();
	}

	public function setFather ($father)
	{
		$this->father = $father;
	}

	public function getFather ()
	{
		return $this->father;
	}

	public function setIcon ($icon)
	{
		$this->icon = trim ($icon);
	}

	public function getIcon ()
	{
		return $this->icon;
	}

	public function setDescription ($description)
	{
		$this->description = $description;
	}

	public function getDescription ()
	{
		$this->description = translate ($this->description);

		return $this->description;
	}

	public function setDoc ($doc)
	{
		$this->doc = $doc;
	}

	public function isFake ()
	{
		return (bool) $this->fake;
	}

	public function isHidden ()
	{
		return (bool) $this->hidden;
	}

	public function getDoc ($action = FALSE)
	{
		$b1 = translate ($this->doc);

		if ($action === FALSE)
		{
			$path = $this->getCompPath () .'_doc/.'. Localization::singleton ()->getLanguage () .'.txt';

			if (file_exists ($path))
			{
				$b2 = file_get_contents ($path);

				if (trim ($b1) != '' && trim ($b2) != '')
					return $b1 ."\n\n". $b2;

				return $b1 . $b2;
			}

			return $b1;
		}

		if (!$this->actionExists ($action))
			return '';

		$b1 = $this->getAction ($action)->getDoc ();

		$path = $this->getCompPath () .'_doc/'. $this->getAction ($action)->getEngine () .'.'. Localization::singleton ()->getLanguage () .'.txt';

		if (file_exists ($path))
		{
			$b2 = file_get_contents ($path);

			if (trim ($b1) != '' && trim ($b2) != '')
				return $b1 ."\n\n". $b2;

			return $b1 . $b2;
		}

		return $b1;
	}

	function getPath ()
	{
		return 'section' . DIRECTORY_SEPARATOR . $this->getName () . DIRECTORY_SEPARATOR;
	}
}
