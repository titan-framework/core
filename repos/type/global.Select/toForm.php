<?
$linkColumn = $field->getLinkColumn ();
$linkView = $field->getLinkView ();

if ($field->useSearch ())
{
	ob_start ();
	?>
	<div class="borderSearch" style="width: 600px; background-color: #FFF;">
		<input type="hidden" name="<?= $fieldName ?>" value="<?= $field->getValue () ?>" id="<?= $fieldId ?>" />
		<input type="text" class="field" style="float: none; width: 550px; <?= $field->getStyle () ?>" name="<?= $fieldName ?>_LABEL_" id="<?= $fieldId ?>_LABEL_" value="<?= Form::toText ($field) ?>" disabled="disabled" />
		<img src="titan.php?target=loadFile&file=interface/icon/view.gif" class="icon" border="0" id="img_<?= $fieldId ?>" style="vertical-align: bottom;" title="Buscar" alt="Search" onclick="JavaScript: global.Select.showSearch ('<?= $fieldId ?>');" />
		<? if (!$field->getValue ()) { ?>
			<img src="titan.php?target=loadFile&file=interface/icon/grey/delete.gif" id="<?= $fieldId ?>_DELETE_" border="0" style="vertical-align: bottom;" title="Limpar" alt="Unset" />
		<? } else { ?>
			<img src="titan.php?target=loadFile&file=interface/icon/delete.gif" class="icon" id="<?= $fieldId ?>_DELETE_" border="0" style="vertical-align: bottom;" title="Limpar" alt="Unset" onclick="JavaScript: global.Select.clear ('<?= $fieldId ?>');" />
		<? } ?>
	</div>
	<div id="<?= $fieldId ?>_SEARCH_" style="display: none; position: relative; width: 100%; height: 300px; border: #990000 2px solid; margin-top: 4px;"> 
		<iframe class="iframeSearch" src="titan.php?target=tScript&amp;file=search&amp;auth=1&amp;type=Select&amp;toSection=<?= Business::singleton ()->getSection (Section::TCURRENT)->getName () ?>&amp;fieldId=<?= $fieldId ?>&amp;search=<?= $field->getSearch () ?>&amp;where=<?= urlencode ($field->getWhere ()) ?>" border="0"></iframe>
	</div>
	<?
	return ob_get_clean ();
}
else
{
	$columns = implode (", ", $field->getColumnsView ());
	
	$sth = $db->prepare ("SELECT ". $columns .", ". $field->getLinkColumn () ." FROM ". $field->getLink () . ($field->getWhere () != '' ? ' WHERE '. $field->getWhere () : '') ." ORDER BY ". $columns);

	$sth->execute ();
	
	ob_start ();
	?>
	<select class="field" style="<?= $field->getStyle () ?>" name="<?= $fieldName ?>" id="<?= $fieldId ?>">
		<?
		echo $field->isRequired () ? '' : '<option value="0">Selecione</option>';
		
		while ($item = $sth->fetch (PDO::FETCH_OBJ))
			echo '<option value="'. $item->$linkColumn .'"'. ($item->$linkColumn == $field->getValue () ? ' selected="selected"' : '') .'>'. $field->makeView ($item) .'</option>';
		?>
	</select>
	<?
	return ob_get_clean ();
}
?>