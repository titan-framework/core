<?
if (!isset ($_GET['itemId']) || !isset ($_GET['table']))
	die (__ ('Error! Data losted.'));

require Instance::singleton ()->getCorePath () .'extra/pear.php';
require Instance::singleton ()->getCorePath () .'extra/zip.php';

$itemId = $_GET['itemId'];
$table  = $_GET['table'];
?>
<html>
	<head>
		<?
		$archive = Archive::singleton ();

		$message = Message::singleton ();

		if (isset ($_FILES['file']) && (int) $_FILES['file']['size'])
		{
			$file = $_FILES['file'];

			$result = array ();

			$str = '';

			$fileTemp = $file ['tmp_name'];
			$fileSize = $file ['size'];
			$fileType = $file ['type'];

			$fileName = fileName ($file ['name']);

			$db = Database::singleton ();

			try
			{
				$db->beginTransaction ();

				if ($fileType == 'application/save' && !($fileType = $archive->getMimeByExtension (array_pop (explode ('.', $file ['name'])))))
					throw new Exception (__ ('This file type ( [1] ) is not accept by the system!', $fileType));

				if (in_array ($fileType, array ('application/x-zip-compressed', 'application/zip')))
					$result = zipFile ($itemId, $table, $fileTemp, $fileSize, $fileType);
				else
				{
					if (!$archive->isAcceptable ($fileType, Archive::IMAGE))
						throw new Exception (__ ('This file type ( [1] ) is not accept by the system!', $fileType));

					$fileId = Database::nextId ('_media', '_id');

					$sth = $db->prepare ("INSERT INTO _media (_id, _name, _mimetype, _size, _user) VALUES ('". $fileId ."', '". $fileName ."', '". $fileType ."', '". $fileSize ."', '". User::singleton ()->getId () ."')");

					$sth->execute ();

					$sth = $db->prepare ("INSERT INTO ". $table ." (_item, _media) VALUES ('". $itemId ."', '". $fileId ."')");

					$sth->execute ();

					if (!move_uploaded_file ($fileTemp, $archive->getDataPath () . 'photo_'. str_pad ($fileId, 7, '0', STR_PAD_LEFT)))
						throw new Exception (__ ('The file can not be copied into folder [ [1] ]!', $archive->getDataPath () ) );

					$result = array ($fileId);
				}

				$db->commit ();
			}
			catch (PDOException $e)
			{
				$db->rollBack ();

				$message->addWarning ($e->getMessage ());
			}
			catch (Exception $e)
			{
				$db->rollBack ();

				$message->addWarning ($e->getMessage ());
			}

			if (sizeof ($result))
			{
				$message->addMessage (__ ('Send photos with success!'));

				$str = "parent.createThumb ('". implode ("'); parent.createThumb ('", $result) ."');";
			}
			else
				$message->addWarning (__ ('No photos could be sent!'));

			$message->save ();
			?>
			<script language="javascript" type="text/javascript">
				<?= $str ?>

				parent.Sortable.create("idGallery",{tag:'div',overlap:'horizontal',constraint: false});

				parent.ajax.showMessages ();

				parent.hideWait ();
			</script>
			<?
		}
		?>
		<link rel="stylesheet" href="titan.php?target=loadFile&amp;file=interface/css/general.css" type="text/css" />
		<style type="text/css">
		body
		{
			background: none #FFFFFF;
		}
		</style>
		<script language="javascript">
		function upload ()
		{
			parent.showWait ();

			parent.showUpload ();

			document.uploadFile.submit ();
		}
		</script>
	</head>
	<body>
		<div id="form">
			<form name="uploadFile" action="<?= $_SERVER['PHP_SELF'] .'?'. $_SERVER['QUERY_STRING'] ?>" method="POST" enctype="multipart/form-data">
				<p class="pFile">
					<label class="labelFile" style="font-weight: bold;" for="up_file"><?=__ ('File')?>:</label>
					<input type="file" class="fieldFile" name="file" id="up_file" style="width: 120px;" />
					<input type="button" class="buttonFile" value="<?=__ ('Send File')?>" onClick="JavaScript: upload ();" />
				</p>
				<p style="font-size: 9px; padding-left: 75px; color: #656565;"><?=__ ('Supported files: JPG, GIF, PNG and ZIP')?></p>
			</form>
		</div>
	</body>
</html>