<table border="0">
	<tr>
		<td rowspan="2">
			<img src="titan.php?target=loadFile&file=interface/image/update.big.png" border="0" style="margin-right: 10px;" />
		</td>
		<td style="text-align: center; font-weight: bold;">
			Deseja iniciar a atualiza&ccedil;&atilde;o? <label style="color: #990000;">Esta a&ccedil;&atilde;o pode demorar alguns minutos e n&atilde;o pode ser interrompida!</label>
		</td>
	</tr>
	<tr>
		<td style="text-align: center;">
			<input type="button" class="button" style="display: none; border: #CCCCCC 1px solid; color: #CCCCCC;" id="buttonFakeUpdate" value="Atualizar" />
			<input type="button" class="button" style="display: none; border: #CCCCCC 1px solid; color: #CCCCCC;" id="buttonFakeCancel" value="Cancelar" />
			<input type="button" class="button" style="display:;" id="buttonUpdate" value="Atualizar" onclick="JavaScript: update ();" />
			<input type="button" class="button" id="buttonCancel" value="Cancelar" onclick="JavaScript: modalMsg.close ();" />
			<input type="button" class="button" style="display: none;" id="buttonClose" value="Fechar" onclick="JavaScript: upClose ();" />
		</td>
	</tr>
	<tr height="5px"><td></td></tr>
	<tr>
		<td colspan="2" style="text-align: center;">
			<div style="border: #CCCCCC 1px solid; height: 171px; padding: 5px; overflow: auto;">
				<label id="labelUpdate"></label>
			</div>
		</td>
	</tr>
</table>