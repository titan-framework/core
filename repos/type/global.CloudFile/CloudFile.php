<?php

class CloudFile extends File
{
	const ENCODE_FOLDER = 'cloud-file';

	public function __construct ($table, $field)
	{
		if (!Database::tableExists ('_cloud'))
			throw new Exception ('The mandatory table [_cloud] do not exists! Its necessary to use type CloudFile.');

		parent::__construct ($table, $field);
	}

	public function getInfo ()
	{
		if (!$this->getValue ())
			return NULL;

		$sth = Database::singleton ()->prepare ("SELECT _name, _size, _mimetype FROM _cloud WHERE _id = :id AND _deleted = B'0'");

		$sth->bindParam (':id', $this->getValue (), PDO::PARAM_INT);

		$sth->execute ();

		$obj = $sth->fetch (PDO::FETCH_OBJ);

		if (!$obj)
			return NULL;

		return array ('_NAME_' => $obj->_name,
					  '_SIZE_' => $obj->_size,
					  '_MIME_' => $obj->_mimetype);
	}

	public static function getFilePath ($id)
	{
		return Archive::singleton ()->getDataPath () . 'cloud_' . str_pad ($id, 19, '0', STR_PAD_LEFT);
	}

	public static function resize ($id, $type, $width = 0, $height = 0, $force = FALSE, $bw = FALSE, $crop = FALSE)
	{
		$source = self::getFilePath ($id);

		$cache = Instance::singleton ()->getCachePath ();

		if (!file_exists ($cache . self::ENCODE_FOLDER) && !@mkdir ($cache . self::ENCODE_FOLDER, 0777))
			throw new Exception ('Unable create cache directory!');

		if (!file_exists ($cache . self::ENCODE_FOLDER . DIRECTORY_SEPARATOR .'.htaccess') && !file_put_contents ($cache . self::ENCODE_FOLDER . DIRECTORY_SEPARATOR .'.htaccess', 'deny from all'))
			throw new Exception ('Impossible to enhance security for folder ['. $cache . self::ENCODE_FOLDER .'].');

		$destination = $cache . self::ENCODE_FOLDER . DIRECTORY_SEPARATOR .'resized_' . str_pad ($id, 19, '0', STR_PAD_LEFT) .'_'. $width .'x'. $height .'_'. ($force ? '1' : '0') .'_'. ($bw ? '1' : '0');

		return EncodeMedia::resizeImage ($source, $type, $destination, $width, $height, $force, $bw);
	}

