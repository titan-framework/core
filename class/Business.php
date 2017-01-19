<?php
/**
 * This class contains logic mapping for business layer.
 *
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @category class
 * @package core
 * @subpackage business
 * @copyright 2005-2017 Titan Framework
 * @license http://www.titanframework.com/license/ BSD License (3 Clause)
 * @see Instance, Section, Action
 */
class Business
{
	static private $business = FALSE;

	private $sections = array ();

	private $sectionDefault = '';

	private $currentSection = '';

	private final function __construct ()
	{
		$array = Instance::singleton ()->getBusiness ();

		if (!array_key_exists ('xml-path', $array))
			throw new Exception ('Não foi encontrada a propriedade [xml-path] na tag &lt;business-layer&gt;&lt;/business-layer&gt; do arquivo [configure/titan.xml]!');

		$file = $array ['xml-path'];

		if (!file_exists ($file))
			throw new Exception ('O arquivo de configuração da Camada de Negócios do Titan não existe no caminho ['. $file .'].');

		$cacheFile = Instance::singleton ()->getCachePath () .'parsed/'. fileName ($file) .'_'. md5_file ($file) .'.php';

		if (file_exists ($cacheFile))
			$array = include $cacheFile;
		else
		{
			$xml = new Xml ($file);

			$array = $xml->getArray ();

			$array = $array ['section-mapping'][0];

			xmlCache ($cacheFile, $array);
		}

		if (array_key_exists ('section', $array))
		{
			if (!is_array ($array ['section']))
				$array ['section'] = array ($array ['section']);

			foreach ($array ['section'] as $key => $section)
			{
				if (!array_key_exists ('name', $section) || array_key_exists ($section ['name'], $this->sections))
					continue;

				if (array_key_exists ('default', $section) && strtoupper ($section ['default']) == 'TRUE')
					$this->sectionDefault = $section ['name'];

				$this->sections [$section ['name']] = new Section ($section);
			}

			reset ($this->sections);
		}
	}

	static public function singleton ()
	{
		if (self::$business !== FALSE)
			return self::$business;

		$class = __CLASS__;

		self::$business = new $class ();

		return self::$business;
	}

	static public function reload ()
	{
		self::$business = FALSE;
	}

	public function getSection ($name = FALSE, $listFake = FALSE)
	{
		if ($name === FALSE)
		{
			do
			{
				$section = each ($this->sections);
			} while ($section !== FALSE && !$listFake && $section ['value']->isFake ());

			if ($section !== FALSE)
				return $section ['value'];

			reset ($this->sections);

			return NULL;
		}

		if ($name === Section::TDEFAULT)
		{
			if (trim ($this->sectionDefault) == '')
				return current ($this->sections);

			return $this->sections [$this->sectionDefault];
		}

		if ($name === Section::TCURRENT)
		{
			if (trim ($this->currentSection) == '')
				return current ($this->sections);

			return $this->sections [$this->currentSection];
		}

		if (array_key_exists ($name, $this->sections))
			return $this->sections [$name];

		return NULL;
	}

	public function getAction ($name = FALSE, $force = FALSE)
	{
		$section = $this->getSection (Section::TCURRENT);

		return $section->getAction ($name, $force);
	}

	public function setCurrent ($section = FALSE, $action = FALSE)
	{
		$section = is_object ($section) ? $section->getName () : $section;

		$action = is_object ($action) ? $action->getName () : $action;

		$public = array ('_rss', '_script', '_job', '_modify', '_api');

		if ($section === FALSE)
			$section = isset ($_GET ['toSection']) ? $_GET ['toSection'] : FALSE;

		if ($section === FALSE || !array_key_exists ($section, $this->sections))
			$section = $this->getSection (Section::TDEFAULT)->getName ();
		elseif (!in_array ($action, $public) && (!Security::singleton ()->allowRegister ($section) && !User::singleton ()->accessSection ($section)))
		{
			Message::singleton ()->addWarning (__ ('You dont have permission to access this section! ['. $section .'/'. $action .']'));

			$section = $this->getSection (Section::TDEFAULT)->getName ();
		}

		$this->currentSection = $section;

		$this->sections [$section]->setCurrent ($action);
	}

	public function sectionExists ($name)
	{
		return array_key_exists ($name, $this->sections);
	}

	public function getChildren ($father)
	{
		$array = array ();
		while ($section = $this->getSection (FALSE, TRUE))
			if ($section->getFather () == $father)
				$array [$section->getName ()] = $section;

		return $array;
	}

	public function setSectionDefault ($s)
	{
		$s = trim ($s);

		if ($s == '' || !$this->sectionExists ($s))
			return FALSE;

		$this->sectionDefault = $s;

		return TRUE;
	}
}
