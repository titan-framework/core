<?
class Localization
{
	static private $locale = FALSE;
	
	private $language = 'en_US';
	
	private $enabled = array ('en_US');
	
	private $packs = array ();
	
	static private $supported = array (	'pt_BR' => 'Português',
										'es_ES' => 'Espanol',
										'en_US' => 'English');
	
	static private $negotiate = array (	'pt'	=> 'pt_BR',
										'pt-br' => 'pt_BR',
										'es'	=> 'es_ES',
										'es-mx' => 'es_ES',
										'es-gt' => 'es_ES',
										'es-cr' => 'es_ES',
										'es-do' => 'es_ES',
										'es-pa' => 'es_ES',
										'es-ve' => 'es_ES',
										'es-co' => 'es_ES',
										'es-pe' => 'es_ES',
										'es-ar' => 'es_ES',
										'es-ec' => 'es_ES',
										'es-cl' => 'es_ES',
										'es-uy' => 'es_ES',
										'es-py' => 'es_ES',
										'es-bo' => 'es_ES',
										'es-sv' => 'es_ES',
										'es-hn' => 'es_ES',
										'es-ni' => 'es_ES',
										'es-pr' => 'es_ES',
										'en'	=> 'en_US',
										'en-us' => 'en_US',
										'en-gb' => 'en_US',
										'en-au' => 'en_US',
										'en-ca' => 'en_US',
										'en-nz' => 'en_US',
										'en-ie' => 'en_US',
										'en-za' => 'en_US',
										'en-jm' => 'en_US',
										'en-bz' => 'en_US',
										'en-tt' => 'en_US');
	
	static private $winLocales = array ('pt_BR'	=> 'ptb',
										'en_US' => 'usa',
										'es_ES' => 'esp');
	
	private $forComponent = '';
	
	private final function __construct ()
	{
		$languages = Instance::singleton ()->getLanguages ();
		
		$array = array ();
		foreach ($languages as $trash => $language)
		{
			$language = trim ($language);
			
			if (!self::supports ($language))
				continue;
			
			$array [] = $language;
		}
		
		if ((int) sizeof ($array))
			$this->enabled = $array;
		else
			$array = $this->enabled;
		
		if (isset ($_GET ['language']))
			$language = trim ($_GET ['language']);
		if (isset ($_COOKIE ['_TITAN_LOCALE_']) && trim ($_COOKIE ['_TITAN_LOCALE_']) != '')
			$language = trim ($_COOKIE ['_TITAN_LOCALE_']);
		else
			$language = self::negotiatedLanguage ();
		
		if (trim ($language) == '' || !in_array ($language, $array))
			$language = array_shift ($array);
		
		$this->setLanguage ($language);
	}
	
	static public function singleton ()
	{
		if (self::$locale !== FALSE)
			return self::$locale;
		
		$class = __CLASS__;
		
		self::$locale = new $class ();
		
		return self::$locale;
	}
	
	public function setLanguage ($language)
	{
		$language = trim ($language);
		
		if (!self::supports ($language))
			return FALSE;
		
		setcookie ('_TITAN_LOCALE_', $language);
		
		$this->language = $language;
		
		if (setlocale (LC_ALL, $this->getLocale ()) === FALSE)
			toLog ('Impossible to set system locale ['. $this->getLocale () .'].');
		
		return TRUE;
	}
	
	public function getLanguage ()
	{
		return $this->language;
	}
	
	public function getLocale ()
	{
		if (stristr (PHP_OS, 'win') !== FALSE)
			return self::$winLocales [$this->getLanguage ()];
		
		return $this->getLanguage ();
	}
	
	public function translate ($array)
	{
		if (!sizeof ($array))
			return '';
		
		$message = array_shift ($array);
		
		$pack = $this->getLanguagePack ();
		
		if (isset ($pack [$message]))
			$message = $pack [$message];
		
		foreach ($array as $key => $value)
			$message = str_replace ('['. ($key + 1) .']', $value, $message);
		
		return $message;
	}
	
	private function getLanguagePack ($language = FALSE)
	{
		if ($language === FALSE)
			$language = $this->language;
		
		$language = trim ($language);
		
		$section = Business::singleton ()->getSection (Section::TCURRENT);
		
		if (array_key_exists ($language, $this->packs) && $this->forComponent == $section->getComponent ())
			return $this->packs [$language];
		
		$this->forComponent = $section->getComponent ();
		
		$array = array ();
		
		$files = array (Instance::singleton ()->getCorePath ().'locale/i18n/'. $language .'.xml', $section->getCompPath () .'_i18n/'. $language .'.xml');
		
		$packs = array (Instance::singleton ()->getCachePath () .'i18n/'. $language .'.php', Instance::singleton ()->getCachePath () .'i18n/'. $language .'-component-'. fileName ($section->getComponent ()) .'.php');
		
		foreach (Instance::singleton ()->getTypes () as $type => $path)
		{
			$files [] = $path .'_i18n/'. $language .'.xml';
			$packs [] = Instance::singleton ()->getCachePath () .'i18n/'. $language .'-type-'. fileName ($type) .'.php';
		}
		
		foreach ($files as $key => $file)
		{
			if (!file_exists ($file))
				continue;
			
			$pack = $packs [$key];
			
			if (file_exists ($pack) && @filemtime ($pack) > @filemtime ($file))
			{
				$array = array_merge ($array, include $pack);
				
				continue;
			}
			
			$xml = new Xml ($file);
			
			$aux = $xml->getArray ();
			
			if (!isset ($aux ['i18n'][0]['message']) || !sizeof ($aux ['i18n'][0]['message']))
				continue;
			
			$aFile = array ();
			
			foreach ($aux ['i18n'][0]['message'] as $trash => $message)
			{
				if (!is_array ($message) || !array_key_exists ('from', $message) || !array_key_exists ('to', $message))
					continue;
				
				$aFile [$message ['from']] = $message ['to'];
			}
			
			$array = array_merge ($array, $aFile);
			
			$content  = "<? \n";
			$content .= "/* Language Pack (i18n) - ". date ('d-m-Y H:i:s') ." */ \n\n";
			$content .= "return ". var_export ($aFile, TRUE) ."; \n";
			$content .= "?>";
			
			$path = Instance::singleton ()->getCachePath () .'i18n/';
			
			if (!file_exists ($path) && !@mkdir ($path, 0777))
				toLog ('Impossible to create directory ['. $path .'].');
			elseif (file_put_contents ($pack, $content) === FALSE)
				toLog ('Impossible to create file ['. $pack .'].');
		}
		
		$this->packs [$language] = $array;
		
		return $array;
	}
	
	private static function supports ($language)
	{
		return array_key_exists ($language, self::$supported);
	}
	
	public static function negotiatedLanguage ()
	{
		if (!array_key_exists ('HTTP_ACCEPT_LANGUAGE', $_SERVER))
			return '';
		
		$client = str_replace (array (',', ';'), '#', strtolower ($_SERVER ['HTTP_ACCEPT_LANGUAGE']));
		
		$languages = explode ('#', $client);
		
		foreach ($languages as $trash => $lang)
			if (array_key_exists ($lang, self::$negotiate))
				return self::$negotiate [$lang];
		
		return '';
	}
	
	public function getAvaliableLanguages ()
	{
		$array = array ();
		foreach (self::$supported as $language => $label)
			if (in_array ($language, $this->enabled))
				$array [$language] = $label;
		
		return $array;
	}
}
?>