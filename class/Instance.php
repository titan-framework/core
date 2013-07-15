<?
/**
 * Instance.php
 *
 * This class is used for instantiate a singleton object with contains
 * base configuration of Titan instance.
 *
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @category class
 * @package core
 * @subpackage business_layer
 * @copyright Creative Commons Attribution No Derivatives (CC-BY-ND)
 * @see Business, Section, Action
 */
class Instance
{
	static private $instance = FALSE;
	
	private $array = array ();
	
	private $database = array ();
	
	private $security = array ();
	
	private $schedule = array ();
	
	private $friendlyUrl = array ();
	
	private $search = array ();
	
	private $lucene = array ();
	
	private $archive = array ();
	
	private $business = array ();
	
	private $skin = array ();
	
	private $version = array ();
	
	private $mail = array ();
	
	private $alert = array ();
	
	private $shopping = array ();
	
	private $log = array ();
	
	private $backup = array ();
	
	private $type = array ();
	
	private $types = array ();
	
	private $tool = array ();
	
	private $tools = array ();
	
	private $cachePath = '/dev/null';
	
	private final function __construct ()
	{
		$file = 'configure/titan.xml';
		
		if (!file_exists ($file))
			throw new Exception ('Dont exists a valid instance of Titan and is not possible create a new without file ['. $file .'].');
		
		$this->array = array (	'name' 			=> '',
								'description' 	=> '',
								'e-mail' 		=> '',
								'url'			=> '',
								'login-url' 	=> '',
								'core-path' 	=> '',
								'repos-path'	=> '',
								'debug-mode' 	=> FALSE,
								'session'		=> '',
								'cache-path'	=> '',
								'use-chat'		=> TRUE,
								'timezone'		=> 'America/Campo_Grande',
								'only-firefox'	=> FALSE,
								'language'		=> array ('pt_BR'),
								'all-sections'	=> TRUE,
								'author'		=> '',
								'doc-path'		=> '');
		
		$xml = new Xml ($file);
		
		$array = $xml->getArray ();
		
		if (!isset ($array ['titan-configuration'][0]))
			throw new Exception ('The tag &lt;titan-configuration&gt;&lt;/titan-configuration&gt; dont exist in file ['. $file .'].');
		
		$array = $array ['titan-configuration'][0];
		
		foreach ($this->array as $key => $trash)
			if (array_key_exists ($key, $array) && trim ($array [$key]) != '')
				if (is_bool ($this->array [$key]))
					$this->array [$key] = strtoupper ($array [$key]) == 'TRUE' ? TRUE : FALSE;
				elseif (is_array ($this->array [$key]))
					$this->array [$key] = explode (',', $array [$key]);
				else
					$this->array [$key] = $array [$key];
		
		if (!in_array ($this->array ['timezone'], DateTimeZone::listIdentifiers ()))
			throw new Exception ('The value of attribute [timezone] of tag &lt;titan-configuration&gt;&lt;/titan-configuration&gt; in [titan.xml] is incorrect.');
		
		if (isset ($_COOKIE['_TITAN_TIMEZONE_']) && trim ($_COOKIE['_TITAN_TIMEZONE_']) != '')
			$this->array ['timezone'] = $_COOKIE['_TITAN_TIMEZONE_'];
		
		date_default_timezone_set ($this->array ['timezone']);
		
		if (array_key_exists ('database', $array))
			$this->database = $array ['database'][0];
		
		if (array_key_exists ('security', $array))
			$this->security = $array ['security'][0];
		
		if (array_key_exists ('schedule', $array))
			$this->schedule = $array ['schedule'][0];
		
		if (array_key_exists ('friendly-url', $array))
			$this->friendlyUrl = $array ['friendly-url'][0];
		
		if (array_key_exists ('search', $array))
			$this->search = $array ['search'][0];
		
		if (array_key_exists ('lucene', $array))
			$this->lucene = $array ['lucene'][0];
		
		if (array_key_exists ('archive', $array))
			$this->archive = $array ['archive'][0];
		
		if (array_key_exists ('mail', $array))
			$this->mail = $array ['mail'][0];
		
		if (array_key_exists ('alert', $array))
			$this->alert = $array ['alert'][0];
		
		if (array_key_exists ('shopping', $array))
			$this->shopping = $array ['shopping'][0];
		
		if (array_key_exists ('backup', $array))
			$this->backup = $array ['backup'][0];
		
		if (array_key_exists ('type', $array))
			$this->type = $array ['type'][0];
		
		if (array_key_exists ('tool', $array))
			$this->tool = $array ['tool'][0];
		
		if (array_key_exists ('business-layer', $array))
			$this->business = $array ['business-layer'][0];
		
		if (array_key_exists ('skin', $array))
			$this->skin = $array ['skin'][0];
		
		if (array_key_exists ('version-control', $array))
			$this->version = $array ['version-control'][0];
		
		if (array_key_exists ('log', $array))
			$this->log = $array ['log'][0];
		
		if (!file_exists ($this->getReposPath () .'type/type.xml'))
			throw new Exception ('A propriedade [repos-path] da tag &lt;titan-configuration&gt;&lt;/titan-configuration&gt; do [titan.xml] está incorreta ou o diretório de <i>Tipos</i> não está presente no repositório.');
		
		$file = $this->getReposPath () .'type/type.xml';
		
		$cacheFile = $this->getCachePath () .'parsed/'. fileName ($file) .'_'. md5_file ($file) .'.php';
		
		if (file_exists ($cacheFile))
			$aux = include $cacheFile;
		else
		{
			$xml = new Xml ($file);
			
			$aux = $xml->getArray ();
			
			if (!isset ($aux ['type-mapping'][0]['type']))
				throw new Exception ('A tag &lt;type-mapping&gt;&lt;/type-mapping&gt; não existe no arquivo ['. $file .'].');
			
			xmlCache ($cacheFile, $aux, $this->getCachePath () .'parsed/');
		}
		
		$aux = $aux ['type-mapping'][0]['type'];
		
		foreach ($aux as $trash => $type)
			if (array_key_exists ('component', $type) && file_exists ($this->getReposPath () .'type/'. $type ['component'] .'/'. $type ['name'] .'.php'))
				$this->types [$type ['name']] = $this->getReposPath () .'type/'. $type ['component'] .'/';
		
		if (is_array ($this->type) && array_key_exists ('xml-path', $this->type) && trim ($this->type ['xml-path']) != '')
		{
			$file = $this->type ['xml-path'];
			
			if (!file_exists ($file))
				throw new Exception ('O arquivo de configuração de Tipos Locais não existe no caminho ['. $file .'].');
			
			$cacheFile = $this->getCachePath () .'parsed/'. fileName ($file) .'_'. md5_file ($file) .'.php';
			
			if (file_exists ($cacheFile))
				$aux = include $cacheFile;
			else
			{
				$xml = new Xml ($file);
				
				$aux = $xml->getArray ();
				
				if (!isset ($aux ['type-mapping'][0]['type']))
					throw new Exception ('A tag &lt;type-mapping&gt;&lt;/type-mapping&gt; não existe no arquivo ['. $file .'].');
				
				xmlCache ($cacheFile, $aux, $this->getCachePath () .'parsed/');
			}
			
			$aux = $aux ['type-mapping'][0]['type'];
			
			foreach ($aux as $trash => $type)
				if (array_key_exists ('path', $type) && file_exists ($type ['path'] . $type ['name'] .'.php'))
					$this->types [$type ['name']] = $type ['path'];
		}
		
		reset ($this->types);
		
		if (is_array ($this->tool) && array_key_exists ('xml-path', $this->tool) && trim ($this->tool ['xml-path']) != '')
		{
			$file = $this->tool ['xml-path'];
			
			if (!file_exists ($file))
				throw new Exception ('O arquivo de configuração de Ferramentas Locais não existe no caminho ['. $file .'].');
			
			$cacheFile = $this->getCachePath () .'parsed/'. fileName ($file) .'_'. md5_file ($file) .'.php';
			
			if (file_exists ($cacheFile))
				$aux = include $cacheFile;
			else
			{
				$xml = new Xml ($file);
				
				$aux = $xml->getArray ();
				
				if (!isset ($aux ['tool-mapping'][0]['tool']))
					throw new Exception ('A tag &lt;tool-mapping&gt;&lt;/tool-mapping&gt; não existe no arquivo ['. $file .'].');
				
				xmlCache ($cacheFile, $aux, $this->getCachePath () .'parsed/');
			}
			
			$aux = $aux ['tool-mapping'][0]['tool'];
			
			foreach ($aux as $trash => $tool)
				if (array_key_exists ('name', $tool) && array_key_exists ('bootstrap', $tool) && file_exists ($tool ['bootstrap']))
					$this->tools [$tool ['name']] = $tool ['bootstrap'];
		}
	}
	
