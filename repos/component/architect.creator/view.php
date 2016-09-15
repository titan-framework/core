<div id="idForm">
	<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
		<?php
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
			<?php
		}
		?>
	</table>
</div>