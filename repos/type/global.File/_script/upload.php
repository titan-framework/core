<?
set_time_limit (0);

if (!User::singleton ()->isLogged ())
	throw new Exception (__ ('Attention! Probably attack detected. Access Denied!'));

if (!isset ($_GET['field']) || !isset ($_GET['public']))
	throw new Exception (__ ('There was lost of variables!'));

$field = $_GET['field'];

$public = (bool) $_GET['public'];
?>
<html>
	<head>
		<?
		if (isset ($_FILES['file']) && (int) $_FILES['file']['size'] && isset ($_POST['filter']))
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
				
				$uploadFilter = array ();

				if (trim ($_POST['filter']) != '')
					$uploadFilter = explode (',', $_POST['filter']);

				if (sizeof ($uploadFilter) && !in_array ($fileType, $uploadFilter))
				{
					$types = array ();
					
					foreach ($uploadFilter as $trash => $mime)
					{
						$aux = strtoupper (trim ($archive->getExtensionByMime ($mime)));
						
						if (empty ($aux) || in_array ($aux, $types))
							continue;
						
						$types [] = $aux;
					}
					
					throw new Exception (__ ('This type of file ([1]) is not accept at this field! Files accepts are: [2].', strtoupper ($archive->getExtensionByMime ($fileType)), implode (', ', $types)));
				}
				
				$id = Database::nextId ('_file', '_id');
				
				$array = array (
					array ('_id', $id, PDO::PARAM_INT),
					array ('_name', $fileName, PDO::PARAM_STR),
					array ('_mimetype', $fileType, PDO::PARAM_STR),
					array ('_size', $fileSize, PDO::PARAM_INT),
					array ('_user', User::singleton ()->getId (), PDO::PARAM_INT)
				);
				
				if (!$public)
				{
					$hash = File::getRandomHash ();
					
					$array [] = array ('_public', 0, PDO::PARAM_INT);
					$array [] = array ('_hash', $hash, PDO::PARAM_STR);
				}
				
				$columns = array ();
				$values  = array ();
				
				foreach ($array as $trash => $item)
				{
					$columns [] = $item [0];
					$values []  = ':'. $item [0];
				}
				
				$sth = $db->prepare ("INSERT INTO _file (". implode (", ", $columns) .") VALUES (". implode (", ", $values) .")");
				
				foreach ($array as $trash => $item)
					$sth->bindParam (':'. $item [0], $item [1], $item [2]);
				
				$sth->execute ();
				
				$path = File::getFilePath ($id);
				
				if (move_uploaded_file ($fileTemp, $path))
				{
					try
					{
						File::assyncEncodeFile ($id);
					}
					catch (Exception $e)
					{
						toLog ($e->getMessage ());
					}
					?>
					<script language="javascript" type="text/javascript">
						parent.global.File.load (<?= $id ?>, '<?= $field ?>');
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
					parent.global.File.error ('<?= $e->getMessage () ?>', '<?= $field ?>');
				</script>
				<?
			}
			catch (Exception $e)
			{
				$db->rollBack ();

				?>
				<script language="javascript" type="text/javascript">
					parent.global.File.error ('<?= $e->getMessage () ?>', '<?= $field ?>');
				</script>
				<?
			}
		}
		?>
		<link rel="stylesheet" href="titan.php?target=packerCss&contexts=main" type="text/css" />
		<!--[if IE]><link rel="stylesheet" type="text/css" href="titan.php?target=packerCss&contexts=ie" /><![endif]-->
		<script language="javascript" type="text/javascript" src="titan.php?target=loadFile&file=js/prototype.js"></script>
		<script language="javascript">
		function upload ()
		{
			parent.global.File.clear ('<?= $field ?>');
			
			$('_TITAN_GLOBAL_FILE_FORM_').style.display = 'none';
			
			$('_TITAN_GLOBAL_FILE_PROGRESS_').style.display = '';
			
			$('_TITAN_GLOBAL_FILE_FORM_').submit ();
		}
		function loadFilter ()
		{
			$('_TITAN_GLOBAL_FILE_FILTER_').value = parent.global.File.getFilter ('<?= $field ?>');
		}
		</script>
	</head>
	<body onLoad="JavaScript: loadFilter ();" style="background: none #EEE; padding: 0px; height: 47px; overflow: hidden; vertical-align: middle;">
		<div id="_TITAN_GLOBAL_FILE_PROGRESS_" style="display: none; margin: 0 auto; text-align: center; color: #656565;">
			<b><?= __ ('Wait! Sending...') ?></b> <br />
			<img src="titan.php?target=loadFile&file=interface/image/loader.gif" border="0" />
		</div>
		<form action="<?= $_SERVER['PHP_SELF'] .'?'. $_SERVER['QUERY_STRING'] ?>" id="_TITAN_GLOBAL_FILE_FORM_" method="POST" enctype="multipart/form-data" style="display: block;">
			<input type="hidden" id="_TITAN_GLOBAL_FILE_FILTER_" name="filter" value="" />
			<input type="button" class="button" value="<?= __ ('Send File') ?>" onClick="JavaScript: upload ();" style="float: right;" />
			<input type="file" name="file" id="_TITAN_GLOBAL_FILE_FILE_" /><br />
			<div style="font-size: 10px; font-family: Verdana, Geneva, sans-serif; margin-top: 2px;"><?= __ ('Max Size:') .' <b style="color: #900">'. Archive::getServerUploadLimit () .' MB</b>' ?></div>
		</form>
	</body>
</html>