	static public function singleton ()
	{
		if (self::$instance !== FALSE)
			return self::$instance;
		
		$class = __CLASS__;
		
		self::$instance = new $class ();
		
		return self::$instance;
	}
	
	public function getName ()
	{
		return $this->array ['name'];
	}
	
	public function getDescription ()
	{
		return $this->array ['description'];
	}
	
	public function getEmail ()
	{
		return $this->array ['e-mail'];
	}
	
	public function getLoginUrl ()
	{
		return $this->array ['login-url'];
	}
	
	public function getUrl ()
	{
		return $this->array ['url'];
	}
	
	public function getCorePath ()
	{
		return $this->array ['core-path'];
	}
	
	public function getCachePath ()
	{
		return $this->array ['cache-path'];
	}
	
	public function getReposPath ()
	{
		return $this->array ['core-path'] . 'repos/';
	}
	
	public function onDebugMode ()
	{
		return $this->array ['debug-mode'] === TRUE;
	}
	
	public function getSession ()
	{
		return $this->array ['session'];
	}
	
	public function useChat ()
	{
		return $this->array ['use-chat'] === TRUE;
	}
	
	public function getTimeZone ()
	{
		return $this->array ['timezone'];
	}
	
	public function setTimeZone ($tz)
	{
		if (!in_array ($tz, DateTimeZone::listIdentifiers ()))
			return FALSE;
		
		$this->array ['timezone'] = $tz;
		
		$_COOKIE['_TITAN_TIMEZONE_'] = $tz;
		
		date_default_timezone_set ($tz);
	}
	
