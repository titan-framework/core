<div id="idForm">
	<?php
	$description = Business::singleton ()->getAction (Action::TCURRENT)->getDescription ();
	if (trim ($description) != '')
		echo '<div class="description">'. $description .'</div>';
	
	$warning = Business::singleton ()->getAction (Action::TCURRENT)->getWarning ();
	if (trim ($warning) != '')
		echo '<div class="warning"><b style="color: #900;">'. __ ('Attention!') .'</b> '. $warning .'</div>';
	?>
	<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
		<tr height="20" style="background-color: #FFFFFF;">
			<td width="20"><input type="checkbox" id="_SELECT_ALL_" onclick="JavaScript: selectAll ();" /></td>
			<td><b>Selecionar Todos</b></td>
			<td></td>
			<td width="20"><input type="checkbox" id="_SEARCH_" /></td>
			<td><b>Exportar somente resultados da busca</b></td>
		</tr>
		<tr height="2px"><td colspan="5" style="border-bottom: 1px #900 solid;"></td></tr>
		<tr height="10px"><td></td></tr>
		<?php
		$backColor = 'FFFFFF';
		$cont = 0;
		
		while (TRUE)
		{
			$backColor = $backColor == 'FFFFFF' ? 'F4F4F4' : 'FFFFFF';
			$flag = FALSE;
			
			if (!($field = $view->getField ()))
				break;
			
			$label1 = Form::toLabel ($field);
			$assign1 = $field->getAssign ();
			
			if ($field = $view->getField ())
			{
				$flag = TRUE;
				
				$label2 = Form::toLabel ($field);
				$assign2 = $field->getAssign ();
			}
			?>
			<tr height="20" style="background-color: #<?= $backColor ?>;">
				<td width="3%"><input type="checkbox" id="check_<?= $cont++ ?>" name="<?= $assign1 ?>" /></td>
				<td width="46%"><?= $label1 ?></td>
				<td width="2%" style="background-color: #FFF;"></td>
				<td width="3%"><?= $flag ? '<input type="checkbox" id="check_'. $cont++ .'" name="'. $assign2 .'" />' : '' ?></td>
				<td width="46%"><?= $flag ? $label2 : '' ?></td>
			</tr>
			<?php
			
			if (!$flag)
				break;
		}
		?>
	</table>
</div>