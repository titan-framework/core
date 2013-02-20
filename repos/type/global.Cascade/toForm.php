<?
$selects = array ();

$linkColumn = $field->getLinkColumn ();
$linkView = $field->getLinkView ();
$fatherColumn = $field->getFatherColumn ();

$columns = implode (", ", $field->getColumnsView ());

$array [0] = array ();

$sth = $db->prepare ("SELECT ". $columns .", ". $field->getLinkColumn () ." FROM ". $field->getLink () ." WHERE ". $field->getFatherColumn () ." IS NULL". ($field->getWhere () != "" ? " AND ". $field->getWhere () : "") ." ORDER BY ". $columns);

$sth->execute ();

while ($item = $sth->fetch (PDO::FETCH_OBJ))
	$array [0][$item->$linkColumn] = array ($item->$linkColumn, $field->makeView ($item));

$id = $field->getValue ();

$aux = array ();

while (!is_null ($id))
{
	$aux [$id] = array ();
	
	$sth = $db->prepare ("SELECT ". $columns .", ". $field->getLinkColumn () ." FROM ". $field->getLink () ." WHERE ". $field->getFatherColumn () ." = '". $id ."'". ($field->getWhere () != "" ? " AND ". $field->getWhere () : "") ." ORDER BY ". $columns);
	
	$sth->execute ();
	
	while ($item = $sth->fetch (PDO::FETCH_OBJ))
		$aux [$id][$item->$linkColumn] = array ($item->$linkColumn, $field->makeView ($item));
	
	$query = $db->query ("SELECT ". $field->getFatherColumn () ." FROM ". $field->getLink () ." WHERE ". $field->getLinkColumn () ." = '". $id ."'");
	
	$id = $query->fetchColumn ();
}

$array = $array + array_reverse ($aux, TRUE);

ob_start ();
?>
<div id="_DIV_<?= $fieldId ?>" style="float: left; border: #CCC 1px solid; background-color: #FFF; width: 499px; padding: 3px 3px 0px 3px;">
	<?
	foreach ($array as $id => $opts)
	{
		if (!sizeof ($opts))
			continue;
		?>
		<select class="field" style="float: none; margin-bottom: 3px; width: 499px; <?= $field->getStyle () ?>" name="<?= $fieldName ?>_<?= $id ?>" id="<?= $fieldId ?>_<?= $id ?>" onchange="JavaScript: global.Cascade.choose ('<?= $fieldId ?>', '<?= $id ?>', this, '<?= $field->getLink () ?>', '<?= $field->getLinkColumn () ?>', '<?= $field->getFatherColumn () ?>', '<?= $field->getLinkView () ?>')">
			<option value="<?= $id ?>">Selecione...</option>
			<?
			foreach ($opts as $value => $label)
				echo '<option value="'. $value .'"'. (array_key_exists ($value, $array) ? ' selected="selected"' : '') .'>'. $label [1] .'</option>';
			?>
		</select>
		<?
	}
	?>
</div>
<?= $field->getTip () != '' ? '<div class="fieldTip" style="padding: 8px 8px;">'. $field->getTip () .'</div>' : '' ?>
<input type="hidden" name="<?= $fieldName ?>" id="_HIDDEN_<?= $fieldId ?>" value="<?= $field->getValue () ?>" />
<script language="javascript" type="text/javascript">
global.Cascade.values_<?= $fieldId ?> = new Array ('<?= implode ("', '", array_keys ($array)) ?>');
</script>
<?
return ob_get_clean ();
?>