	public static function synopsis ($id, $filter = array (), $dimension = 200)
	{
		$path = self::getFilePath ($id);

		if (!file_exists ($path))
			throw new Exception (__ ('The file has not been fully sended to server and cannot be displayed until it is.'));

		try
		{
			$db = Database::singleton ();

			$sth = $db->prepare ("SELECT c._name AS name, c._size AS size, c._mimetype AS mime, u._name AS user, u._email AS email,
								  EXTRACT (EPOCH FROM c._devise) AS taken
								  FROM _cloud c
								  LEFT JOIN _user u ON u._id = c._user
								  WHERE c._id = :id AND c._ready = B'1' AND c._deleted = B'0'");

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

		if (!$archive->isAcceptable ($obj->mime))
			throw new Exception (__ ('This type of file is not accepted by the system ([1])!', $obj->mime));

		if (is_array ($filter) && (int) sizeof ($filter) && !in_array ($obj->mime, $filter))
		{
			$types = array ();

			foreach ($filter as $trash => $mime)
			{
				$aux = trim ($archive->getExtensionByMime ($mime));

				if (empty ($aux))
					continue;

				$types [] = strtoupper ($aux);
			}

			throw new Exception (__ ('This type of file ([1]) is not accept at this field! Files accepts are: [2].', $obj->mime, implode (', ', $types)));
		}

		ob_start ();

		switch ($archive->getAssume ($obj->mime))
		{
			case Archive::IMAGE:
				$alt = $obj->name ." (". File::formatFileSizeForHuman ($obj->size) ." &bull; ". $obj->mime .") \n". __ ('By [1] ([2]) on [3].', $obj->user, $obj->email, strftime ('%x %X', $obj->taken));
				?>
				<a href="titan.php?target=tScript&type=CloudFile&file=open&fileId=<?= $id ?>&auth=1" target="_blank" title="<?= $alt ?>"><img src="titan.php?target=tScript&type=CloudFile&file=thumbnail&fileId=<?= $id ?>&height=<?= $dimension ?>&auth=1" alt="<?= $alt ?>" border="0" /></a>
				<?php
				break;

			case Archive::VIDEO:

				if (self::isReadyToPlay ($id, $obj->mime))
				{
					?>
					<video width="320" height="240" controls="controls" preload="metadata">
						<source src="titan.php?target=tScript&type=CloudFile&file=play&fileId=<?= $id ?>&auth=1" />
						<a href="titan.php?target=tScript&type=CloudFile&file=play&fileId=<?= $id ?>&auth=1" target="_blank" title="<?= __ ('Play') ?>">
							<img src="titan.php?target=tResource&type=Note&file=play.png" border="0" alt="<?= __ ('Play') ?>" />
						</a>
					</video>
					<?php
				}
				else
				{
					$alt = $obj->name ." (". File::formatFileSizeForHuman ($obj->size) ." &bull; ". $obj->mime .") \n". __ ('By [1] ([2]) on [3].', $obj->user, $obj->email, strftime ('%x %X', $obj->taken));
					?>
					<div style="width: 343px; height: 106px;">
						<div style="position: absolute; width: 100px; height: 100px; top: 3px; left: 3px;">
							<a href="titan.php?target=tScript&type=CloudFile&file=open&fileId=<?= $id ?>&auth=1" target="_blank" title="<?= $alt ?>"><img src="titan.php?target=tScript&type=CloudFile&file=thumbnail&fileId=<?= $id ?>&width=100&height=100&auth=1" border="0" alt="<?= $alt ?>" /></a>
						</div>
						<div style="position: relative; width: 220px; top: 10px; left: 110px; overflow: hidden; background-color: #FFF; text-align: justify;">
							<b style="color: #900;"><?= __ ('This video is not supported by native player of your browser or still is being encoded to be displayed! Until then, you can download it directly to your computer to watch in player of your choice.') ?></b>
						</div>
					</div>
					<?php
				}
				break;

			case Archive::AUDIO:

				if (self::isReadyToPlay ($id, $obj->mime))
				{
					?>
					<audio controls="controls" preload="metadata">
						<source src="titan.php?target=tScript&type=CloudFile&file=play&fileId=<?= $id ?>&auth=1" />
						<a href="titan.php?target=tScript&type=CloudFile&file=open&fileId=<?= $id ?>&auth=1" target="_blank" title="<?= __ ('Play') ?>">
							<img src="titan.php?target=tResource&type=Note&file=play.png" border="0" alt="<?= __ ('Play') ?>" />
						</a>
					</audio>
					<?php
				}
				else
				{
					$alt = $obj->name ." (". File::formatFileSizeForHuman ($obj->size) ." &bull; ". $obj->mime .") \n". __ ('By [1] ([2]) on [3].', $obj->user, $obj->email, strftime ('%x %X', $obj->taken));
					?>
					<div style="width: 343px; height: 106px;">
						<div style="position: absolute; width: 100px; height: 100px; top: 3px; left: 3px;">
							<a href="titan.php?target=tScript&type=CloudFile&file=open&fileId=<?= $id ?>&auth=1" target="_blank" title="<?= $alt ?>"><img src="titan.php?target=tScript&type=CloudFile&file=thumbnail&fileId=<?= $id ?>&width=100&height=100&auth=1" border="0" alt="<?= $alt ?>" /></a>
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
						<a href="titan.php?target=tScript&type=CloudFile&file=open&fileId=<?= $id ?>&auth=1" target="_blank"><img src="titan.php?target=tScript&type=CloudFile&file=thumbnail&fileId=<?= $id ?>&width=100&height=100&auth=1" border="0" /></a>
					</div>
					<div style="position: relative; width: 220px; top: 10px; left: 110px; overflow: hidden; background-color: #FFF; text-align: left;">
						<b><?= $obj->name ?></b> <br />
						<?= File::formatFileSizeForHuman ($obj->size) ?> <br />
						<?= $obj->mime ?> <br /><br />
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

		curl_setopt ($ch, CURLOPT_URL, Instance::singleton ()->getUrl () .'titan.php?target=tScript&type=CloudFile&file=encode&fileId='. $id);
		curl_setopt ($ch, CURLOPT_FRESH_CONNECT, TRUE);
		curl_setopt ($ch, CURLOPT_TIMEOUT_MS, 1);

		curl_exec ($ch);

		curl_close ($ch);
	}
}
