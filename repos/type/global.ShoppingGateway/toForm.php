<?php
if (!Shopping::isActive ())
	return __ ('The Shopping Module in Titan Framework must be enable!');

$gw = Shopping::singleton ()->getGateways ();

if (!sizeof ($gw) && $field->isRequired ())
	return __ ('None available!');

ob_start ();
?>
<select class="field" style="<?= $field->getStyle () ?>" name="<?= $fieldName ?>" id="<?= $fieldId ?>">
	<?php
	if (!$field->isRequired ())
		echo '<option value="">'. __ ('Select a option...') .'</option>';
	
	foreach ($gw as $id => $value)
		echo '<option value="'. $id .'">'. $value ['account'] .' '. __ ('in') .' '. $value ['driver'] .'</option>';
	?>
</select>
<?php return ob_get_clean () ?>