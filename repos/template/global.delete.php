<div id="idForm">
	<?
	$description = Business::singleton ()->getAction (Action::TCURRENT)->getDescription ();
	if (trim ($description) != '')
		echo '<div class="description">'. $description .'</div>';
	
	$warning = Business::singleton ()->getAction (Action::TCURRENT)->getWarning ();
	if (trim ($warning) != '')
		echo '<div class="warning"><b style="color: #900;">'. __ ('Attention!') .'</b> '. $warning .'</div>';
	?>
	<form id="form_<?= $form->getAssign () ?>" action="" method="post">
		<input type="hidden" name="itemId" value="<?= $itemId ?>" />
	</form>
	<div style="background-color: #F4F4F4; text-align: center; padding: 10px; margin: 0px auto 20px; border: #990000 1px solid;">
		<label style="color: #990000; font-weight: bold;"><?= __ ('Attention!') ?></label> <?= __ ('The item will be deleted permanently. Are you sure you want to proceed?') ?>
	</div>
	<?
	while ($group = $form->getGroup ())
	{
		ob_start ();
		?>
		<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
			<?
			$backColor = 'FFF';
			while ($field = $form->getField (FALSE, $group->getId ()))
			{
				$label = Form::toLabel ($field);
				$backColor = $backColor == 'FFF' ? 'F4F4F4' : 'FFF';
				?>
				<tr id="row_<?= $field->getAssign () ?>" height="18px" style="background-color: #<?= $backColor ?>;">
					<?
					if (trim ($label) != '&nbsp;')
					{
						?>
						<td width="20%" nowrap style="text-align: right;"><b><?= $label ?>:</b></td>
						<td><?= Form::toHtml ($field) ?></td>
						<?
					}
					else
						echo '<td colspan="2">'. Form::toHtml ($field) .'</td>';
					?>
				</tr>
				<tr height="2px"><td></td></tr>
				<?
			}
			?>
		</table>
		<?
		$output = ob_get_clean ();

		if ($group->getId ())
		{
			?>
			<fieldset id="group_<?= $group->getId () ?>" class="<?= $group->isVisible () ? 'formGroup' : 'formGroupCollapse' ?>">
				<legend onclick="JavaScript: showGroup (<?= $group->getId () ?>); return false;">
					<?= $group->getLabel () ?>
				</legend>
				<?= trim ($group->getInfo ()) != '' ? '<div class="info">'. $group->getInfo () .'</div>' : '' ?>
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
</div>