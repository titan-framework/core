<script language="javascript" type="text/javascript">
function saveRelation ()
{
	sizeUsers = document.forms[0].elements['selectSystemsFromType[]'].length;
	
	for(i = 0 ; i < sizeUsers ; i++)
		document.forms[0].elements['selectSystemsFromType[]'].options[i].selected = true;
	
	document.forms[0].submit();
}
</script>
<form id="form_<?= $form->getAssign () ?>" action="titan.php?target=commit&toSection=<?= $section->getName () ?>&toAction=<?= $action->getName () ?>" method="post">
<input type="hidden" name="typeIdHidden" value="<?= $typeSelected ?>" />
<table align="center" border="0" width="100%" cellpading="0" cellspacing="0">
	<tr>
		<td width="100%" style="text-align: center; vertical-align: top;">
			<table align="center" border="0" width="500" cellpadding="0" cellspacing="0">
				<tr height="10"><td></td></tr>
				<tr>
					<td width="235px" rowspan="2">
						<select id="selectSystems" name="selectSystems" class="field" style="width: 100%;  height: 241px;" multiple>
							<?
							foreach ($arraySystem as $keySystem => $valueSystem)
								echo '<option value="'. $keySystem .'">'. $valueSystem .'</option>'
							?>
						</select>
					</td>
					<td width="30px">&nbsp;</td>
					<td width="235px">
						<select id="selectTypes" name="selectTypes" class="field" style="width: 100%;" onChange="JavaScript: saveRelation ();">
							<option value="0">Selecione um Tipo de Usuário</option>
							<?
							foreach ($arrayType as $keyType => $valueType)
								echo '<option value="'. $keyType .'" '. ($typeSelected == $keyType ? 'selected' : '') .'>'. $valueType .'</option>'
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td style="text-align: center;">
						<a href="#" onClick="JavaScript: changeSelect ('selectSystems', 'selectSystemsFromType[]', 'selectTypes');">
							<img src="titan.php?target=loadFile&file=interface/icon/arrow.right.gif" border="0" title="Adicionar" />
						</a>
						<br><br>
						<a href="#" onClick="JavaScript: changeSelect ('selectSystemsFromType[]', 'selectSystems', 'selectTypes');">
							<img src="titan.php?target=loadFile&file=interface/icon/arrow.left.gif" border="0" title="Retirar" />
						</a>
					</td>
					<td>
						<select id="selectSystemsFromType" name="selectSystemsFromType[]" class="field" style="width: 100%; height: 215px;" multiple>
							<?
							foreach ($arrayRelation as $keySystem => $valueSystem)
								echo '<option value="'. $keySystem .'">'. $valueSystem .'</option>'
							?>
						</select>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</form>