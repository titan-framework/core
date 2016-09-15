<form id="form_<?= $form->getAssign () ?>" action="titan.php?target=commit&toSection=<?= $section->getName () ?>&toAction=<?= $action->getName () ?>" method="post">
<input type="hidden" name="itemId" value="<?= $itemId ?>" />
<table align="center" border="0" width="100%" cellpading="0" cellspacing="0">
	<tr>
		<td width="100%" style="text-align: center; vertical-align: top;">
			<table align="center" border="0" width="500" cellpadding="0" cellspacing="0">
				<tr height="10"><td></td></tr>
				<tr>
					<td width="235px" rowspan="2">
						<select id="selectFrom" name="selectFrom" class="field" style="width: 100%;  height: 241px;" multiple>
							<?php
							foreach ($arrayFrom as $keyFrom => $valueFrom)
								echo '<option value="'. $keyFrom .'">'. $valueFrom .'</option>'
							?>
						</select>
					</td>
					<td width="30px">&nbsp;</td>
					<td width="235px">
						<select id="selectItem" name="selectItem" class="field" style="width: 100%;" onChange="JavaScript: saveRelation ();">
							<option value="0"><?= __ ('Select an item') ?></option>
							<?php
							foreach ($arrayItem as $keyItem => $valueItem)
								echo '<option value="'. $keyItem .'" '. ($itemId == $keyItem ? 'selected' : '') .'>'. $valueItem .'</option>'
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td style="text-align: center;">
						<a href="#" onClick="JavaScript: changeSelect ('selectFrom', 'selectFor[]', 'selectItem');">
							<img src="titan.php?target=loadFile&file=interface/icon/arrow.right.gif" border="0" title="<?= __ ('Add') ?>" />
						</a>
						<br><br>
						<a href="#" onClick="JavaScript: changeSelect ('selectFor[]', 'selectFrom', 'selectItem');">
							<img src="titan.php?target=loadFile&file=interface/icon/arrow.left.gif" border="0" title="<?php __ ('Remove') ?>" />
						</a>
					</td>
					<td>
						<select id="selectFor" name="selectFor[]" class="field" style="width: 100%; height: 215px;" multiple>
							<?php
							foreach ($arrayFor as $keyFor => $valueFor)
								echo '<option value="'. $keyFor .'">'. $valueFor .'</option>'
							?>
						</select>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</form>