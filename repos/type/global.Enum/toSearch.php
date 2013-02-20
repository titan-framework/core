<? ob_start () ?>

<select class="field" style="'. $field->getStyle () .'" name="<?= $fieldName ?>" id="<?= $fieldId ?>">
	<option value="">Selecione</option>
	<?
	foreach ($field->getMapping () as $column => $value)
		echo '<option value="'. $column .'"'. ($column == $field->getValue () ? ' selected' : '') .'>'. $value .'</option>';
	?>
</select>

<?
$aux = ob_get_contents ();

ob_end_clean();

return $aux;
?>