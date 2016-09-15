<?php
$value = $field->getValue ();
				
if(!$value || !is_array ($value))
	$value = array (date('H'), date ('i'), date ('s'));
	
if($value [0] < 0)
	$value [0] = date ('H');
	
if($value [1] < 0)
	$value [1] = date ('i');
	
if($value [2] < 0)
	$value [2] = date ('s');

ob_start ();
?>
<input type="hidden" name="<?= $fieldId ?>" id="<?= $fieldId ?>" value="<?php echo $field ?>" />

<select class="field" style="width: 40px; <?= $field->getStyle () ?>" name="<?= $fieldName ?>[]" id="hour_<?= $fieldId ?>" onChange="JavaScript: type.global.Time.alter ('<?= $fieldId ?>');">
	<option value="-1">--</option>
	<?php
	for($i = 0 ; $i <= 23 ; $i++)
		echo '<option value="'. $i .'"'. ($i == $value [0] ? ' selected' : '') .'>'. ($i < 10 ? '0' : '') . $i .'</option>';
	?>
</select>

<select class="field" style="width: 40px; margin-left: 3px; <?= $field->getStyle () ?>" name="<?= $fieldName ?>[]" id="minute_<?= $fieldId ?>" onChange="JavaScript: type.global.Time.alter ('<?= $fieldId ?>');">
	<option value="-1">--</option>
	<?php
	for($i = 0 ; $i <= 59 ; $i++)
		echo '<option value="'. $i .'"'. ($i == $value [1] ? ' selected' : '') .'>'. ($i < 10 ? '0' : '') . $i .'</option>';
	?>
</select>

<select class="field" style="width: 40px; margin-left: 3px; <?= $field->getStyle () ?>" name="<?= $fieldName ?>[]" id="second_<?= $fieldId ?>" onChange="JavaScript: type.global.Time.alter ('<?= $fieldId ?>');">
	<option value="-1">--</option>
	<?php
	for($i = 0 ; $i <= 59 ; $i++)
		echo '<option value="'. $i .'"'. ($i == $value [2] ? ' selected' : '') .'>'. ($i < 10 ? '0' : '') . $i .'</option>';
	?>
</select>
<?php
$aux = ob_get_contents ();

ob_end_clean();

return $aux;
?>