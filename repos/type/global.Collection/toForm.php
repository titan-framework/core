<?
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
?><fieldset id="collection_edit_<?= $fieldId ?>" style="display: none; margin: 10px 0px; border: #900 2px solid; background-color: #FFF;">
	<legend id="collection_label_<?= $fieldId ?>"></legend>
	<div id="idForm">
		<form id="collection_form_<?= $fieldId ?>" action="" method="post">
			<input type="hidden" name="itemId" id="collection_id_<?= $fieldId ?>" value="" />
			<?
			while ($group = $form->getGroup ())
			{
				ob_start ();
				
				try
				{
					?>
					<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
						<?
						$backColor = 'FFFFFF';
						while ($auxField = $form->getField (FALSE, $group->getId ()))
						{
							$backColor = $backColor == 'FFFFFF' ? 'F4F4F4' : 'FFFFFF';
							$label = Form::toLabel ($auxField, TRUE);
							?>
							<tr id="collection_row_<?= $auxField->getAssign () ?>" height="18px" style="background-color: #<?= $backColor ?>;">
								<td width="20%" nowrap style="text-align: right;"><b><?= trim ($label) == '&nbsp;' ? '&nbsp;' : $label .':' ?></b></td>
								<td><?= Form::toForm ($auxField) ?></td>
								<td width="20px" style="vertical-align: top;"><?= Form::toHelp ($auxField); ?></td>
							</tr>
							<tr height="2px"><td></td></tr>
							<?
						}
						?>
					</table>
					<?
					$output = ob_get_clean ();
				}
				catch (Exception $e)
				{
					ob_end_clean ();
					
					throw new Exception ($e->getMessage ());
				}
				catch (PDOException $e)
				{
					ob_end_clean ();
					
					throw new PDOException ($e->getMessage ());
				}
				
				if ($group->getId ())
				{
					?>
					<fieldset id="group_<?= $group->getId () ?>" class="<?= $group->isVisible () ? 'formGroup' : 'formGroupCollapse' ?>">
						<legend onclick="JavaScript: showGroup (<?= $group->getId () ?>); return false;">
							<?= $group->getLabel () ?>
						</legend>
						<div>
							<?= $output ?>
						</div>
					</fieldset>
					<?
				}
				else
					echo $output;
			}
			?>
			<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
				<tr height="18px">
					<td width="20%"></td>
					<td colspan="2">
						<input type="button" value="Adicionar Item" class="button" onclick="JavaScript: global.Collection.save ('<?= $itemId ?>', '<?= $field->getColumn () ?>', '<?= $fieldId ?>', '<?= $field->getXmlPath () ?>');" />
						<input type="button" value="Fechar" class="button" onclick="JavaScript: global.Collection.create ('<?= $fieldId ?>', <?= $itemId ?>);" />
					</td>
				</tr>
			</table>
		</form>
	</div>
</fieldset>
<div id="idList" style="margin: 0px;">
	<table id="collection_view_<?= $fieldId ?>" style="background-color: #FFF;">
		<tr>
			<?
			$columns = sizeof ($view->getFields ()) + 1;
			
			while ($auxField = $view->getField ())
				echo '<td class="cTableHeader">'. View::toLabel ($auxField, FALSE) .'</td>';
			?>
			<td class="cTableHeader" style="text-align: right; cursor: pointer;"><img src="titan.php?target=loadFile&file=interface/icon/create.gif" border="0" title="Inserir Item" onclick="JavaScript: global.Collection.create ('<?= $fieldId ?>', <?= $itemId ?>);" /></td>
		</tr>
		<tr height="5px"><td colspan="<?= $columns ?>"></td></tr>
		<?
		$bkpItemId = $itemId;
		
		while ($view->getItem ())
		{
			$itemId = $view->getId ();
			?>
			<tr class="cTableItem" id="collection_row_<?= $view->getId () ?>">
				<? while ($auxField = $view->getLink ()) echo '<td>'. $auxField .'</td>'; ?>
				<td style="text-align: right; display:;" nowrap="nowrap">
					<!-- <img src="titan.php?target=loadFile&file=interface/icon/edit.gif" border="0" title="Editar" style="cursor: pointer;" />&nbsp; -->
					<img src="titan.php?target=loadFile&file=interface/icon/delete.gif" border="0" title="Apagar" style="cursor: pointer;" onclick="JavaScript: global.Collection.delRow ('<?= $fieldId ?>', '<?= $field->getXmlPath () ?>', '<?= $view->getId () ?>');" />&nbsp;
				</td>
			</tr>
			<tr class="cSeparator" id="collection_row_<?= $view->getId () ?>_space"><td colspan="<?= $columns ?>"></td></tr>
			<?
		}
		
		$itemId = $bkpItemId;
		?>
	</table>
</div>
<?
return ob_get_clean ();
?>