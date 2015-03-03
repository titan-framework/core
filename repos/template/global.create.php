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
		<?
		$notSavables = array ();
		
		while ($group = $form->getGroup ())
		{
			ob_start ();
			
			$hasField = FALSE;
			
			try
			{
				?>
				<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
					<?
					$backColor = 'FFFFFF';
					while ($field = $form->getField (FALSE, $group->getId ()))
					{
						if (!$field->isSubmittable ())
						{
							if (array_key_exists ($group->getId (), $notSavables))
								$notSavables [$group->getId ()][] = $field->getAssign ();
							else
								$notSavables [$group->getId ()] = array ($field->getAssign ());
							
							continue;
						}
						
						$hasField = TRUE;
						
						$backColor = $backColor == 'FFFFFF' ? 'F4F4F4' : 'FFFFFF';
						$label = Form::toLabel ($field, TRUE);
						
						if ($field->useFullWidth ())
						{
							if ((trim ($label) != '' && trim ($label) != '&nbsp;') || trim ($field->getHelp ()) != '')
							{
								?>
								<tr height="18px" style="background-color: #<?= $backColor ?>;">
									<td colspan="2" nowrap="nowrap" style="text-align: left;"><b><?= trim ($label) == '&nbsp;' ? '&nbsp;' : $label .':' ?></b></td>
									<td width="20px" style="vertical-align: top;"><?= Form::toHelp ($field) ?></td>
								</tr>
								<?
							}
							?>
							<tr id="row_<?= $field->getAssign () ?>">
								<td colspan="3"><?= Form::toForm ($field) ?></td>
							</tr>
							<tr height="2px"><td></td></tr>
							<?
						}
						else
						{
							?>
							<tr id="row_<?= $field->getAssign () ?>" height="18px" style="background-color: #<?= $backColor ?>;">
								<td width="20%" nowrap="nowrap" style="text-align: right;"><b><?= trim ($label) == '&nbsp;' ? '&nbsp;' : $label .':' ?></b></td>
								<td><?= Form::toForm ($field) ?></td>
								<td width="20px" style="vertical-align: top;"><?= Form::toHelp ($field) ?></td>
							</tr>
							<tr height="2px"><td></td></tr>
							<?
						}
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
			
			if ($hasField)
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
	</form>
	<?
	foreach ($notSavables as $key => $fields)
	{
		try
		{
			ob_start ();
			?>
			<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
				<?
				$backColor = 'FFFFFF';
				foreach ($fields as $trash => $keyField)
				{
					$field = $form->getField ($keyField);
					
					if (is_null ($field))
						continue;
					
					$backColor = $backColor == 'FFFFFF' ? 'F4F4F4' : 'FFFFFF';
					$label = Form::toLabel ($field, TRUE);
					
					if ($field->useFullWidth ())
					{
						if ((trim ($label) != '' && trim ($label) != '&nbsp;') || trim ($field->getHelp ()) != '')
						{
							?>
							<tr height="18px" style="background-color: #<?= $backColor ?>;">
								<td colspan="2" nowrap="nowrap" style="text-align: left;"><b><?= trim ($label) == '&nbsp;' ? '&nbsp;' : $label .':' ?></b></td>
								<td width="20px" style="vertical-align: top;"><?= Form::toHelp ($field) ?></td>
							</tr>
							<?
						}
						?>
						<tr id="row_<?= $field->getAssign () ?>">
							<td colspan="3"><?= Form::toForm ($field) ?></td>
						</tr>
						<tr height="2px"><td></td></tr>
						<?
					}
					else
					{
						?>
						<tr id="row_<?= $field->getAssign () ?>" height="18px" style="background-color: #<?= $backColor ?>;">
							<td width="20%" nowrap="nowrap" style="text-align: right;"><b><?= trim ($label) == '&nbsp;' ? '&nbsp;' : $label .':' ?></b></td>
							<td><?= Form::toForm ($field) ?></td>
							<td width="20px" style="vertical-align: top;"><?= Form::toHelp ($field) ?></td>
						</tr>
						<tr height="2px"><td></td></tr>
						<?
					}
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
		
		if ($key)
		{
			$group = $form->getGroup ($key);
			?>
			<fieldset id="group_<?= $key ?>" class="<?= $group->isVisible () ? 'formGroup' : 'formGroupCollapse' ?>">
				<legend onclick="JavaScript: showGroup (<?= $key ?>); return false;">
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