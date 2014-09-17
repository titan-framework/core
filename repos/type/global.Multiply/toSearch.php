<?
$linkColumn = $field->getLinkColumn ();
$linkView = $field->getLinkView ();

$columns = implode (", ", $field->getColumnsView ());

$values = $field->getValue ();

array_unshift ($values, 0);

$sql = "SELECT ". $columns .", ". $field->getLinkColumn () ." FROM ". $field->getLink () . ($field->getWhere () != '' ? ' WHERE '. $field->getWhere () : '') ." ORDER BY ". $columns;

$sth = $db->prepare ($sql);

$sth->execute ();

ob_start ();

if (!$field->useCheckBoxes ())
{
	?>
	<table id="<?= $fieldId ?>_table"></table>
	<select class="field" style="<?= $field->getStyle () ?>" name="<?= $fieldName ?>_select" id="<?= $fieldId ?>" onchange="JavaScript: global.Multiply.choose ('<?= $fieldName ?>', '<?= $fieldId ?>', this.options[this.selectedIndex].value, this.options[this.selectedIndex].text); this.selectedIndex = 0;">
		<option value="0">Selecione</option>
		<?
		while ($item = $sth->fetch (PDO::FETCH_OBJ))
			echo '<option value="'. $item->$linkColumn .'">'. $field->makeView ($item) .'</option>';
		?>
	</select>
	<script language="javascript" type="text/javascript">
	<?
	$sth = $db->prepare ("SELECT l.". implode (", l.", $field->getColumnsView ()) .", l.". $field->getLinkColumn () ." FROM ". $field->getRelation () ." r INNER JOIN ". $field->getLink () ." l ON r.". $field->getColumn () ." = l.". $field->getLinkColumn () ." WHERE r.". $field->getColumn () ." IN ('". implode ("', '", $values) ."')");
	
	$sth->execute ();
	
	while ($obj = $sth->fetch (PDO::FETCH_OBJ))
	{ 
		?>
		global.Multiply.choose ('<?= $fieldName ?>', '<?= $fieldId ?>', <?= $obj->$linkColumn ?>, '<?= $field->makeView ($obj) ?>');
		<?
	}
	?>
	</script>
	<?
}
else
{
	?>
	<table>
		<?
		while ($item = $sth->fetch (PDO::FETCH_OBJ))
			echo '<tr><td><input type="checkbox" name="'. $fieldName .'[]" value="'. $item->$linkColumn .'" id="check_'. $fieldId .'_'. $item->$linkColumn .'" '. (in_array ($item->$linkColumn, $values) ? 'checked="checked"' : '') .' /></td><td>'. $field->makeView ($item) .'</td></tr>';
		?>
	</table>
	<?
}

return ob_get_clean ();
?>