<?php
class CKEditor extends Fck
{
	const ENCODE_FOLDER = 'ckeditor';
	
	public function __construct ($table, $field)
	{
		if (!Database::tableExists ('_ckeditor'))
			throw new Exception ('The mandatory table [_ckeditor] do not exists! Its necessary to use type CKEditor.');
		
		parent::__construct ($table, $field);
	}
	
	public static function getFilePath ($id)
	{
		return Archive::singleton ()->getDataPath () . 'ckeditor_' . str_pad ($id, 19, '0', STR_PAD_LEFT);
	}
	
	public static function resize ($id, $type, $width = 0, $height = 0, $force = FALSE, $bw = FALSE, $crop = FALSE, $webp = FALSE, $jp2 = FALSE)
	{
		$source = self::getFilePath ($id);
		
		$cache = Instance::singleton ()->getCachePath ();
		
		if (!file_exists ($cache . self::ENCODE_FOLDER) && !@mkdir ($cache . self::ENCODE_FOLDER, 0777))
			throw new Exception ('Unable create cache directory!');
		
		if (!file_exists ($cache . self::ENCODE_FOLDER . DIRECTORY_SEPARATOR .'.htaccess') && !file_put_contents ($cache . self::ENCODE_FOLDER . DIRECTORY_SEPARATOR .'.htaccess', 'deny from all'))
			throw new Exception ('Impossible to enhance security for folder ['. $cache . self::CACHE_FOLDER .'].');
		
		$destination = $cache . self::ENCODE_FOLDER . DIRECTORY_SEPARATOR .'resized_' . str_pad ($id, 19, '0', STR_PAD_LEFT) .'_'. $width .'x'. $height .'_'. ($force ? '1' : '0') .'_'. ($bw ? '1' : '0');
		
		return EncodeMedia::resizeImage ($source, $type, $destination, $width, $height, $force, $bw, $crop, $webp, $jp2);
	}
	
	public static function synopsis ($id, $hash)
	{
		$path = self::getFilePath ($id);
		
		if (!file_exists ($path) || !is_readable ($path) || !(int) sizeof ($path))
			throw new Exception (__ ('Invalid file!'));
		
		try
		{
			$db = Database::singleton ();
			
			$sth = $db->prepare ("SELECT * FROM _ckeditor WHERE _id = :id AND _hash = :hash");
			
			$sth->bindParam (':id', $id, PDO::PARAM_INT);
			$sth->bindParam (':hash', $hash, PDO::PARAM_STR, 32);
			
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
			throw new Exception (__ ('This type of file is not accepted by the system ([1])!', $obj->mime));
		
		ob_start ();
		
		switch ($archive->getAssume ($obj->_mimetype))
		{
			case Archive::IMAGE:
				?>
				<img src="titan.php?target=tScript&type=CKEditor&file=open&id=<?= $id ?>&hash=<?= $hash ?>" alt="<?= $obj->_name ?>" border="0" />
				<?php
				break;
			
			case Archive::VIDEO:
				
				if (self::isReadyToPlay ($id, $obj->_mimetype))
				{
					?>
					<video width="320" height="240" controls="controls" preload="metadata">
						<source src="titan.php?target=tScript&type=CKEditor&file=play&id=<?= $id ?>&hash=<?= $hash ?>" />
						<a href="titan.php?target=tScript&type=CKEditor&file=open&id=<?= $id ?>&hash=<?= $hash ?>" target="_blank" title="<?= __ ('Play') ?>">
							<?= __ ('This video is not supported by native player of your browser! Click here to download it directly to your computer to watch in player of your choice.') ?>
						</a>
					</video>
					<?php
				}
				else
				{
					?>
					<a href="titan.php?target=tScript&type=CKEditor&file=open&id=<?= $id ?>&hash=<?= $hash ?>" target="_blank" title="<?= __ ('Play') ?>">
						<?= __ ('This video is not supported by native player of your browser and cannot be converted! Click here to download it directly to your computer to watch in player of your choice.') ?>
					</a>
					<?php
				}
				break;
			
			case Archive::AUDIO:
				
				if (self::isReadyToPlay ($id, $obj->_mimetype))
				{
					?>
					<audio controls="controls" preload="metadata">
						<source src="titan.php?target=tScript&type=CKEditor&file=play&id=<?= $id ?>&hash=<?= $hash ?>" />
						<a href="titan.php?target=tScript&type=CKEditor&file=open&id=<?= $id ?>&hash=<?= $hash ?>" target="_blank" title="<?= __ ('Play') ?>">
							<?= __ ('This audio is not supported by native player of your browser! Click here to download it directly to your computer to listen in player of your choice.') ?>
						</a>
					</audio>
					<?php
				}
				else
				{
					?>
					<a href="titan.php?target=tScript&type=CKEditor&file=open&id=<?= $id ?>&hash=<?= $hash ?>" target="_blank" title="<?= __ ('Play') ?>">
						<?= __ ('This audio is not supported by native player of your browser and cannot be converted! Click here to download it directly to your computer to listen in player of your choice.') ?>
					</a>
					<?php
				}
				break;
			
			case Archive::DOWNLOAD:
			case Archive::OPEN:
			default:
				?>
				<a href="titan.php?target=tScript&type=CKEditor&file=open&id=<?= $id ?>&hash=<?= $hash ?>" target="_blank"><img src="titan.php?target=loadFile&file=interface/file/<?= $archive->getIcon ($obj->_mimetype) ?>.gif" border="0" /></a>
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
	
	public static function getRandomHash ()
	{
		$hash = '';
	
		while (strlen ($hash) < 32)
			$hash .= substr ('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxwz', mt_rand (0, 61), 1);
	
		return $hash;
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
			return FALSE;
		
		if (file_exists ($control))
			return FALSE;
		
		return TRUE;
	}
}
?>