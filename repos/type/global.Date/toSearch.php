<?php
$value = $field->getValue ();

$firstYear = $field->getFirstYear ();

$lastYear = $field->getLastYear ();

if (!$firstYear)
	$firstYear = date ('Y') - 30;

if (!$lastYear)
	$lastYear = date ('Y') + 30;

ob_start ();
?>
<select class="field" style="width: 45px; <?= $field->getStyle () ?>" name="<?= $fieldName ?>[]" id="<?= $fieldId ?>-dd" onchange="JavaScript: global.Date.validateSearch ('<?= $fieldId ?>');">
	<option value="0">--</option>
	<?php
	for ($i = 1 ; $i <= 31 ; $i++)
		echo '<option value="'. $i .'"'. ($i == $value [0] ? ' selected' : '') .'>'. ($i < 10 ? '0' : '') . $i .'</option>';
	?>
</select>

<select class="field" style="width: 90px; margin-left: 3px; <?= $field->getStyle () ?>" name="<?= $fieldName ?>[]" id="<?= $fieldId ?>-mm" onchange="JavaScript: global.Date.validateSearch ('<?= $fieldId ?>');">
	<option value="0">--</option>
	<?php
	for ($i = 1 ; $i <= 12 ; $i++)
		echo '<option value="'. $i .'"'. ($i == $value [1] ? ' selected' : '') .'>'. month ($i) .'</option>';
	?>
</select>

<select class="field split-date" style="width: 60px; margin-left: 3px; <?= $field->getStyle () ?>" name="<?= $fieldName ?>[]" id="<?= $fieldId ?>" onchange="JavaScript: global.Date.validateSearch ('<?= $fieldId ?>');">
	<option value="0">----</option>
	<?php
	for ($i = $firstYear ; $i <= $lastYear ; $i++)
		echo '<option value="'. $i .'"'. ($i == $value [2] ? ' selected' : '') .'>'. $i .'</option>';
	?>
</select>

<input type="hidden" name="<?= $fieldName ?>" value="<?= implode ('-', $value) ?>" id="_HIDDEN_<?= $fieldId ?>" />

<a id="fd-but-<?= $fieldId ?>" tabindex="0" aria-haspopup="true" role="button" href="#" class="date-picker-control"><img id="_CALENDAR_<?= $fieldId ?>" src="titan.php?target=loadFile&file=interface/icon/calendar.gif" border="0" style="vertical-align: top;" title="Calendário" /></a>
<?php
$aux = ob_get_contents ();

ob_end_clean();

return $aux;
?>