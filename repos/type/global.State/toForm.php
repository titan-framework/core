<?php
$columns = implode (", ", $field->getColumnsView ());

$sth = $db->prepare ("SELECT ". $columns .", ". $field->getLinkColumn () ." FROM ". $field->getLink () . ($field->getWhere () != '' ? ' WHERE '. $field->getWhere () : '') ." ORDER BY ". $columns);

$sth->execute ();

ob_start ();
?>

<select class="field" style="<?= $field->getStyle () ?>" name="<?= $fieldName ?>" id="<?= $fieldId ?>" <?= $field->getCityId () !== FALSE ? 'onchange="JavaScript: global.City.load (\''. $field->getCityId () .'\', \''. $fieldId .'\');"' : '' ?>>
	<option value="0">Selecione</option>
	<?php
	$linkColumn = $field->getLinkColumn ();
	$linkView = $field->getLinkView ();
	
	while ($item = $sth->fetch (PDO::FETCH_OBJ))
		echo '<option value="'. $item->$linkColumn .'"'. ($item->$linkColumn == $field->getValue () ? ' selected="selected"' : '') .'>'. $field->makeView ($item) .'</option>';
	?>
</select>

<?php
return ob_get_clean ();
?>