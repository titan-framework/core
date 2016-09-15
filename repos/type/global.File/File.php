<?php

class File extends Integer
{
	const ENCODE_FOLDER = 'file';
	
	protected $mimeTypes = array ();
	
	protected $ownerOnly = FALSE;
	
	protected $public = TRUE;
	
	protected $resolution = 200;
		
	public function __construct ($table, $field)
	{
		if (!Database::tableExists ('_file'))
			throw new Exception ('The mandatory table [_file] do not exists! Its necessary to use type File.');
		
		parent::__construct ($table, $field);
		
		if (array_key_exists ('owner-only', $field))
			$this->setOwnerOnly (strtoupper (trim ($field ['owner-only'])) == 'TRUE' ? TRUE : FALSE);
		
		if (array_key_exists ('public', $field))
			$this->setPublic (strtoupper (trim ($field ['public'])) == 'TRUE' ? TRUE : FALSE);
		
		if (array_key_exists ('resolution', $field) && is_numeric ($field ['resolution']))
			$this->resolution = (int) trim ($field ['resolution']);
		
		if (array_key_exists ('mime-type', $field))
		{
			$archive = Archive::singleton ();
			
			if (!is_array ($field ['mime-type']))
				$field ['mime-type'] = array ($field ['mime-type']);
			
			foreach ($field ['mime-type'] as $trash => $item)
				if ($archive->isAcceptable ($item))
					$this->mimeTypes [] = $item;
		}
	}
	
	public function setValue ($value)
	{
		if (is_null ($value) || (is_numeric ($value) && (int) $value === 0) || (is_string ($value) && $value === ''))
			$this->value = NULL;
		else
			$this->value = $value;
	}
	
	public function setPublic ($public)
	{
		$this->public = (bool) $public;
	}
	
	public function isPublic ()
	{
		return $this->public;
	}
	
	public function setOwnerOnly ($ownerOnly)
	{
		$this->ownerOnly = (bool) $ownerOnly;
	}
	
	public function ownerOnly ()
	{
		return $this->ownerOnly;
	}
	
	public function getFilter ()
	{
		return implode (',', $this->mimeTypes);
	}
	
	public function isAcceptable ($mime)
	{
		if (sizeof ($this->mimeTypes))
			return in_array ($mime, $this->mimeTypes);
		
		return Archive::singleton ()->isAcceptable ($mime);
	}
	
	public function getInfo ()
	{
		if (!$this->getValue ())
			return NULL;
		
		$sth = Database::singleton ()->prepare ("SELECT * FROM _file WHERE _id = :id");
		
		$sth->bindParam (':id', $this->getValue (), PDO::PARAM_INT);
		
		$sth->execute ();
		
		$obj = $sth->fetch (PDO::FETCH_OBJ);
		
		if (!$obj)
			return NULL;
		
		return array ('_NAME_' => $obj->_name,
					  '_SIZE_' => $obj->_size,
					  '_MIME_' => $obj->_mimetype,
					  '_HASH_' => (string) @$obj->_hash);
	}
	
	public function isEmpty ()
	{
		return is_null ($this->getValue ()) || !is_numeric ($this->getValue ()) || $this->getValue () == 0;
	}
	
	public function getResolution ()
	{
		return $this->resolution;
	}
	
	public static function getFilePath ($id)
	{
		return Archive::singleton ()->getDataPath () . 'file_' . str_pad ($id, 19, '0', STR_PAD_LEFT);
	}
	
	public static function getLegacyFilePath ($id)
	{
		return Archive::singleton ()->getDataPath () . 'file_' . str_pad ($id, 7, '0', STR_PAD_LEFT);
	}
	
