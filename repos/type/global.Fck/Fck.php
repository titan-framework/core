<?php

class Fck extends String
{
	protected $public = TRUE;
	
	protected $ownerOnly = FALSE;
	
	public function __construct ($table, $field)
	{
		parent::__construct ($table, $field);
		
		if (array_key_exists ('value-default', $field))
			$this->setValue ($field ['value-default']);
		
		if (array_key_exists ('value', $field))
			$this->setValue ($field ['value']);
		
		if (array_key_exists ('owner-only', $field))
			$this->setOwnerOnly (strtoupper (trim ($field ['owner-only'])) == 'TRUE' ? TRUE : FALSE);
		
		if (array_key_exists ('public', $field))
			$this->setPublic (strtoupper (trim ($field ['public'])) == 'TRUE' ? TRUE : FALSE);
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
	
	public static function getCKEditorPath ()
	{
		return Instance::singleton ()->getCorePath () .'repos/type/global.'. __CLASS__ .'/CKEditor/';
	}
	
	public static function getLanguage ()
	{
		$conversion = array ('pt_BR' => 'pt-br', 'en_US' => 'en', 'es_ES' => 'es');
		
		$language = Localization::singleton ()->getLanguage ();
		
		if (array_key_exists ($language, $conversion))
			return $conversion [$language];
		
		return 'en';
	}
	
	public static function synopsis ($id)
	{
		$path = File::getFilePath ($id);
		
		if (!file_exists ($path))
			$path = File::getLegacyFilePath ($id);
		
		if (!file_exists ($path))
			throw new Exception (__ ('Invalid file!'));
		
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
			throw new Exception (__ ('This type of file is not accepted by the system ([1])!', $obj->mime));
		
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
				<img src="titan.php?target=tScript&type=File&file=thumbnail&fileId=<?= $id ?>&height=<?= $dimension ?><?= $hashQueryString ?>" alt="<?= $alt ?>" border="0" />
				<?
				break;
			
			case Archive::VIDEO:
				
				if (File::isReadyToPlay ($id, $obj->_mimetype))
				{
					?>
					<video width="320" height="240" controls="controls" preload="metadata">
						<source src="titan.php?target=tScript&type=File&file=play&id=<?= $id ?><?= $hashQueryString ?>" />
						<a href="titan.php?target=tScript&type=File&file=open&id=<?= $id ?><?= $hashQueryString ?>" target="_blank" title="<?= __ ('Play') ?>">
							<?= __ ('This video is not supported by native player of your browser! Click here to download it directly to your computer to watch in player of your choice.') ?>
						</a>
					</video>
					<?
				}
				else
				{
					?>
					<a href="titan.php?target=tScript&type=File&file=open&id=<?= $id ?><?= $hashQueryString ?>" target="_blank" title="<?= __ ('Play') ?>">
						<?= __ ('This video is not supported by native player of your browser and cannot be converted! Click here to download it directly to your computer to watch in player of your choice.') ?>
					</a>
					<?
				}
				break;
			
			case Archive::AUDIO:
				
				if (File::isReadyToPlay ($id, $obj->_mimetype))
				{
					?>
					<audio controls="controls" preload="metadata">
						<source src="titan.php?target=tScript&type=File&file=play&id=<?= $id ?><?= $hashQueryString ?>" />
						<a href="titan.php?target=tScript&type=File&file=open&id=<?= $id ?><?= $hashQueryString ?>" target="_blank" title="<?= __ ('Play') ?>">
							<?= __ ('This audio is not supported by native player of your browser! Click here to download it directly to your computer to listen in player of your choice.') ?>
						</a>
					</audio>
					<?
				}
				else
				{
					?>
					<a href="titan.php?target=tScript&type=File&file=open&id=<?= $id ?><?= $hashQueryString ?>" target="_blank" title="<?= __ ('Play') ?>">
						<?= __ ('This audio is not supported by native player of your browser and cannot be converted! Click here to download it directly to your computer to listen in player of your choice.') ?>
					</a>
					<?
				}
				break;
			
			case Archive::DOWNLOAD:
			case Archive::OPEN:
			default:
				?>
				<a href="titan.php?target=tScript&type=File&file=open&id=<?= $id ?><?= $hashQueryString ?>" target="_blank"><img src="titan.php?target=loadFile&file=interface/file/<?= $archive->getIcon ($obj->_mimetype) ?>.gif" border="0" /></a>
				<?
		}
		
		return str_replace ("\t", '', ob_get_clean ());
	}
}