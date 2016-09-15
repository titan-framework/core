<div id="idForm">
	<form id="form_<?= $form->getAssign () ?>" action="" method="post">
	<input type="hidden" name="itemId" value="<?= $itemId ?>" />
	<input type="hidden" name="_SEND_" value="0" />
	<?php
	while ($group = $form->getGroup ())
	{
		ob_start ();
		?>
		<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
			<?php
			$backColor = 'FFFFFF';
			while ($field = $form->getField (FALSE, $group->getId ()))
			{
				$backColor = $backColor == 'FFFFFF' ? 'F4F4F4' : 'FFFFFF';
				?>
				<tr id="row_<?= $field->getAssign () ?>" height="18px" style="background-color: #<?= $backColor ?>;">
					<td width="20%" nowrap style="text-align: right;"><b><?= Form::toLabel ($field, TRUE) ?>:</b></td>
					<td><?= Form::toForm ($field) ?></td>
					<td width="20px" style="vertical-align: top;"><?= Form::toHelp ($field); ?></td>
				</tr>
				<tr height="2px"><td></td></tr>
				<?php
			}
			?>
		</table>
		<?php
		$output = ob_get_clean ();
		
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
	</form>
</div>