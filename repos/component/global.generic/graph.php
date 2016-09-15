<div id="idSearch" style="display: none;">
	<form action="<?= $_SERVER['PHP_SELF'] .'?target=body&toSection='. $section->getName () .'&toAction='. $action->getName () ?>" method="post">
	<input type="hidden" name="search" value="1" />
	<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
		<tr>
			<td colspan="3" class="cTitle"><?= __ ('Filter') ?></td>
		</tr>
	</table>
	<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0" style="border: #36817C 1px solid; border-top-width: 3px;">
		<tr height="5px"><td></td></tr>
		<?php
		$backColor = 'FFFFFF';
		while ($field = $search->getField ())
		{
			$backColor = $backColor == 'FFFFFF' ? 'F4F4F4' : 'FFFFFF';
			?>
			<tr height="18px" style="background-color: #<?= $backColor ?>;">
				<td width="20%" nowrap style="text-align: right;"><b><?= $field->getLabel () ?>:</b></td>
				<td><?= $search->isBlocked ($field) ? Form::toHtml ($field) : Search::toForm ($field) ?></td>
				<td width="20px" style="vertical-align: top;"><?= Form::toHelp ($field); ?></td>
			</tr>
			<tr height="2px"><td></td></tr>
			<?php
		}
		?>
		<tr>
			<td></td>
			<td colspan="2">
				<input type="submit" class="button" value="<?= __ ('Search') ?>" />
				<input type="button" class="button" value="<?= __ ('Cancel') ?>" onclick="JavaScript: showSearch ();" />
			</td>
		</tr>
		<tr height="5px"><td></td></tr>
	</table>
	</form>
</div>
<div id="idForm">
	<?php
	while ($group = $graph->getGroup ())
	{
		ob_start ();
		?>
		<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
			<?php
			$where = $search->makeWhere ();
			$flag = FALSE;

			while ($img = $graph->getGraph (FALSE, $group->getId (), $where))
			{
				$flag = TRUE;
				?>
				<tr>
					<td style="text-align: center;"><img border="0" src="<?= $img ?>" /></td>
				</tr>
				<tr height="10px"><td></td></tr>
				<?php
			}
			?>
		</table>
		<?php
		$output = ob_get_clean ();

		if ($group->getId () && $flag)
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
</div>