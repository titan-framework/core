<?php
ob_start ();
?>
<fieldset style="margin: 10px 0px; border: #900 2px solid; background-color: #FFF;">
	<legend><?= __ ('Edit Item') ?></legend>
	<div id="idForm">
		<form id="collection_form_edit_<?= $fieldId ?>_edit" action="" method="post">
			<input type="hidden" name="itemId" id="collection_id_<?= $fieldId ?>" value="<?= $itemId ?>" />
			<?php
			while ($group = $form->getGroup ())
			{
				ob_start ();

				try
				{
					?>
					<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
						<?php
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
							<?php
						}
						?>
					</table>
					<?php
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
					<?php
				}
				else
					echo $output;
			}
			?>
			<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
				<tr height="18px">
					<td width="20%"></td>
					<td colspan="2">
						<input type="button" value="<?= __ ('Save Item') ?>" class="button" onclick="JavaScript: global.Collection.save ('<?= $itemId ?>', '<?= $fatherColumn ?>', '<?= $fieldId ?>', '<?= $file ?>');" />
						<input type="button" value="<?= __ ('Close') ?>" class="button" onclick="JavaScript: global.Collection.create ('<?= $fieldId ?>', <?= $itemId ?>);" />
					</td>
				</tr>
			</table>
		</form>
	</div>
</fieldset>
<?php
return ob_get_clean ();
