<?
$value = $field->getValue ();
				
if(!$value || !is_array ($value))
	$value = array ('', '', 0);

ob_start ();
?>
<table class="coordinate">
	<tr>
		<td class="label">Longitude:</td>
		<td><input type="text" class="field" style="width: 150px;" name="<?= $fieldName ?>[]" id="<?= $fieldId ?>Longitude" value="<?= htmlspecialchars ($value [0]) ?>" /></td>
	</tr>
	<tr>
		<td class="label">Latitude:</td>
		<td><input type="text" class="field" style="width: 150px;" name="<?= $fieldName ?>[]" id="<?= $fieldId ?>Latitude" value="<?= htmlspecialchars ($value [1]) ?>" /></td>
	</tr>
	<tr>
		<td class="label">Altitude:</td>
		<td><input type="text" class="field" style="width: 150px;" name="<?= $fieldName ?>[]" id="<?= $fieldId ?>Altitude" value="<?= $value [2] ?>" onkeypress="JavaScript: return glogal.Integer.format (this, event);" onkeyup="JavaScript: glogal.Integer.format (this,false);" /></td>
	</tr>
</table>
<?
$aux = ob_get_contents ();

ob_end_clean();

return $aux;
?>