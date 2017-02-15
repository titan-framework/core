<?php

$value = $field->getValue ();

$firstYear = $field->getFirstYear ();

$lastYear = $field->getLastYear ();

if (!is_array ($value))
	$value = $field->isRequired () ? array (date ('d'), date ('m'), date ('Y')) : array (0, 0, 0);

if($field->isRequired () && !$value [0])
	$value [0] = date ('d');

if($field->isRequired () && !$value [1])
	$value [1] = date ('m');

if($field->isRequired () && !$value [2])
	$value [2] = date ('Y');

if (!$firstYear)
	$firstYear = date ('Y') - 30;

if (!$lastYear)
	$lastYear = date ('Y') + 30;

ob_start ();
?>
<select class="field" style="width: 45px; <?= $field->getStyle () ?>" name="<?= $fieldName ?>[]" id="<?= $fieldId ?>-dd" onchange="JavaScript: global.Date.validate ('<?= $fieldId ?>', this);">
	<?php
	if (!$field->isRequired ())
		echo '<option value="0">--</option>';

	for ($i = 1 ; $i <= 31 ; $i++)
		echo '<option value="'. $i .'"'. ($i == $value [0] ? ' selected' : '') .'>'. ($i < 10 ? '0' : '') . $i .'</option>';
	?>
</select>

<select class="field" style="width: 90px; margin-left: 3px; <?= $field->getStyle () ?>" name="<?= $fieldName ?>[]" id="<?= $fieldId ?>-mm" onchange="JavaScript: global.Date.validate ('<?= $fieldId ?>', this);">
	<?php
	if (!$field->isRequired ())
		echo '<option value="0">--</option>';

	for ($i = 1 ; $i <= 12 ; $i++)
		echo '<option value="'. $i .'"'. ($i == $value [1] ? ' selected' : '') .'>'. month ($i) .'</option>';
	?>
</select>

<select class="field split-date" style="width: 60px; margin-left: 3px; <?= $field->getStyle () ?>" name="<?= $fieldName ?>[]" id="<?= $fieldId ?>" onchange="JavaScript: global.Date.validate ('<?= $fieldId ?>', this);">
	<?php
	if (!$field->isRequired ())
		echo '<option value="0">----</option>';

	for ($i = $firstYear ; $i <= $lastYear ; $i++)
		echo '<option value="'. $i .'"'. ($i == $value [2] ? ' selected' : '') .'>'. $i .'</option>';
	?>
</select>

<input type="hidden" name="<?= $fieldName ?>" value="<?= implode ('-', $value) ?>" id="_HIDDEN_<?= $fieldId ?>" />

<a id="fd-but-<?= $fieldId ?>" tabindex="0" aria-haspopup="true" role="button" href="#" class="date-picker-control"><img id="_CALENDAR_<?= $fieldId ?>" src="titan.php?target=loadFile&file=interface/icon/calendar.gif" border="0" style="vertical-align: top; margin-top: 2px;" title="<?= __ ('Calendar') ?>" /></a>
<?php
if (!$field->isRequired ())
{
	?>
	<a href="#"><img src="titan.php?target=tResource&type=Date&file=clear.png" border="0" style="vertical-align: top; margin-top: 2px;" title="<?= __ ('Clear') ?>" onclick="JavaScript: global.Date.clear ('<?= $fieldId ?>');" /></a>
	<?php
}
$aux = ob_get_contents ();

ob_end_clean();

return $aux;
