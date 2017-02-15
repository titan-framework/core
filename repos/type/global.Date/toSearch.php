<?php

if ($field->isEmpty ())
	$value = array (array (0, 0, 0), array (0, 0, 0));
else
	$value = $field->getValue ();

$firstYear = $field->getFirstYear ();

$lastYear = $field->getLastYear ();

if (!$firstYear)
	$firstYear = date ('Y') - 30;

if (!$lastYear)
	$lastYear = date ('Y') + 30;

ob_start ();
?>
<div class="tGlobalDateSearchContainer">
	<div class="tGlobalDateSearchLabel">
		<?= __ ('From') ?>
	</div>
	<div class="tGlobalDateSearchSelects">
		<select class="field" style="width: 45px; <?= $field->getStyle () ?>" name="<?= $fieldName ?>[0][0]" id="<?= $fieldId ?>-from-dd" onchange="JavaScript: global.Date.validateSearch ('<?= $fieldId ?>', '-from', this);">
			<option value="0">--</option>
			<?php
			for ($i = 1 ; $i <= 31 ; $i++)
				echo '<option value="'. $i .'"'. ($i == $value [0][0] ? ' selected' : '') .'>'. ($i < 10 ? '0' : '') . $i .'</option>';
			?>
		</select>

		<select class="field" style="width: 85px; margin-left: 3px; <?= $field->getStyle () ?>" name="<?= $fieldName ?>[0][1]" id="<?= $fieldId ?>-from-mm" onchange="JavaScript: global.Date.validateSearch ('<?= $fieldId ?>', '-from', this);">
			<option value="0">--</option>
			<?php
			for ($i = 1 ; $i <= 12 ; $i++)
				echo '<option value="'. $i .'"'. ($i == $value [0][1] ? ' selected' : '') .'>'. month ($i) .'</option>';
			?>
		</select>

		<select class="field split-date" style="width: 60px; margin-left: 3px; <?= $field->getStyle () ?>" name="<?= $fieldName ?>[0][2]" id="<?= $fieldId ?>-from" onchange="JavaScript: global.Date.validateSearch ('<?= $fieldId ?>', '-from', this);">
			<option value="0">----</option>
			<?php
			for ($i = $firstYear ; $i <= $lastYear ; $i++)
				echo '<option value="'. $i .'"'. ($i == $value [0][2] ? ' selected' : '') .'>'. $i .'</option>';
			?>
		</select>

		<input type="hidden" name="<?= $fieldName ?>[0]" value="<?= implode ('-', $value [0]) ?>" id="_HIDDEN_<?= $fieldId ?>-from" />
	</div>
	<div class="tGlobalDateSearchIcon">
		<a id="fd-but-<?= $fieldId ?>-from" tabindex="0" aria-haspopup="true" role="button" href="#" class="date-picker-control"><img id="_CALENDAR_<?= $fieldId ?>-from" src="titan.php?target=loadFile&file=interface/icon/calendar.gif" border="0" style="vertical-align: top;" title="Calendário" /></a>
	</div>
	<div class="tGlobalDateSearchLabel">
		<?= __ ('to') ?>
	</div>
	<div class="tGlobalDateSearchSelects">
		<select class="field" style="width: 45px; <?= $field->getStyle () ?>" name="<?= $fieldName ?>[1][0]" id="<?= $fieldId ?>-to-dd" onchange="JavaScript: global.Date.validateSearch ('<?= $fieldId ?>', '-to', this);">
			<option value="0">--</option>
			<?php
			for ($i = 1 ; $i <= 31 ; $i++)
				echo '<option value="'. $i .'"'. ($i == $value [1][0] ? ' selected' : '') .'>'. ($i < 10 ? '0' : '') . $i .'</option>';
			?>
		</select>

		<select class="field" style="width: 85px; margin-left: 3px; <?= $field->getStyle () ?>" name="<?= $fieldName ?>[1][1]" id="<?= $fieldId ?>-to-mm" onchange="JavaScript: global.Date.validateSearch ('<?= $fieldId ?>', '-to', this);">
			<option value="0">--</option>
			<?php
			for ($i = 1 ; $i <= 12 ; $i++)
				echo '<option value="'. $i .'"'. ($i == $value [1][1] ? ' selected' : '') .'>'. month ($i) .'</option>';
			?>
		</select>

		<select class="field split-date" style="width: 60px; margin-left: 3px; <?= $field->getStyle () ?>" name="<?= $fieldName ?>[1][2]" id="<?= $fieldId ?>-to" onchange="JavaScript: global.Date.validateSearch ('<?= $fieldId ?>', '-to', this);">
			<option value="0">----</option>
			<?php
			for ($i = $firstYear ; $i <= $lastYear ; $i++)
				echo '<option value="'. $i .'"'. ($i == $value [1][2] ? ' selected' : '') .'>'. $i .'</option>';
			?>
		</select>

		<input type="hidden" name="<?= $fieldName ?>[1]" value="<?= implode ('-', $value [1]) ?>" id="_HIDDEN_<?= $fieldId ?>-to" />
	</div>
	<div class="tGlobalDateSearchIcon">
		<a id="fd-but-<?= $fieldId ?>-to" tabindex="0" aria-haspopup="true" role="button" href="#" class="date-picker-control"><img id="_CALENDAR_<?= $fieldId ?>-to" src="titan.php?target=loadFile&file=interface/icon/calendar.gif" border="0" style="vertical-align: top;" title="Calendário" /></a>
	</div>
	<div class="tGlobalDateSearchClear">
		<a href="#"><img src="titan.php?target=tResource&type=Date&file=clear.png" border="0" style="vertical-align: top; margin-top: 2px;" title="<?= __ ('Clear') ?>" onclick="JavaScript: global.Date.clearSearch ('<?= $fieldId ?>');" /></a>
	</div>
</div>
<?php
$aux = ob_get_contents ();

ob_end_clean();

return $aux;
