<?
ob_start ();

$array = array ();
?>
<table>
	<tr><td><input type="radio" name="<?= $fieldName ?>" id="<?= $fieldId ?>_disabled" value="" <?= $field->isEmpty () ? 'checked' : '' ?> /></td><td><?= __ ('Disable') ?></td></tr>
	<?
	foreach ($field->getMapping () as $value => $label)
		echo '<tr><td><input type="radio" name="'. $fieldName .'" value="'. $value .'"'. ($value == $field->getValue () ? ' checked="checked"' : '') .' /></td><td>'. $label .'</td></tr>';
	?>
</table>
<?
return ob_get_clean ();
?>