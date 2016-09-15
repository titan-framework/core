<?php
if (!User::singleton ()->isLogged ())
	throw new Exception (__ ('Attention! Probably attack detected. Access Denied!'));

if (!isset ($_GET['field']))
	throw new Exception (__ ('There was lost of variables!'));

$field = $_GET['field'];
?>
<html>
	<head>
		<link rel="stylesheet" href="titan.php?target=packerCss&contexts=main" type="text/css" />
		<!--[if IE]><link rel="stylesheet" type="text/css" href="titan.php?target=packerCss&contexts=ie" /><![endif]-->
		<script language="javascript" type="text/javascript" src="titan.php?target=loadFile&file=js/prototype.js"></script>
		<script language="javascript">
		function choose (id)
		{
			parent.global.File.load (id, '<?= $field ?>');
		}
		function loadFilter ()
		{
			$('_TITAN_GLOBAL_FILE_ARCHIVE_FILTER_').value = parent.global.File.getFilter ('<?= $field ?>');
		}
		</script>
	</head>
	<body onLoad="JavaScript: loadFilter ();" style="background: none #EEE; padding: 0px; height: 100%; width: 100%; overflow: auto;">
		<form action="<?= $_SERVER['PHP_SELF'] .'?'. $_SERVER['QUERY_STRING'] ?>" id="_TITAN_GLOBAL_FILE_ARCHIVE_FORM_" method="POST">
			<input type="hidden" id="_TITAN_GLOBAL_FILE_ARCHIVE_FILTER_" name="filter" value="" />
			<input type="submit" class="button" value="<?= __ ('Search') ?>" style="float: right;" />
			<input type="text" class="field" name="term" style="float: left;" />
		</form>
	</body>
</html>