<?
ob_start ();

if ($field->getValue ())
{
	try
	{
		$out = CloudFile::synopsis ($field->getValue (), $field->getFilter (), $field->getResolution ());
		?>
		<div style="position: relative; border: 1px #CCC solid; background-color: #FFF; padding: 2px; float: left;">
			<?= $out ?>
		</div>
		<?
	}
	catch (Exception $e)
	{
		echo '<b style="color: #900;">'. $e->getMessage () .'</b>';
	}
}
else
	echo '<img src="titan.php?target=tResource&type=CloudFile&file=no-file.png" border="0" />';

return ob_get_clean ();
?>