<?
if (!$field->getValue ())
	return '-';

$info = $field->getInfo ();

ob_start ();
?><a href="titan.php?target=openFile&amp;fileId=<?= $field->getValue () ?>" target="_blank"><img src="titan.php?target=loadFile&amp;file=interface/file/icon/<?= Archive::singleton ()->getIcon ($info ['_MIME_']) ?>.png" border="0" align="left"></a>&nbsp;<?= $info ['_NAME_'] ?> (<?= number_format ($info ['_SIZE_'] / 1024, 0, '', '.') ?> KB)<?
return ob_get_clean ();
?>