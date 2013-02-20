<?
if ($field->isEmpty ())
	return '&nbsp;';

$linkColumn = $field->getLinkColumn ();
$linkView = $field->getLinkView ();
$fatherColumn = $field->getFatherColumn ();

$columns = implode (", ", $field->getColumnsView ());

$id = $field->getValue ();

$array = array ();

while (!is_null ($id))
{
	$sth = $db->prepare ("SELECT ". $columns .", ". $field->getLinkColumn () .", ". $field->getFatherColumn () ." FROM ". $field->getLink () ." WHERE ". $field->getLinkColumn () ." = '". $id ."'");
	
	$sth->execute ();
	
	$item = $sth->fetch (PDO::FETCH_OBJ);
	
	$array [] = $field->makeView ($item);
	
	$id = $item->$fatherColumn;
}

$array = array_reverse ($array);

ob_start ();
?>
<span id="_LIST_<?= $fieldId .'_'. $itemId ?>"><?= end ($array) ?></span>
<div id="_TIP_<?= $fieldId .'_'. $itemId ?>" class="tooltip"><span><?= implode (' &raquo; ', $array) ?></span></div>
<script type="text/javascript">new Tooltip('_LIST_<?= $fieldId .'_'. $itemId ?>', '_TIP_<?= $fieldId .'_'. $itemId ?>', {className: 'tooltip', hook: {target: 'topMid', tip: 'bottomMid'}})</script>
<?
return ob_get_clean ();
?>