<?
if (!$field->getValue ())
	return '-';

$info = $field->getInfo ();

ob_start ();
?>
<a href="titan.php?target=tScript&type=File&file=open&id=<?= $field->getValue () ?>&hash=<?= $info ['_HASH_'] ?>" target="_blank">
<img src="titan.php?target=loadFile&file=interface/file/icon/<?= Archive::singleton ()->getIcon ($info ['_MIME_']) ?>.png" border="0" align="left">
&nbsp;<?= $info ['_NAME_'] ?> (<?= File::formatFileSizeForHuman ($info ['_SIZE_']) ?>)
</a>
<?
return str_replace ("\n", '', ob_get_clean ());
?>