	public function onlyFirefox ()
	{
		return $this->array ['only-firefox'];
	}
	
	public function getLanguages ()
	{
		return $this->array ['language'];
	}
	
	public function setLanguages ($language)
	{
		$this->array ['language'] = is_array ($language) ? $language : array ($language);
	}
	
	public function showAllSections ()
	{
		return $this->array ['all-sections'];
	}
	
	public function getAuthor ()
	{
		return $this->array ['author'];
	}
	
	public function getDocPath ()
	{
		return trim ($this->array ['doc-path']);
	}
	
	public function getFriendlyUrl ($link)
	{
		if (array_key_exists ($link, $this->friendlyUrl))
			return trim ($this->friendlyUrl [$link]);
		
		return '';
	}
	
	public function getBusiness ()
	{
		return $this->business;
	}
	
	public function getDatabase ()
	{
		return $this->database;
	}
	
	public function getSecurity ()
	{
		return $this->security;
	}
	
	public function getSchedule ()
	{
		return $this->schedule;
	}
	
	public function getSearch ()
	{
		return $this->search;
	}
	
	public function getLucene ()
	{
		return $this->lucene;
	}
	
	public function getArchive ()
	{
		return $this->archive;
	}
	
	public function getMail ()
	{
		return $this->mail;
	}
	
	public function getAlert ()
	{
		return $this->alert;
	}
	
	public function getShopping ()
	{
		return $this->shopping;
	}
	
	public function getBackup ()
	{
		return $this->backup;
	}
	
	public function getSkin ()
	{
		return $this->skin;
	}
	
	public function getVersionControl ()
	{
		return $this->version;
	}
	
	public function getLog ()
	{
		return $this->log;
	}
	
	public function getTypes ()
	{
		return $this->types;
	}
	
	public function getTypePath ($type)
	{
		if (!array_key_exists ($type, $this->types))
			return NULL;
		
		return $this->types [$type];
	}
	
	public function typeExists ($type)
	{
		return array_key_exists ($type, $this->types);
	}
	
	public function getTools ()
	{
		return $this->tools;
	}
}
?>