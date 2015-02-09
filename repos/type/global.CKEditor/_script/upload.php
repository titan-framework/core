<?
set_time_limit (0);

if (!User::singleton ()->isLogged ())
	throw new Exception (__ ('Attention! Probably attack detected. Access Denied!'));

if (!isset ($_GET['field']) || trim ($_GET['field']) == '' || !isset ($_GET['media']) || !in_array ($_GET['media'], array ('image', 'video', 'audio', 'media', 'all')))
	throw new Exception (__ ('There was lost of variables!'));

$field = $_GET['field'];

$media = $_GET['media'];
?>
<html>
	<head>
		<?
		if (isset ($_FILES['file']) && (int) $_FILES['file']['size'])
		{
			$file = $_FILES['file'];
			
			$fileTemp = $file ['tmp_name'];
			$fileSize = $file ['size'];
			$fileType = $file ['type'];
			$fileName = fileName ($file ['name']);
			
			try
			{
				$db = Database::singleton ();

				$db->beginTransaction ();
				
				$archive = Archive::singleton ();

				if ($fileType == 'application/save' && !($fileType = $archive->getMimeByExtension (array_pop (explode ('.', $file ['name'])))))
					throw new Exception (__ ('This file type ([1]) is not supported!', $obj->_mimetype));
				
				if ($fileType == 'video/3gpp' && !Archive::is3GPPVideo ($fileTemp))
					$fileType = 'audio/3gpp';
				
				if (!$archive->isAcceptable ($fileType))
					throw new Exception (__ ('This type of file is not accepted by the system ([1])!', $fileType));
				
				if ($media != 'all')
				{
					switch ($media)
					{
						case 'image':
							$filter = $archive->getMimesByType (Archive::IMAGE);
							break;
						
						case 'video':
							$filter = $archive->getMimesByType (Archive::VIDEO);
							break;
						
						case 'audio':
							$filter = $archive->getMimesByType (Archive::AUDIO);
							break;
						
						case 'media':
							$filter = array_merge ($archive->getMimesByType (Archive::VIDEO), $archive->getMimesByType (Archive::AUDIO));
							break;
						
						default:
							throw new Exception (__ ('There was lost of variables!'));
					}
					
					if (!in_array ($fileType, $filter))
					{
						$types = array ();
						
						foreach ($filter as $trash => $mime)
						{
							$aux = strtoupper (trim ($archive->getExtensionByMime ($mime)));
							
							if (empty ($aux) || in_array ($aux, $types))
								continue;
							
							$types [] = $aux;
						}
						
						throw new Exception (__ ('This type of file ([1]) is not accept at this field! Files accepts are: [2].', $fileType, implode (', ', $types)));
					}
				}

				$id = Database::nextId ('_cloud', '_id');
				
				$sth = $db->prepare ("INSERT INTO _cloud (_id, _name, _mimetype, _size, _user, _ready, _devise, _change, _author)
									  VALUES (:id, :name, :type, :size, :user, B'1', now(), now(), :user)");
				
				$sth->bindParam (':id', $id, PDO::PARAM_INT);
				$sth->bindParam (':name', $fileName, PDO::PARAM_STR);
				$sth->bindParam (':type', $fileType, PDO::PARAM_STR);
				$sth->bindParam (':size', $fileSize, PDO::PARAM_INT);
				$sth->bindParam (':user', User::singleton ()->getId (), PDO::PARAM_INT);
				
				$sth->execute ();
				
				$path = CloudFile::getFilePath ($id);
				
				if (move_uploaded_file ($fileTemp, $path))
				{
					try
					{
						CloudFile::getPlayableFile ($id, $fileType);
					}
					catch (Exception $e)
					{
						toLog ($e->getMessage ());
					}
					?>
					<script language="javascript" type="text/javascript">
						parent.global.CKEditor.imageUploadSuccess ('<?= $field ?>', '<?= $media ?>', <?= $id ?>);
					</script>
					<?
				}
				else
					throw new Exception (__ ('Unable copy file to directory [[1]]!',  $archive->getDataPath ()));

				$db->commit ();
			}
			catch (PDOException $e)
			{
				$db->rollBack ();

				?>
				<script language="javascript" type="text/javascript">
					parent.global.CKEditor.imageUploadError ('<?= $field ?>', '<?= $media ?>', '<?= $e->getMessage () ?>');
				</script>
				<?
			}
			catch (Exception $e)
			{
				$db->rollBack ();

				?>
				<script language="javascript" type="text/javascript">
					parent.global.CKEditor.imageUploadError ('<?= $field ?>', '<?= $media ?>', '<?= $e->getMessage () ?>');
				</script>
				<?
			}
		}
		?>
		<link rel="stylesheet" href="titan.php?target=packerCss&amp;contexts=main" type="text/css" />
		<!--[if IE]><link rel="stylesheet" type="text/css" href="titan.php?target=packerCss&amp;contexts=ie" /><![endif]-->
		<script language="javascript" type="text/javascript" src="titan.php?target=loadFile&file=js/prototype.js"></script>
		<script language="javascript">
		function clear ()
		{
			parent.global.CKEditor.imageUploadClear ('<?= $field ?>', '<?= $media ?>');
		}
		
		function upload ()
		{
			$('_CLOUD_FORM_').style.display = 'none';
			
			$('_CLOUD_PROGRESS_').style.display = '';
			
			$('_CLOUD_FORM_').submit ();
		}
		</script>
	</head>
	<body onUnload="JavaScript: clear ();" style="background: none #EEE; padding: 0px; height: 47px; overflow: hidden; vertical-align: middle;">
		<div id="_CLOUD_PROGRESS_" style="display: none; margin: 0 auto; text-align: center; color: #656565;">
			<b><?= __ ('Wait! Sending...') ?></b> <br />
			<img src="titan.php?target=loadFile&file=interface/image/loader.gif" border="0" />
		</div>
		<form action="<?= $_SERVER['PHP_SELF'] .'?'. $_SERVER['QUERY_STRING'] ?>" id="_CLOUD_FORM_" method="POST" enctype="multipart/form-data" style="display: block;">
			<input type="button" class="button" value="<?= __ ('Send File') ?>" onClick="JavaScript: upload ();" style="float: right;" />
			<input type="file" name="file" id="_CLOUD_FILE_" /><br />
			<div style="font-size: 10px; font-family: Verdana, Geneva, sans-serif; margin-top: 2px;"><?= __ ('Max Size:') .' <b style="color: #900">'. Archive::getServerUploadLimit () .' MB</b>' ?></div>
		</form>
	</body>
</html>