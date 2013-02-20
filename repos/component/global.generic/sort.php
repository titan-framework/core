<div id="idForm" style="text-align: center;">
	<ul id="sortableList">
		<?
		$count = 1;
		while ($view->getItem ())
		{
			$field = $view->getField ('_TITLE_');
			
			echo '<li id="item_'. $view->getId () .'">'. Form::toHtml ($field) .'</li>';
		}
		?>
	</ul>
</div>
<script language="javascript" type="text/javascript">
function saveSort ()
{
	showWait ();
	
	ajax.saveSort (Sortable.serialize('sortableList'), '<?= $view->getTable () ?>', '<?= $view->getPrimary () ?>', function () {
		hideWait ();
	});
}

Sortable.create ('sortableList',{tag:'li'});
</script>