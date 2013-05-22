<?
global $itemId;

if (!$itemId)
	return __ ('You need save form before use this field!');

$field->initiate ();

ob_start ();

while ($term = $field->getDocument ())
{
	$form = new DocumentForm ($term->getPath () . $term->getTemplate ());

	$form->loadSimple ($term->getSimple ());

	$term->getReplaced ($form, $itemId);
	?>
	<fieldset id="_TERM_<?= $fieldId ?>_<?= $term->getId () ?>" style="display: none; margin: 10px 0px; border: #900 2px solid; background-color: #FFF;">
		<legend id="_TERM_LABEL_<?= $fieldId ?>_<?= $term->getId () ?>"><?= $term->getLabel () ?></legend>
		<div id="idForm">
			<form id="_TERM_FORM_<?= $fieldId ?>_<?= $term->getId () ?>" action="" method="post">
				<input type="hidden" name="id" value="<?= $term->getId () ?>" />
				<?
				while ($group = $form->getGroup ())
				{
					ob_start ();

					try
					{
						?>
						<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
							<?
							$backColor = 'FFF';
							while ($auxField = $form->getField (FALSE, $group->getId ()))
							{
								$backColor = $backColor == 'FFF' ? 'F4F4F4' : 'FFF';
								$label = Form::toLabel ($auxField, TRUE);
								?>
								<tr height="18px" style="background-color: #<?= $backColor ?>;">
									<td width="20%" nowrap style="text-align: right;"><b><?= trim ($label) == '&nbsp;' ? '&nbsp;' : $label .':' ?></b></td>
									<td><?= Form::toForm ($auxField, $term->getId ()) ?></td>
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
							<input type="button" style="border: #090 2px solid; color: #090; width: 150px; height: 50px; background-color: #CCEBCC;" id="_TERM_FORM_SAVE_<?= $fieldId ?>_<?= $term->getId () ?>" value="<?= __ ('Generate Document') ?>" class="button" onclick="JavaScript: global.Document.save ('<?= $fieldId ?>', '<?= $itemId ?>', '<?= $term->getId () ?>', '<?= $field->getRelation () ?>', '<?= $term->getPath () . $term->getTemplate () ?>', '<?= $term->getLabel () ?>', <?= $term->isValidatable () ? '1' : '0' ?>);" />
							<input type="button" style="border-color: #900; color: #900; font-weight: normal;" id="_TERM_FORM_CANCEL_<?= $fieldId ?>_<?= $term->getId () ?>" value="<?= __ ('Cancel') ?>" class="button" onclick="JavaScript: global.Document.cancel ('<?= $fieldId ?>', '<?= $term->getId () ?>');" />
						</td>
					</tr>
				</table>
			</form>
		</div>
	</fieldset>
	<?
}
?>
<div id="idList" style="margin: 0px; background-color: #FFF;">
	<table id="_TERM_VIEW_<?= $fieldId ?>">
		<tr>
			<td class="cTableHeader"><?= __ ('Name') ?></td>
			<td class="cTableHeader"><?= __ ('Version') ?></td>
			<td class="cTableHeader"><?= __ ('Author') ?></td>
			<td class="cTableHeader"><?= __ ('Date') ?></td>
			<td class="cTableHeader">
				<select id="_TERM_SELECT_<?= $fieldId ?>" class="field" style="margin: 0px; float: right; display: none;" onchange="JavaScript: global.Document.showCreate ('<?= $fieldId ?>', this);">
					<option value=""><?= __ ('Select') ?></option>
					<?
					while ($term = $field->getDocument ())
						echo '<option value="'. $term->getId () .'">'. $term->getLabel () .'</option>';
					?>
				</select>
				<img id="_TERM_ADD_<?= $fieldId ?>" src="titan.php?target=loadFile&file=interface/icon/create.gif" style="cursor: pointer; float: right; display: block;" border="0" title="Inserir Item" onclick="JavaScript: global.Document.add ('<?= $fieldId ?>', this);" />
			</td>
		</tr>
		<tr height="5px"><td colspan="5"></td></tr>
		<?
		$db = Database::singleton ();

		$sql = "SELECT u._name AS author, r.*, to_char(r._create, 'DD-MM-YYYY HH24:MI:SS') AS created
				FROM ". $field->getRelation () ." r
				LEFT JOIN ". $db->getSchema () ."._user u ON u._id = r._user
				WHERE r._relation = '". $itemId ."'
				ORDER BY r._create DESC";

		$sth = $db->prepare ($sql);

		$sth->execute ();

		$control = array ();

		while ($obj = $sth->fetch (PDO::FETCH_OBJ))
		{
			if (!$field->docExists ($obj->_id))
				continue;
			
			$valid = FALSE;

			if (!in_array ($obj->_id, $control))
			{
				$control [] = $obj->_id;
				$valid = TRUE;
			}
			?>
			<tr class="cTableItem" id="_TERM_ROW_<?= $fieldId ?>_<?= $obj->_id ?>_<?= $obj->_version ?>" style="cursor: pointer; <?= !$valid ? 'background-image: url(titan.php?target=loadFile&file=interface/back/aba.gif);' : '' ?>" onclick="JavaScript: global.Document.openDocument ('<?= Document::register ($field->getRelation (), $obj->_id, $obj->_relation, $obj->_version, $field->getDocument ($obj->_id)->getPath () . $field->getDocument ($obj->_id)->getTemplate (), $field->getDocument ($obj->_id)->getLabel ()) ?>');">
				<td id="_TERM_COLUMN_<?= $fieldId ?>_<?= $obj->_id ?>_<?= $obj->_version ?>" style="text-decoration: <?= $valid ? 'none' : 'line-through' ?>;"><?= $field->getDocument ($obj->_id)->getLabel () ?></td>
				<td><?= $obj->_version ?></td>
				<td><?= is_null ($obj->author) ? __ ('Generated by the system') : $obj->author ?></td>
				<td><?= $obj->created ?></td>
				<td style="text-align: right; display:;" nowrap="nowrap">
					<img id="_TERM_IMG_<?= $fieldId ?>_<?= $obj->_id ?>_<?= $obj->_version ?>" src="titan.php?target=loadFile&file=interface/icon/<?= $valid ? 'pdf' : 'grey/pdf' ?>.gif" border="0" title="<?= __ ('Generate PDF') ?>" />&nbsp;
				</td>
			</tr>
			<tr class="cSeparator"><td colspan="5"></td></tr>
			<?
		}
		?>
	</table>
</div>
<?
return ob_get_clean ();
?>