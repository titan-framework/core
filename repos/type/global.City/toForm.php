<?
ob_start ();
?>

<select class="field" style="<?= $field->getStyle () ?>" name="<?= $fieldName ?>" id="<?= $fieldId ?>">

<?
if ($field->getValue ())
{
	$columns = implode (", ", $field->getColumnsView ());
	
	$sth = $db->prepare ("	SELECT ". $columns .", ". $field->getLinkColumn () ."
							FROM ". $field->getLink () ." 
							WHERE _state = (SELECT _state 
											FROM ". $field->getLink () ." 
											WHERE ". $field->getLinkColumn () ." = '". $field->getValue () ."')
							ORDER BY ". $columns);

	$sth->execute ();
	
	$linkColumn = $field->getLinkColumn ();
	$linkView = $field->getLinkView ();
	
	while ($item = $sth->fetch (PDO::FETCH_OBJ))
		echo '<option value="'. $item->$linkColumn .'"'. ($item->$linkColumn == $field->getValue () ? ' selected="selected"' : '') .'>'. $field->makeView ($item) .'</option>';
}
elseif ($state = $field->getState ())
{
	$columns = implode (", ", $field->getColumnsView ());
	
	$sth = $db->prepare ("	SELECT ". $columns .", ". $field->getLinkColumn () ."
							FROM ". $field->getLink () ." 
							WHERE _state = '". $state ."'
							ORDER BY ". $columns);

	$sth->execute ();
	
	$linkColumn = $field->getLinkColumn ();
	$linkView = $field->getLinkView ();
	
	while ($item = $sth->fetch (PDO::FETCH_OBJ))
		echo '<option value="'. $item->$linkColumn .'"'. ($item->$linkColumn == $field->getValue () ? ' selected="selected"' : '') .'>'. $field->makeView ($item) .'</option>';
}
else
	echo '<option value="0">Selecione um Estado</option>';
?>

</select>

<?
return ob_get_clean ();
?>