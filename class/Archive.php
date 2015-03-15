<?
class Archive
{
	static private $archive = FALSE;

	private $mimeTypes = array ();

	private $fileTable = '';

	private $dataPath = '/dev/null';

	const OPEN = '_FILE_OPEN_';
	const IMAGE = '_FILE_IMAGE_';
	const VIDEO = '_FILE_VIDEO_';
	const AUDIO = '_FILE_AUDIO_';
	const DOWNLOAD = '_FILE_DOWNLOAD_';

	private final function __construct ()
	{
		$array = Instance::singleton ()->getArchive ();

		if (!array_key_exists ('data-path', $array) || trim ($array ['data-path']) == '')
			throw new Exception (__ ('The property [data-path] was not found or its empty in the tag <archive></archive> from file [configure/titan.xml]!'));

		$this->dataPath = $array ['data-path'];

		if (!array_key_exists ('xml-path', $array))
			throw new Exception (__ ('The property [xml-path] was not found in the tag <archive></archive> of file [configure/titan.xml]!'));

		$file = $array ['xml-path'];

		if (!file_exists ($file))
			throw new Exception (__ ('The Titan configuration file system does not exist at path [ [1] ]', $file));

		$cacheFile = Instance::singleton ()->getCachePath () .'parsed/'. fileName ($file) .'_'. md5_file ($file) .'.php';

		if (file_exists ($cacheFile))
			$array = include $cacheFile;
		else
		{
			$xml = new Xml ($file);

			$array = $xml->getArray ();

			$array = $array ['archive-mapping'][0];

			xmlCache ($cacheFile, $array);
		}

		if (array_key_exists ('mime-type', $array))
		{
			if (!is_array ($array ['mime-type']))
				$array ['mime-type'] = array ($array ['mime-type']);

			foreach ($array ['mime-type'] as $key => $mime)
				if (array_key_exists (0, $mime))
				{
					$key = $mime [0];

					unset ($mime [0]);

					$this->mimeTypes [$key] = $mime;
				}
		}
	}

	static public function singleton ()
	{
		if (self::$archive !== FALSE)
			return self::$archive;

		$class = __CLASS__;

		self::$archive = new $class ();

		return self::$archive;
	}

	public function getDataPath ()
	{
		return $this->dataPath;
	}
	
	public function getFilePath ($id)
	{
		return file_exists (File::getLegacyFilePath ($id)) ? File::getLegacyFilePath ($id) : File::getFilePath ($id);
	}

	public function isAcceptable ($mime, $assume = FALSE)
	{
		if ($assume !== FALSE && $assume != $this->getAssume ($mime))
			return FALSE;

		return array_key_exists ($mime, $this->mimeTypes);
	}

	public function getInfo ($mime)
	{
		if (!array_key_exists ($mime, $this->mimeTypes))
			return array ();

		return $this->mimeTypes [$mime];
	}

	public function getAllMimes ()
	{
		return $this->mimeTypes;
	}
	
	public function getMimesByType ($type)
	{
		switch ($type)
		{
			case self::IMAGE:
				$assume = 'image';
				break;
			
			case self::VIDEO:
				$assume = 'video';
				break;
			
			case self::AUDIO:
				$assume = 'audio';
				break;
			
			case self::DOWNLOAD:
				$assume = 'download';
				break;
			
			case self::OPEN:
				$assume = 'open';
				break;
			
			default:
				return array ();
		}
		
		$array = array ();
		
		foreach ($this->mimeTypes as $mime => $data)
			if ($data ['assume'] == $assume)
				$array [] = $mime;
		
		return $array;
	}

	public function getAssume ($mime)
	{
		if (!array_key_exists ($mime, $this->mimeTypes))
			return self::DOWNLOAD;

		if (!array_key_exists ('assume', $this->mimeTypes [$mime]))
			return self::DOWNLOAD;

		switch ($this->mimeTypes [$mime]['assume'])
		{
			case 'image':
				return self::IMAGE;

			case 'video':
				return self::VIDEO;
			
			case 'audio':
				return self::AUDIO;
			
			case 'open':
				return self::OPEN;
		}

		return self::DOWNLOAD;
	}

	public function getFilter ($assume = FALSE)
	{
		$aux = array ();

		foreach ($this->mimeTypes as $mime => $trash)
			if ($assume === FALSE || $this->getAssume ($mime) == $assume)
				$aux [] = $mime;

		return implode (',', $aux);
	}

	public function getIcon ($mime)
	{
		if (!array_key_exists ($mime, $this->mimeTypes))
			return 'file';

		if (!array_key_exists ('icon', $this->mimeTypes [$mime]))
			return 'file';

		return $this->mimeTypes [$mime]['icon'];
	}

	public function getMimeByExtension ($extension)
	{
		foreach ($this->mimeTypes as $mime => $array)
		{
			if (array_key_exists ('extension', $array) && $array ['extension'] == $extension)
				return $mime;
			
			if (array_key_exists ('icon', $array) && $array ['icon'] == $extension)
				return $mime;
		}

		return NULL;
	}
	
	public function getExtensionByMime ($mime)
	{
		if (!array_key_exists ($mime, $this->mimeTypes))
			return '';
		
		if (array_key_exists ('extension', $this->mimeTypes [$mime]) && trim ($this->mimeTypes [$mime]['extension']) != '')
			return $this->mimeTypes [$mime]['extension'];
		
		if (array_key_exists ('icon', $this->mimeTypes [$mime]) && trim ($this->mimeTypes [$mime]['icon']) != '')
			return $this->mimeTypes [$mime]['icon'];
		
		return '';
	}
	
	public function getUploadLimit ()
	{
		return self::getServerUploadLimit ();
	}

	public static function mimeType ($file)
	{
		if (function_exists ('finfo_open'))
		{
			$finfo = finfo_open (FILEINFO_MIME);

			$mimeType = finfo_file ($finfo, $file);

			finfo_close ($finfo);

			return $mimeType;
		}

		if (function_exists ('mime_content_type'))
			return mime_content_type ($file);

		return NULL;
	}
	
	public static function getServerUploadLimit ()
	{
		$upload = (int) (ini_get ('upload_max_filesize'));
		
		$post = (int) (ini_get ('post_max_size'));
		
		$memory = (int) (ini_get ('memory_limit'));
		
		return min ($upload, $post, $memory);
	}
	
	public static function is3GPPVideo ($file)
    {
		if (!file_exists ($file) || !is_readable ($file) || !(int) filesize ($file))
			return FALSE;
		
        return strpos (file_get_contents ($file), 'vide') !== FALSE;
    }
}
?>