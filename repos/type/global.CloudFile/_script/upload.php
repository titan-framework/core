<?
if (!User::singleton ()->isLogged ())
	throw new Exception (__ ('Attention! Probably attack detected. Access Denied!'));

if (!isset ($_GET['fieldId']))
	throw new Exception (__ ('There was lost of variables!'));

$idFieldFile = $_GET['fieldId'];

ob_start ();
?>
<html>
	<head>
		<?
		$archive = Archive::singleton ();

		if (isset ($_FILES['file']) && (int) $_FILES['file']['size'] && isset ($_POST['upload_filter']))
		{
			$file = $_FILES['file'];

			if (isset ($_POST['name']))
				$auxName = $_POST['name'];
			else
				$auxName = '';

			if (isset ($_POST['description']))
				$fileDesc = $_POST['description'];
			else
				$fileDesc = '';

			$fileTemp = $file ['tmp_name'];
			$fileSize = $file ['size'];
			$fileType = $file ['type'];

			if (trim ($auxName) == '')
				$fileName = $file ['name'];
			else
				$fileName = $auxName . substr ($file ['name'], strrpos ($file ['name'], '.'));

			$fileName = fileName ($fileName);

			try
			{
				$db = Database::singleton ();

				$db->beginTransaction ();

				if ($fileType == 'application/save' && !($fileType = $archive->getMimeByExtension (array_pop (explode ('.', $file ['name'])))))
					throw new Exception (__ ('This type of file is not accepted by the system !'));

				if (!$archive->isAcceptable ($fileType))
					throw new Exception (__ ('This type of file is not accepted by the system ( [1] ) !', $fileType));

				$uploadFilter = array ();

				if (trim ($_POST['upload_filter']) != '')
					$uploadFilter = explode (',', $_POST['upload_filter']);

				if (sizeof ($uploadFilter) && !in_array ($fileType, $uploadFilter))
					throw new Exception (__ ('This type of file is not accept at this field! Files accepts are : [1]', implode (', ', $uploadFilter)));

				$fileId = Database::nextId ('_cloud', '_id');

				$sth = $db->prepare ("INSERT INTO _cloud (_id, _name, _mimetype, _size, _user, _ready, _creation_date, _last_change)
									  VALUES (:id, :name, :type, :size, :user, B'1', now(), now())");
				
				$sth->bindParam (':id', $fileId, PDO::PARAM_INT);
				$sth->bindParam (':name', $fileName, PDO::PARAM_STR);
				$sth->bindParam (':type', $fileType, PDO::PARAM_STR);
				$sth->bindParam (':size', $fileSize, PDO::PARAM_INT);
				$sth->bindParam (':user', User::singleton ()->getId (), PDO::PARAM_INT);
				
				$sth->execute ();

				if (move_uploaded_file ($fileTemp, $archive->getDataPath () . 'cloud_'. str_pad ($fileId, 7, '0', STR_PAD_LEFT)))
				{
					Lucene::singleton ()->saveFile ($fileId);
					?>
					<script language="javascript" type="text/javascript">
						parent.global.CloudFile.load (<?= $fileId ?>, '<?= $idFieldFile ?>');
					</script>
					<?
				}
				else
					throw new Exception (__ ('Unable copy file to directory [ [1] ]!',  $archive->getDataPath ()));

				$db->commit ();
			}
			catch (PDOException $e)
			{
				$db->rollBack ();

				$error = $e->getMessage ();
			}
			catch (Exception $e)
			{
				$db->rollBack ();

				$error = $e->getMessage ();
			}
		}
		?>
		<link rel="stylesheet" href="titan.php?target=packerCss&amp;contexts=main" type="text/css" />
		<!--[if IE]><link rel="stylesheet" type="text/css" href="titan.php?target=packerCss&amp;contexts=ie" /><![endif]-->
		<style type="text/css">
		body
		{
			background: none #FFF;
		}
		#idMessage .cError a.cReport
		{
			display: none;
		}
		</style>
		<script language="javascript">
		function upload ()
		{
			document.getElementById ('form').style.display = 'none';
			document.getElementById ('uploading').style.display = '';

			document.upload_file.submit ();
		}
		function loadFilter ()
		{
			document.getElementById ('upload_filter').value = parent.global.CloudFile.getFilter ('<?= $idFieldFile ?>');
		}
		</script>
	</head>
	<body onLoad="JavaScript: loadFilter ();">
		<div id="uploading" style="position: absolute; display: none; width: 340; height: 106; top: 0; left: 0; background-color: #FFFFFF;">
			<div style="position: absolute; width: 96; height: 96; top: 3; left: 3; border-color: #ABCDEF; border-width: 2; border-style: solid;">
				<div style="position: absolute; width: 16; height: 16; top: 42; left: 42;">
					<img src="titan.php?target=loadFile&amp;file=interface/icon/upload.gif" border="0">
				</div>
			</div>
			<div style="position: absolute; width: 190; top: 10; left: 110; color: #000099; font-weight: bold;"><?=__ ('Uploading file.<br />Wait!') ?></div>
		</div>
		<div id="form" style="position: absolute; width: 100%; height: 106px; top: 0; left: 0; overflow: auto; *overflow: hidden; *height: 256px;">
			<?= isset ($error) ? '<div style="color: #990000; border: #900 1px solid; margin: 3px; padding: 3px;">'. $error .'</div>' : '' ?>
			<form action="<?= $_SERVER['PHP_SELF'] .'?'. $_SERVER['QUERY_STRING'] ?>" id="upload_file" name="upload_file" method="POST" enctype="multipart/form-data">
				<input type="hidden" id="upload_filter" name="upload_filter" value="" />
				<p class="pFile" style="margin-top: 10px;"><label class="labelFile" for="up_name"><?= __ ('Name') ?>:</label> <input type="text" class="fieldFile" name="name" id="up_name" /></p>
				<p class="pFile"><label class="labelFile" for="up_file"><?= __ ('File') ?>:</label> <input type="file" class="fieldFile" name="file" id="up_file" /></p>
				<p class="pFile"><label class="infoFile"><?= __ ('Maximum file size') ?>: <b style="color: #900;"><?= $archive->getUploadLimit () ?>MB</b></label></p>
				<p class="pFile"><input type="button" class="buttonFile" value="<?= __ ('Send File') ?>" onClick="JavaScript: upload ();" /></p>
			</form>
		</div>
	</body>
</html>
<?
echo ob_get_clean ();
?>