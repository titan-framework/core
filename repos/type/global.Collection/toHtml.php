<?

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
			<?
			while ($auxField = $view->getField ())
				echo '<td class="cTableHeader">'. View::toLabel ($auxField, FALSE) .'</td>';
			?>
		</tr>
		<tr height="5px"><td></td></tr>
		<?
		$bkpItemId = $itemId;
		
		while ($view->getItem ())
		{
			$itemId = $view->getId ();
			?>
			<tr class="cTableItem" id="collection_row_<?= $view->getId () ?>">
				<? while ($auxField = $view->getLink ()) echo '<td>'. $auxField .'</td>'; ?>
			</tr>
			<tr class="cSeparator"><td></td></tr>
			<?
		}
		
		$itemId = $bkpItemId;
		?>
	</table>
</div>
<?
return ob_get_clean ();
?>