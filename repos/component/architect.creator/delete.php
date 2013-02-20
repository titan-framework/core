<div id="idForm">
	<form id="form_<?= $form->getFile () ?>" action="titan.php?toSection=<?= $section->getName () ?>&toAction=<?= $goTo ?>" method="post">
	<input type="hidden" name="fromAction" value="<?= $action->getName () ?>" />
	<input type="hidden" name="itemId" value="<?= $itemId ?>" />
	<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
		<?
		$backColor = 'FFFFFF';
		while ($field = $form->getField ())
		{
			$backColor = $backColor == 'FFFFFF' ? 'F4F4F4' : 'FFFFFF';
			?>
			<tr id="row_<?= $field->getAssign () ?>" height="18px" style="background-color: #<?= $backColor ?>;">
				<td width="20%" nowrap style="text-align: right;"><b><?= Form::toLabel ($field) ?>:</b></td>
				<td><?= Form::toHtml ($field) ?></td>
			</tr>
			<tr height="2px"><td></td></tr>
			<?
		}
		?>
		<tr>
			<td></td>
			<td colspan="2">
				<input type="submit" class="button" value="<?= $action->getLabel () ?>" />
				<input type="button" class="button" value="Cancelar" onclick="JavaScript: document.location='titan.php?toSection=<?= $section->getName () ?>&toAction=<?= $goTo ?>';" />
			</td>
		</tr>
	</table>
	</form>
</div>