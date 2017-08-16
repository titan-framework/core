<?php

$xml = Business::singleton ()->getAction (Action::TCURRENT)->getXmlPath ();

Business::singleton ()->getAction (Action::TCURRENT)->setXmlPath (FALSE);

$view = new View ($field->getXmlPath ());
Business::singleton ()->getAction (Action::TCURRENT)->setXmlPath ($xml);

$form = new Form ($field->getXmlPath ());

if (isset ($_SESSION['_TITAN_FORMS_FOR_SAVE_']['_ITEM_ID_']))
	$itemId = $_SESSION['_TITAN_FORMS_FOR_SAVE_']['_ITEM_ID_'];
else
	global $itemId;

if (!isset ($itemId) || (is_numeric ($itemId) && !(int) $itemId) || trim ($itemId) == '')
	$itemId = 0;

if (!$view->load ($field->getColumn () ." = '". $itemId ."'"))
	throw new Exception ('Não foi possível carregar dados!');

ob_start ();
?>
<label id="collectionLabelMessage_<?= $fieldId ?>" class="collectionLabelMessage"></label>
<div id="collection_create_or_edit_<?= $fieldId ?>" style="display: none;"></div>
<div id="idList" style="margin: 0px;">
	<table style="background-color: #FFF;">
		<tr>
			<?php
			$columns = sizeof ($view->getFields ()) + 1;

			while ($auxField = $view->getField ())
				echo '<td class="cTableHeader">'. View::toLabel ($auxField, FALSE) .'</td>';
			?>
			<td class="cTableHeader" style="text-align: right; white-space: nowrap; width: 1px;">
				<?php
				if ($view->isSortable ())
				{
					?>
					<a href="#" class="globalCollectionButton" onclick="JavaScript: global.Collection.saveSort (this, '<?= $field->getXmlPath () ?>', '<?= $fieldId ?>');"><img src="titan.php?target=loadFile&file=interface/icon/save.gif" border="0" /><?= __ ('Save Order') ?></a>&nbsp;
					<?php
				}
				?>
				<a href="#" class="globalCollectionButton" onclick="JavaScript: global.Collection.create ('<?= $fieldId ?>', '<?= $field->getXmlPath () ?>', <?= $itemId ?>, '<?= $field->getColumn () ?>');"><img src="titan.php?target=loadFile&file=interface/icon/create.gif" border="0" /><?= __ ('New') ?></a>
			</td>
		</tr>
		<tbody id="collection_view_<?= $fieldId ?>">
			<tr height="5px"><td colspan="<?= $columns ?>"></td></tr>
			<?php
			$bkpItemId = $itemId;

			while ($view->getItem ())
			{
				$itemId = $view->getId ();
				?>
				<tr class="cTableItem" id="collection_row_<?= $view->getId () ?>">
					<?php while ($auxField = $view->getLink ()) echo '<td>'. $auxField .'</td>'; ?>
					<td style="text-align: right; display:;" nowrap="nowrap">
						<?php
						if ($view->isSortable ())
						{
							?>
							<input type="hidden" name="idForSort" value="<?= $view->getId () ?>" />
							<img src="titan.php?target=loadFile&file=interface/icon/arrow.up.gif" border="0" title="<?= __ ('Up') ?>" style="cursor: pointer;" onclick="JavaScript: global.Collection.up (this);" />&nbsp;
							<img src="titan.php?target=loadFile&file=interface/icon/arrow.down.gif" border="0" title="<?= __ ('Down') ?>" style="cursor: pointer;" onclick="JavaScript: global.Collection.down (this);" />&nbsp;
							<?php
						}
						?>
						<img src="titan.php?target=loadFile&file=interface/icon/edit.gif" border="0" title="<?= __ ('Edit') ?>" style="cursor: pointer;" onclick="JavaScript: global.Collection.edit ('<?= $fieldId ?>', '<?= $field->getXmlPath () ?>', '<?= $view->getId () ?>');" />&nbsp;
						<img src="titan.php?target=loadFile&file=interface/icon/delete.gif" border="0" title="<?= __ ('Delete') ?>" style="cursor: pointer;" onclick="JavaScript: global.Collection.delRow ('<?= $fieldId ?>', '<?= $field->getXmlPath () ?>', '<?= $view->getId () ?>');" />&nbsp;
					</td>
				</tr>
				<tr class="cSeparator" id="collection_row_<?= $view->getId () ?>_space"><td colspan="<?= $columns ?>"></td></tr>
				<?php
			}

			$itemId = $bkpItemId;
			?>
		</tbody>
	</table>
	<script language="javascript" type="text/javascript">
	</script>
</div>
<?php
return ob_get_clean ();