	public static function formatFileSizeForHuman ($bytes, $decimals = 0)
	{
		$size = array ('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
		
		$factor = floor ((strlen ($bytes) - 1) / 3);
		
		return sprintf ("%.{$decimals}f", $bytes / pow (1024, $factor)) .' '. @$size [$factor];
	}
	
	public static function resize ($id, $type, $width = 0, $height = 0, $force = FALSE, $bw = FALSE, $crop = FALSE)
	{
		$source = self::getFilePath ($id);
		
		if (!file_exists ($source))
			$source = self::getLegacyFilePath ($id);
		
		$cache = Instance::singleton ()->getCachePath ();
		
		if (!file_exists ($cache . self::ENCODE_FOLDER) && !@mkdir ($cache . self::ENCODE_FOLDER, 0777))
			throw new Exception ('Unable create cache directory!');
		
		if (!file_exists ($cache . self::ENCODE_FOLDER . DIRECTORY_SEPARATOR .'.htaccess') && !file_put_contents ($cache . self::ENCODE_FOLDER . DIRECTORY_SEPARATOR .'.htaccess', 'deny from all'))
			throw new Exception ('Impossible to enhance security for folder ['. $cache . self::ENCODE_FOLDER .'].');
		
		$destination = $cache . self::ENCODE_FOLDER . DIRECTORY_SEPARATOR .'resized_' . str_pad ($id, 19, '0', STR_PAD_LEFT) .'_'. $width .'x'. $height .'_'. ($force ? '1' : '0') .'_'. ($bw ? '1' : '0') .'_'. ($crop ? '1' : '0');
		
		return EncodeMedia::resizeImage ($source, $type, $destination, $width, $height, $force, $bw, $crop);
	}
	
	public static function synopsis ($id, $filter = array (), $dimension = 200)
	{
		$path = self::getFilePath ($id);
		
		if (!file_exists ($path))
			$path = self::getLegacyFilePath ($id);
		
		if (!file_exists ($path))
			throw new Exception (__ ('The file does not physically exist on the system.'));
		
		try
		{
			$db = Database::singleton ();
			
			$sth = $db->prepare ("SELECT c.*, u._name AS user, u._email AS email,
								  EXTRACT (EPOCH FROM c._create_date) AS taken
								  FROM _file c 
								  LEFT JOIN _user u ON u._id = c._user
								  WHERE c._id = :id");
			
			$sth->bindParam (':id', $id, PDO::PARAM_INT);
			
			$sth->execute ();
			
			$obj = $sth->fetch (PDO::FETCH_OBJ);
		}
		catch (PDOException $e)
		{
			toLog ('['. $e->getLine () .'] '. $e->getMessage ());
			
			throw new Exception (__ ('There was a severe error when trying to load file! Please, contact your administrator.'));
		}
		
		if (!$obj)
			throw new Exception (__ ('There is no associated file!'));
		
		$archive = Archive::singleton ();
		
		if (!$archive->isAcceptable ($obj->_mimetype))
			throw new Exception (__ ('This type of file is not accepted by the system ([1])!', $obj->_mimetype));
		
		if (is_array ($filter) && (int) sizeof ($filter) && !in_array ($obj->_mimetype, $filter))
		{
			$types = array ();
			
			foreach ($filter as $trash => $mime)
			{
				$aux = trim ($archive->getExtensionByMime ($mime));
				
				if (empty ($aux))
					continue;
				
				$types [] = strtoupper ($aux);
			}
			
			throw new Exception (__ ('This type of file ([1]) is not accept at this field! Files accepts are: [2].', $obj->_mimetype, implode (', ', $types)));
		}
		
		$hashQueryString = '';
		
		if (!is_null (@$obj->_public) && !(int) $obj->_public)
		{
			if (is_null (@$obj->_hash) || strlen (trim ($obj->_hash)) != 32)
				throw new Exception (__ ('Has a critical error with this file! Please, contact your administrator.'));
		
			$hashQueryString = '&hash='. $obj->_hash;
		}
		
		$alt = $obj->_name ." (". File::formatFileSizeForHuman ($obj->_size) ." &bull; ". $obj->_mimetype .") \n". __ ('By [1] ([2]) on [3].', $obj->user, $obj->email, strftime ('%x %X', $obj->taken));
		
		ob_start ();
		
		switch ($archive->getAssume ($obj->_mimetype))
		{
			case Archive::IMAGE:
				?>
				<a href="titan.php?target=tScript&type=File&file=open&id=<?= $id ?><?= $hashQueryString ?>" target="_blank" title="<?= $alt ?>"><img src="titan.php?target=tScript&type=File&file=thumbnail&fileId=<?= $id ?>&height=<?= $dimension ?><?= $hashQueryString ?>" alt="<?= $alt ?>" border="0" /></a>
				<?php
				break;
			
			case Archive::VIDEO:
				
				if (self::isReadyToPlay ($id, $obj->_mimetype))
				{
					?>
					<video width="320" height="240" controls="controls" preload="metadata">
						<source src="titan.php?target=tScript&type=File&file=play&id=<?= $id ?><?= $hashQueryString ?>" />
						<a href="titan.php?target=tScript&type=File&file=play&id=<?= $id ?><?= $hashQueryString ?>" target="_blank" title="<?= __ ('Play') ?>">
							<img src="titan.php?target=tResource&type=Note&file=play.png" border="0" alt="<?= __ ('Play') ?>" />
						</a>
					</video>
					<?php
				}
				else
				{
					?>
					<div style="width: 343px; height: 106px;">
						<div style="position: absolute; width: 100px; height: 100px; top: 3px; left: 3px;">
							<a href="titan.php?target=tScript&type=File&file=open&id=<?= $id ?><?= $hashQueryString ?>" target="_blank" title="<?= $alt ?>"><img src="titan.php?target=tScript&type=File&file=thumbnail&fileId=<?= $id ?>&width=100&height=100<?= $hashQueryString ?>" border="0" alt="<?= $alt ?>" /></a>
						</div>
						<div style="position: relative; width: 220px; top: 10px; left: 110px; overflow: hidden; background-color: #FFF; text-align: justify;">
							<b style="color: #900;"><?= __ ('This video is not supported by native player of your browser or still is being encoded to be displayed! Until then, you can download it directly to your computer to watch in player of your choice.') ?></b>
						</div>
					</div>
					<?php
				}
				break;
			
			case Archive::AUDIO:
				
				if (self::isReadyToPlay ($id, $obj->_mimetype))
				{
					?>
					<audio controls="controls" preload="metadata">
						<source src="titan.php?target=tScript&type=File&file=play&id=<?= $id ?><?= $hashQueryString ?>" />
						<a href="titan.php?target=tScript&type=File&file=open&id=<?= $id ?><?= $hashQueryString ?>" target="_blank" title="<?= __ ('Play') ?>">
							<img src="titan.php?target=tResource&type=Note&file=play.png" border="0" alt="<?= __ ('Play') ?>" />
						</a>
					</audio>
					<?php
				}
				else
				{
					?>
					<div style="width: 343px; height: 106px;">
						<div style="position: absolute; width: 100px; height: 100px; top: 3px; left: 3px;">
							<a href="titan.php?target=tScript&type=File&file=open&id=<?= $id ?><?= $hashQueryString ?>" target="_blank" title="<?= $alt ?>"><img src="titan.php?target=tScript&type=File&file=thumbnail&fileId=<?= $id ?>&width=100&height=100<?= $hashQueryString ?>" border="0" alt="<?= $alt ?>" /></a>
						</div>
						<div style="position: relative; width: 220px; top: 10px; left: 110px; overflow: hidden; background-color: #FFF; text-align: justify;">
							<b style="color: #900;"><?= __ ('This audio is not supported by native player of your browser or still is being encoded to be displayed! Until then, you can download it directly to your computer to listen in player of your choice.') ?></b>
						</div>
					</div>
					<?php
				}
				break;
			
			case Archive::DOWNLOAD:
			case Archive::OPEN:
			default:
				?>
				<div style="width: 343px; height: 106px;">
					<div style="position: absolute; width: 100px; height: 100px; top: 3px; left: 3px;">
						<a href="titan.php?target=tScript&type=File&file=open&id=<?= $id ?><?= $hashQueryString ?>" target="_blank"><img src="titan.php?target=tScript&type=File&file=thumbnail&fileId=<?= $id ?>&width=100&height=100<?= $hashQueryString ?>" border="0" /></a>
					</div>
					<div style="position: relative; width: 220px; top: 10px; left: 110px; overflow: hidden; background-color: #FFF; text-align: left;">
						<b><?= $obj->_name ?></b> <br />
						<?= self::formatFileSizeForHuman ($obj->_size) ?> <br />
						<?= $obj->_mimetype ?> <br /><br />
						<?= $obj->user ?> <br />
						<?= $obj->email ?> <br />
						<?= strftime ('%x %X', $obj->taken) ?>
					</div>
				</div>
				<?php
		}
		
		return str_replace ("\t", '', ob_get_clean ());
	}
	
	public static function getPlayableFile ($id, $mimetype)
	{
		$source = self::getFilePath ($id);
		
		if (!file_exists ($source))
			$source = self::getLegacyFilePath ($id);
		
		$cache = Instance::singleton ()->getCachePath ();
		
		if (!file_exists ($cache . self::ENCODE_FOLDER) && !@mkdir ($cache . self::ENCODE_FOLDER, 0777))
			throw new Exception ('Unable create cache directory!');
		
		if (!file_exists ($cache . self::ENCODE_FOLDER . DIRECTORY_SEPARATOR .'.htaccess') && !file_put_contents ($cache . self::ENCODE_FOLDER . DIRECTORY_SEPARATOR .'.htaccess', 'deny from all'))
			throw new Exception ('Impossible to enhance security for folder ['. $cache . self::CACHE_FOLDER .'].');
		
		$playable = $cache . self::ENCODE_FOLDER . DIRECTORY_SEPARATOR . 'encoded_' . str_pad ($id, 19, '0', STR_PAD_LEFT);
		
		return EncodeMedia::getHtml5PlayableFile ($source, $mimetype, $playable);
	}
	
	public static function isReadyToPlay ($id, $mimetype)
	{
		if (!in_array (Archive::singleton ()->getAssume ($mimetype), array (Archive::VIDEO, Archive::AUDIO)))
			return FALSE;
		
		$convertible = EncodeMedia::getEncodableTypes ();
		
		if (!array_key_exists ($mimetype, $convertible))
			return TRUE;
		
		$cache = Instance::singleton ()->getCachePath ();
		
		$encoded = $cache . self::ENCODE_FOLDER . DIRECTORY_SEPARATOR .'encoded_' . str_pad ($id, 19, '0', STR_PAD_LEFT) .'.'. $convertible [$mimetype];
		
		$control = $cache . self::ENCODE_FOLDER . DIRECTORY_SEPARATOR .'encoded_' . str_pad ($id, 19, '0', STR_PAD_LEFT) .'.encoding';
		
		if (!file_exists ($encoded) || (!(int) filesize ($encoded) && (!file_exists ($control) || filemtime ($control) < strtotime ('-1 day'))))
		{
			self::assyncEncodeFile ($id);
			
			return FALSE;
		}
		
		if (file_exists ($control))
			return FALSE;
		
		return TRUE;
	}
	
	public static function assyncEncodeFile ($id)
	{
		if (!function_exists ('curl_version'))
			throw new Exception ('The PHP library cURL is not enable!');

		$ch = curl_init ();
	
		curl_setopt ($ch, CURLOPT_URL, Instance::singleton ()->getUrl () .'titan.php?target=tScript&type=File&file=encode&fileId='. $id);
		curl_setopt ($ch, CURLOPT_FRESH_CONNECT, TRUE);
		curl_setopt ($ch, CURLOPT_TIMEOUT_MS, 1);
		 
		curl_exec ($ch);
		
		curl_close ($ch);
	}
	
	public static function getRandomHash ()
	{
		$hash = '';
	
		while (strlen ($hash) < 32)
			$hash .= substr ('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxwz', mt_rand (0, 61), 1);
	
		return $hash;
	}
}