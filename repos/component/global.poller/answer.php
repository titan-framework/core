<div id="idForm">
	<div style="background-color: #F4F4F4; text-align: center; padding: 10px; margin: 0px auto 20px; border: #990000 1px solid;">
		<label style="color: #990000; font-weight: bold;">Aten&ccedil;&atilde;o!</label> A edição de respostas faz com que todos os votos já registrados para esta enquete sejam apagados.
	</div>
	<table align="center" border="0" width="98%" cellpading="0" cellspacing="0">
		<tr>
			<td width="100%" style="text-align: center; vertical-align: top;">
				<table align="center" border="0" width="390px" cellpadding="0" cellspacing="0">
					<form name="enquete" id="enquete" action="titan.php?target=commit&toSection=<?= $section->getName () ?>&toAction=<?= $action->getName () ?>" method="post">
					<input type="hidden" name="itemId" value="<?= $itemId ?>" />
					<tr>
						<td>Resposta:</td>
						<td>&nbsp;</td>
						<td colspan="2"><input class="field" type="text" name="nova_resposta"></td>
					</tr>
					<tr height="3"><td></td></tr>
					<tr>
						<td colspan="2"></td>
						<td colspan>
							<input class="button" type="button" value="Inserir" onclick="JavaScript: InserirResposta(); document.enquete.nova_resposta.focus(); return true;">
							<input class="button" type="button" value="Modificar" onclick="JavaScript: ModificarResposta(); document.enquete.nova_resposta.focus(); return true;">
							<input class="button" type="button" value="Remover" onclick="JavaScript: RemoverResposta(); document.enquete.nova_resposta.focus(); return true;">
						</td>
						<td></td>
					<tr>
					<tr height="3"><td></td></tr>
					<tr>
						<td colspan="2"></td>
						<td> 
							<select class="field" style="width: 300px; height: 150px;" multiple name="resposta[]">
								<option selected="selected">Insira aqui suas respostas</option>
								<?php
								while ($obj = $sth->fetch (PDO::FETCH_OBJ))
								{
									?>
									<option value="<?= $obj->_label ?>"><?= $obj->_label ?></option>
									<?php
								}
								?>
							</select>
						</td>
						<td valign="middle" style="padding-left: 3;">
							<a href="#" onclick="JavaScript: SubirResposta (); return true;"><img src="titan.php?target=loadFile&file=interface/icon/arrow.up.gif" border="0" /></a><br><br>
							<a href="#" onclick="JavaScript: DescerResposta (); return true;"><img src="titan.php?target=loadFile&file=interface/icon/arrow.down.gif" border="0" /></a>
						</td>
					</tr>
					</form>
				</table>
			</td>
		</tr>
	</table>
</div>