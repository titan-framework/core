<?php

$xml = Business::singleton ()->getAction (Action::TCURRENT)->getXmlPath ();

Business::singleton ()->getAction (Action::TCURRENT)->setXmlPath (FALSE);

$view = new View ($field->getXmlPath ());

Business::singleton ()->getAction (Action::TCURRENT)->setXmlPath ($xml);

global $itemId;

if (!$view->load ($field->getColumn () ." = '". $itemId ."'"))
	throw new Exception ('Não foi possível carregar dados!');

ob_start ();
?>
<div id="idList" style="margin: 0px;">
	<table style="background-color: #FFF;">
		<tr>
			<?php
			$columns = sizeof ($view->getFields ());
			
			while ($auxField = $view->getField ())
				echo '<td class="cTableHeader">'. View::toLabel ($auxField, FALSE) .'</td>';
			?>
		</tr>
		<tr height="5px"><td colspan="<?= $columns ?>"></td></tr>
		<?php
		$bkpItemId = $itemId;
		
		while ($view->getItem ())
		{
			$itemId = $view->getId ();
			?>
			<tr class="cTableItem" id="collection_row_<?= $view->getId () ?>">
				<?php while ($auxField = $view->getLink ()) echo '<td>'. $auxField .'</td>'; ?>
			</tr>
			<tr class="cSeparator"><td colspan="<?= $columns ?>"></td></tr>
			<?php
		}
		
		$itemId = $bkpItemId;
		?>
	</table>
</div>
<?php
return ob_get_clean ();
?>