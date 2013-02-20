<div id="idSearch" style="display: none;">
	<form id="formChangePasswd" action="" method="post">
	<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
		<tr>
			<td colspan="3" class="cTitle">Mudar Senha</td>
		</tr>
	</table>
	<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0" style="border: #36817C 1px solid; border-top-width: 3px;">
		<tr height="5px"><td></td></tr>
		<tr height="18px" style="background-color: #FFFFFF;">
			<td width="20%" nowrap style="text-align: right;"><b>Senha Atual:</b></td>
			<td><input type="password" name="password" class="field" /></td>
			<td width="20px" style="vertical-align: top;"></td>
		</tr>
		<tr height="2px"><td></td></tr>
		<tr height="18px" style="background-color: #F4F4F4;">
			<td width="20%" nowrap style="text-align: right;"><b>Nova Senha:</b></td>
			<td><input type="password" name="newPassword" class="field" onkeypress="JavaScript: strong (this, event);" /></td>
			<td width="20px" style="vertical-align: top;"></td>
		</tr>
		<tr height="2px"><td></td></tr>
		<tr height="18" id="rowStrong" style="display: none;">
			<td>&nbsp;</td>
			<td colspan="2">
				<div id="idStrong" style="position: relative; font-weight: bold;"></div>
				<img id="imgStrong" style="margin: 3px 0px 3px 0px;" src="titan.php?target=loadFile&file=interface/image/passwd.very_weak.gif" border="0" />
			</td>
		</tr>
		<tr height="2px"><td></td></tr>
		<tr height="18px" style="background-color: #FFFFFF;">
			<td width="20%" nowrap style="text-align: right;"><b>Repetir Senha:</b></td>
			<td><input type="password" name="repeat" class="field" /></td>
			<td width="20px" style="vertical-align: top;"></td>
		</tr>
		<tr height="2px"><td></td></tr>
		<tr>
			<td></td>
			<td colspan="2">
				<input type="button" class="button" value="Mudar" onclick="JavaScript: changePassword ('formChangePasswd');" />
				<input type="button" class="button" value="Cancelar" onclick="JavaScript: showSearch ();" />
			</td>
		</tr>
		<tr height="5px"><td></td></tr>
	</table>
	</form>
</div>
<? include Template::import ('global.create') ?>