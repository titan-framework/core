<? ob_start () ?>
<select class="field chosen" style="<?= $field->getStyle () ?>" name="<?= $fieldName ?>" id="<?= $fieldId ?>">
	<?
    $tzIds = DateTimeZone::listIdentifiers ();
    
	$date = new DateTime ();
	
	foreach ($tzIds as $trash => $value)
		if (preg_match ('/^(America|Antartica|Arctic|Asia|Atlantic|Europe|Indian|Pacific)\//', $value))
		{
			$date->setTimezone (new DateTimeZone ($value));
		
			echo '<option value="'. $value .'"'. ($field->getValue () == $value ? ' selected="selected"' : '') .'>'. str_replace (array ('/', '_'), array (' - ', ' '), $value) .' ('. $date->format('H:i') .')</option>';
		}
	?>
</select>
<? return ob_get_clean () ?>