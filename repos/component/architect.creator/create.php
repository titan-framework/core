<script language="javascript" type="text/javascript">
var ajax = <?= XOAD_Client::register(new Ajax) ?>;

var unixName = '';

function verify ()
{
	showWait ();
	
	var field = document.getElementById ('field_instance_unix');
	
	var alerts = '';
	
	try
	{
		if (field.value.replace(/ /g, '') == '')
			throw 'Você deve inserir um Nome Unix para sua instância.';

		var name = ajax.nameValidate (field.value);
		
		field.value = name;
		
		if (!ajax.verify ('UNIQUE', name))
			throw 'O nome escolhido já está sendo utilizado por outra instância.';
		
		alerts += makeAlert ('SUCCESS', 'O nome escolhido é único e pode ser utilizado.');
		
		if (!ajax.verify ('VALID_PATH', name))
			throw 'O caminho para o diretório de instâncias, em [instance/], não é válido.';
		
		alerts += makeAlert ('SUCCESS', 'O caminho para o diretório de instâncias é válido.');
		
		var flagExists = false;
	
		if (!ajax.verify ('EXISTS', name))
		{
			alerts += makeAlert ('WARNING', 'Já existe uma pasta com este nome no diretório de instâncias, você pode optar por reaproveitar esta pasta importando seu conteúdo.');
			
			flagExists = true;
		}
		else
			alerts += makeAlert ('SUCCESS', 'Não existe nenhuma pasta com este nome no diretório de instâncias, logo, poderá ser criada uma nova pasta.');
		
		if (!ajax.verify ('WRITABLE', name))
			throw 'O <b>Titan Architect</b> não possui direitos de escrita no diretório de instâncias, isto é fundamental para copiar os arquivos base da nova aplicação.';
		
		alerts += makeAlert ('SUCCESS', 'O <b>Titan Architect</b> possui direitos de escrita no diretório de instâncias.');
		
		var labelButtons = document.getElementById ('labelButtons');
		
		if (flagExists)
			labelButtons.innerHTML = '<input type="button" class="button" value="Importar Instância &raquo;" onclick="JavaScript: importInstance (\'' + name + '\');" />';
		else
			labelButtons.innerHTML = '<input type="button" class="button" value="Criar Instância &raquo;" onclick="JavaScript: copyInstance (\'' + name + '\');" />';
						
	}
	catch (error)
	{
		alerts += makeAlert ('FAIL', error);
		
		ajax.showMessages ();
	}
	
	document.getElementById ('labelAlerts').innerHTML = alerts;
	
	ajax.delay (function () { hideWait (); });
}

function copyInstance (name)
{
	showWait ();
	
	document.getElementById ('row_button').style.display = 'none';
	
	document.getElementById ('labelButtons').innerHTML = '';
	document.getElementById ('labelAlerts').innerHTML = '';
	
	document.getElementById ('field_instance_name').style.borderWidth = '0px';
	document.getElementById ('field_instance_unix').style.borderWidth = '0px';
	document.getElementById ('field_instance_description').style.borderWidth = '0px';
	document.getElementById ('field_instance_email').style.borderWidth = '0px';
	document.getElementById ('field_instance_base').style.borderWidth = '0px';
	
	document.getElementById ('field_instance_name').style.backgroundColor = '#F4F4F4';
	document.getElementById ('field_instance_unix').style.backgroundColor = '#F4F4F4';
	document.getElementById ('field_instance_description').style.backgroundColor = '#F4F4F4';
	document.getElementById ('field_instance_email').style.backgroundColor = '#F4F4F4';
	document.getElementById ('field_instance_base').style.backgroundColor = '#F4F4F4';
	
	document.getElementById ('field_instance_name').readOnly = true;
	document.getElementById ('field_instance_unix').readOnly = true;
	document.getElementById ('field_instance_description').readOnly = true;
	document.getElementById ('field_instance_email').readOnly = true;
	
	var form, alerts = '';
	
	form = xoad.html.exportForm ('formConfigInstance');
	
	if (!ajax.saveInstance (name, true, form))
	{
		alerts = makeAlert ('FAIL', 'A instância não pode ser salva no Banco de Dados do <b>Titan Architect</b>.');
		
		document.getElementById ('labelAlerts').innerHTML = alerts;
		
		ajax.showMessages ();
		
		ajax.delay (function () { hideWait (); });
		
		return false;
	}
	
	alerts = makeAlert ('SUCCESS', 'A instância foi salva no Banco de Dados do <b>Titan Architect</b>.');
	
	document.getElementById ('labelAlerts').innerHTML = alerts;
	
	document.getElementById ('row_copy').style.display = '';
	
	document.getElementById ('iframeCopy').src = 'titan.php?target=script&toSection=<?= $section->getName () ?>&file=copyBase&base=' + form ['instance_base'] + '&name=' + name;
}

function confirmCopy (name)
{
	var alerts = makeAlert ('SUCCESS', 'Os arquivos-base da nova instância foram copiados com sucesso para a pasta [instance/' + name + '/].');
	
	var form = xoad.html.exportForm ('formConfigInstance');
	
	if (!ajax.custTitanMain (name, form))
	{
		alerts += makeAlert ('FAIL', 'Impossível modificar os dados de configuração da nova instância.');
		
		document.getElementById ('labelAlerts').innerHTML = alerts;
		
		ajax.showMessages ();
		
		ajax.delay (function () { hideWait (); });
		
		return false;
	}
	
	alerts += makeAlert ('SUCCESS', 'Configuração da nova instância realizada com sucesso.');
	
	document.getElementById ('labelAlertsBottom').innerHTML = alerts;
	
	document.getElementById ('labelButtons').innerHTML = '<input type="button" class="button" value="Configurar Banco de Dados &raquo;" onclick="JavaScript: configureDB (\'' + name + '\', 0);" />';
	
	ajax.delay (function () { hideWait (); });
}

function importInstance (name)
{
	showWait ();
	
	document.getElementById ('row_button').style.display = 'none';
	
	document.getElementById ('labelButtons').innerHTML = '';
	document.getElementById ('labelAlerts').innerHTML = '';
	
	document.getElementById ('field_instance_name').style.borderWidth = '0px';
	document.getElementById ('field_instance_unix').style.borderWidth = '0px';
	document.getElementById ('field_instance_description').style.borderWidth = '0px';
	document.getElementById ('field_instance_email').style.borderWidth = '0px';
	document.getElementById ('field_instance_base').style.borderWidth = '0px';
	
	document.getElementById ('field_instance_name').style.backgroundColor = '#F4F4F4';
	document.getElementById ('field_instance_unix').style.backgroundColor = '#F4F4F4';
	document.getElementById ('field_instance_description').style.backgroundColor = '#F4F4F4';
	document.getElementById ('field_instance_email').style.backgroundColor = '#F4F4F4';
	document.getElementById ('field_instance_base').style.backgroundColor = '#F4F4F4';
	
	document.getElementById ('field_instance_name').readOnly = true;
	document.getElementById ('field_instance_unix').readOnly = true;
	document.getElementById ('field_instance_description').readOnly = true;
	document.getElementById ('field_instance_email').readOnly = true;
	
	var form, alerts = '';
	
	form = xoad.html.exportForm ('formConfigInstance');
	
	if (!ajax.importInstance (name, form))
	{
		alerts += makeAlert ('FAIL', 'Os dados da instância não puderam ser importados.');
		
		document.getElementById ('labelAlerts').innerHTML = alerts;
		
		ajax.showMessages ();
		
		ajax.delay (function () { hideWait (); });
		
		return false;
	}
	
	alerts += makeAlert ('SUCCESS', 'Os dados não preenchidos foram recuperados da instância que esta sendo importada.');
	
	form = xoad.html.exportForm ('formConfigInstance');
	
	if (!ajax.saveInstance (name, true, form))
	{
		alerts += makeAlert ('FAIL', 'A instância não pode ser salva no Banco de Dados do <b>Titan Architect</b>.');
		
		document.getElementById ('labelAlerts').innerHTML = alerts;
		
		ajax.showMessages ();
		
		ajax.delay (function () { hideWait (); });
		
		return false;
	}
	
	alerts += makeAlert ('SUCCESS', 'A instância foi salva no Banco de Dados do <b>Titan Architect</b>.');
	
	if (!ajax.custTitanMain (name, form))
	{
		alerts += makeAlert ('FAIL', 'Impossível modificar os dados de configuração da nova instância.');
		
		document.getElementById ('labelAlerts').innerHTML = alerts;
		
		ajax.showMessages ();
		
		ajax.delay (function () { hideWait (); });
		
		return false;
	}
	
	alerts += makeAlert ('SUCCESS', 'Configuração da nova instância realizada com sucesso.');
	
	document.getElementById ('labelAlerts').innerHTML = alerts;
	
	document.getElementById ('labelButtons').innerHTML = '<input type="button" class="button" value="Configurar Banco de Dados &raquo;" onclick="JavaScript: configureDB (\'' + name + '\', 1);" />';
	
	ajax.delay (function () { hideWait (); });
}

function configureDB (name, reuse)
{
	document.getElementById ('labelButtons').innerHTML = '';
	document.getElementById ('labelAlerts').innerHTML = '';
	document.getElementById ('labelAlertsBottom').innerHTML = '';
	document.getElementById ('row_copy').style.display = 'none';
	
	if (reuse)
	{
		showWait ();
		
		ajax.loadDbConfig (name);
		
		ajax.delay (function () { hideWait (); });
	}
	else if (<?= $dbFlag ? 'true' : 'false' ?>)
	{
		showWait ();
		
		ajax.createDb (name);
		
		ajax.delay (function () { hideWait (); });
	}
	
	document.getElementById ('row_database').style.display = '';
	
	document.getElementById ('labelButtons').innerHTML = '<input type="button" class="button" value="Verificar Configuração" onclick="JavaScript: verifyDB (\'' + name + '\', ' + reuse + ');" />';
}

function verifyDB (name, reuse)
{
	document.getElementById ('labelButtons').innerHTML = '';
	document.getElementById ('labelAlerts').innerHTML = '';
	
	showWait ();
	
	var form = xoad.html.exportForm ('formConfigDB');

	if (!ajax.verifyDB (name, form))
	{
		document.getElementById ('labelAlerts').innerHTML = makeAlert ('FAIL', 'Impossível conectar ao Banco de Dados. Veja o erro no topo da página para maiores detalhes.');
		
		document.getElementById ('labelButtons').innerHTML = '<input type="button" class="button" value="Verificar Configuração" onclick="JavaScript: verifyDB (\'' + name + '\', ' + reuse + ');" />';
		
		ajax.showMessages ();
		
		ajax.delay (function () { hideWait (); });
		
		return false;
	}
	
	document.getElementById ('labelMessage').innerHTML = '';
	
	document.getElementById ('labelAlerts').innerHTML = makeAlert ('SUCCESS', 'A conexão foi realizada com sucesso.');
	
	var str = '';
	
	str += '<input type="button" class="button" value="Verificar Configuração" onclick="JavaScript: verifyDB (\'' + name + '\', ' + reuse + ');" />&nbsp;';
	str += '<input type="button" class="button" value="Gerar Base de Dados da Instância &raquo;" onclick="JavaScript: makeDB (\'' + name + '\', ' + reuse + ');" />';
	
	document.getElementById ('labelButtons').innerHTML = str;
	
	ajax.delay (function () { hideWait (); });
}

function makeDB (name, reuse)
{
	showWait ();
	
	document.getElementById ('labelButtons').innerHTML = '';
	document.getElementById ('labelAlerts').innerHTML = '';
	
	var form = xoad.html.exportForm ('formConfigDB');
	
	document.getElementById ('row_database').style.display = 'none';
	
	var alerts = '';
	
	try
	{
		if (!ajax.custTitanMain (name, form, 'database'))
			throw 'Impossível salvar as configurações do Banco de Dados na instância.';
		
		alerts += makeAlert ('SUCCESS', 'Configurações do Banco de Dados da instâncias salvas com sucesso.');
		
		if (!ajax.verifyDB (name, form, true))
			throw 'Impossível conectar ao Banco de Dados. Veja o erro no topo da página para maiores detalhes.';
		
		alerts += makeAlert ('SUCCESS', 'A conexão foi realizada com sucesso.');
		
		if (!ajax.makeDB (name))
			throw 'A execução do script de criação das entidades do Banco de Dados da instância falhou. Veja o erro no topo da página para mais detalhes.';
		
		alerts += makeAlert ('SUCCESS', 'As entidades foram inseridas com sucesso no Banco de Dados da instância.');
		
		unixName = name;
		
		document.getElementById ('row_advance').style.display = '';
	}
	catch (error)
	{
		alerts += makeAlert ('FAIL', error);
		
		ajax.showMessages ();
		
		document.getElementById ('labelButtons').innerHTML = '<input type="button" class="button" value="Tentar Novamente" onclick="JavaScript: makeDB (\'' + name + '\', ' + reuse + ');" />';
	}
	
	document.getElementById ('labelAlerts').innerHTML = alerts;
	
	ajax.delay (function () { hideWait (); });
}
function goToWizard ()
{
	document.location = 'titan.php?target=body&toSection=<?= $section->getName () ?>&toAction=step_1&itemId=' + unixName;
}
</script>
<div id="idForm">
	<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
		<form id="formConfigInstance" action="" method="post">
		<tr height="18px" style="background-color: #F4F4F4;">
			<td width="20%" nowrap style="text-align: right;"><b>Nome:</b></td>
			<td style="vertical-align: top; width: 400px;">
				<input type="text" class="field" style="width: 400px; border: #000000 1px solid;" name="instance_name" id="field_instance_name" value=""  maxlength="256" />
			</td>
			<td>&nbsp;</td>
			<td width="20px" style="vertical-align: top;">
				<img src="titan.php?target=loadFile&file=interface/icon/help.gif" border="0" style="vertical-align: middle;" title="header=[Título da Instância] body=[O nome da sua aplicação.] cssheader=[divHelpHeader] cssbody=[divHelpBody] fade=[on] offsetx=[-310]" />
			</td>
		</tr>
		<tr height="3px"><td></td></tr>
		<tr height="18px" style="background-color: #F4F4F4;">
			<td width="20%" nowrap style="text-align: right;"><b>Nome Unix:</b></td>
			<td style="vertical-align: top; width: 400px;">
				<input type="text" class="field" style="width: 400px; border: #000000 1px solid;" name="instance_unix" id="field_instance_unix" value=""  maxlength="255" />
			</td>
			<td>&nbsp;</td>
			<td width="20px" style="vertical-align: top;">
				<img src="titan.php?target=loadFile&file=interface/icon/help.gif" border="0" style="vertical-align: middle;" title="header=[Nome Unix da Instância] body=[Preencha corretamente com um nome único, que não contenha espaços, acentos ou caracteres especiais. Se estiver importando uma instância existente preencha apenas este campo, os demais serão recuperados da instância.] cssheader=[divHelpHeader] cssbody=[divHelpBody] fade=[on] offsetx=[-310]" />
			</td>
		</tr>
		<tr height="3px"><td></td></tr>
		<tr height="18px" style="background-color: #F4F4F4;">
			<td width="20%" nowrap style="text-align: right;"><b>Descrição:</b></td>
			<td style="vertical-align: top; width: 400px;">
				<input type="text" class="field" style="width: 400px; border: #000000 1px solid;" name="instance_description" id="field_instance_description" value=""  maxlength="512" />
			</td>
			<td>&nbsp;</td>
			<td width="20px" style="vertical-align: top;">
				<img src="titan.php?target=loadFile&file=interface/icon/help.gif" border="0" style="vertical-align: middle;" title="header=[Descrição] body=[Insira uma breve descrição da aplicação.] cssheader=[divHelpHeader] cssbody=[divHelpBody] fade=[on] offsetx=[-310]" />
			</td>
		</tr>
		<tr height="3px"><td></td></tr>
		<tr height="18px" style="background-color: #F4F4F4;">
			<td width="20%" nowrap style="text-align: right;"><b>E-mail:</b></td>
			<td style="vertical-align: top; width: 400px;">
				<input type="text" class="field" style="width: 400px; border: #000000 1px solid;" name="instance_email" id="field_instance_email" value=""  maxlength="256" />
			</td>
			<td>&nbsp;</td>
			<td width="20px" style="vertical-align: top;">
				<img src="titan.php?target=loadFile&file=interface/icon/help.gif" border="0" style="vertical-align: middle;" title="header=[E-mail de Administração] body=[É necessário vincular a aplicação a um e-mail. Insira o endereço eletrônico de um responsável ou um e-mail institucional.] cssheader=[divHelpHeader] cssbody=[divHelpBody] fade=[on] offsetx=[-310]" />
			</td>
		</tr>
		<tr height="3px"><td></td></tr>
		<tr height="18px" style="background-color: #F4F4F4;">
			<td width="20%" nowrap style="text-align: right;"><b>Sistema Base:</b></td>
			<td style="vertical-align: top; width: 400px;">
				<select class="field" style="width: 400px; border: #000000 1px solid;" name="instance_base" id="field_instance_base">
					<option value="basic">Número mínimo de seções, um sistema de usuários</option>
				</select>
			</td>
			<td>&nbsp;</td>
			<td width="20px" style="vertical-align: top;">
				<img src="titan.php?target=loadFile&file=interface/icon/help.gif" border="0" style="vertical-align: middle;" title="header=[Sistema Base] body=[Para iniciar a configuração de uma instância será feita a cópia de um sistema-base. Este recurso tem o intuito de facilitar o trabalho de quem esta instanciando uma nova aplicação, reusando exemplos.] cssheader=[divHelpHeader] cssbody=[divHelpBody] fade=[on] offsetx=[-310]" />
			</td>
		</tr>
		</form>
		<tr height="3px"><td></td></tr>
		<tr height="18px" id="row_button">
			<td></td>
			<td style="text-align: right;">
				<input type="button" class="button" style="height: 19px; display:;" value="Verificar &raquo;" onclick="JavaScript: verify ();" />
			</td>
			<td>&nbsp;</td>
			<td></td>
		</tr>
		<tr height="10px"><td></td></tr>
		<tr id="row_copy" style="display: none;">
			<td></td>
			<td colspan="3">
				<iframe id="iframeCopy" src="" style="width: 400px; height: 200px; border: #000000 1px solid;" scrolling="auto"></iframe>
			</td>
		</tr>
		<tr id="row_database" style="display: none;">
			<td colspan="4">
				<form id="formConfigDB" action="" method="post">
				<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
					<tr>
						<td colspan="4" style="border-bottom: #CCCCCC 1px solid; height: 20px; font-weight: bold;">
							Configuração do Banco de Dados:
						</td>
					</tr>
					<tr height="10px"><td></td></tr>
					<tr>
						<td colspan="4" style="border: #990000 1px solid; padding: 5px; text-align: justify;">
							<label style="color: #990000; font-weight: bold;">Atenção!</label> Você deve criar um usuário com senha e um Banco de Dados em algum servidor acessível
							para que o <b>Titan Architect</b> possa criar as entidades da base de dados desta instância. Se você não possui os privilégios para efetuar esta ação, 
							peça a um administrador ou responsável. Você não precisa se preocupar com a compilação da base, isto será feito de forma automática. Se o Banco de Dados 
							já contiver entidades com o mesmo nome das que deverão ser criadas elas <b>NÃO</b> serão sobreescritas.
						</td>
					</tr>
					<tr height="10px"><td></td></tr>
					<tr height="18px" style="background-color: #F4F4F4;">
						<td width="20%" nowrap style="text-align: right;"><b>SGBD:</b></td>
						<td style="vertical-align: top; width: 400px;">
							<select class="field" style="width: 400px;" name="sgbd" id="database_sgbd" />
								<option value="PostgreSQL">PostgreSQL</option>
							</select>
						</td>
						<td>
							<label id="labelAlertSgbd">&nbsp;</label>
						</td>
						<td width="20px" style="vertical-align: top;">
							<img src="titan.php?target=loadFile&file=interface/icon/help.gif" border="0" style="vertical-align: middle;" title="header=[SGBD] body=[Qual será o SGBD que a instância irá utilizar.] cssheader=[divHelpHeader] cssbody=[divHelpBody] fade=[on] offsetx=[-310]" />
						</td>
					</tr>
					<tr height="3px"><td></td></tr>
					<tr height="18px" style="background-color: #F4F4F4;">
						<td width="20%" nowrap style="text-align: right;"><b>Servidor:</b></td>
						<td style="vertical-align: top; width: 400px;">
							<input type="text" class="field" style="width: 400px;" name="host" id="database_server" value="localhost"  maxlength="256" />
						</td>
						<td>
							<label id="labelAlertServer">&nbsp;</label>
						</td>
						<td width="20px" style="vertical-align: top;">
							<img src="titan.php?target=loadFile&file=interface/icon/help.gif" border="0" style="vertical-align: middle;" title="header=[Host do Servidor] body=[Nome do host aonde estará hospedado o Bando de Dados da instância. Ex.: <i>localhost</i>.] cssheader=[divHelpHeader] cssbody=[divHelpBody] fade=[on] offsetx=[-310]" />
						</td>
					</tr>
					<tr height="3px"><td></td></tr>
					<tr height="18px" style="background-color: #F4F4F4;">
						<td width="20%" nowrap style="text-align: right;"><b>Porta:</b></td>
						<td style="vertical-align: top; width: 400px;">
							<input type="text" class="field" style="width: 400px;" name="port" id="database_port" value="5432"  maxlength="256" />
						</td>
						<td>
							<label id="labelAlertServer">&nbsp;</label>
						</td>
						<td width="20px" style="vertical-align: top;">
							<img src="titan.php?target=loadFile&file=interface/icon/help.gif" border="0" style="vertical-align: middle;" title="header=[Porta do SGBD] body=[Porta em que o SGBD recebe conexões ao Bando de Dados. Ex.: <i>5432</i> (padrão para PostgreSQL).] cssheader=[divHelpHeader] cssbody=[divHelpBody] fade=[on] offsetx=[-310]" />
						</td>
					</tr>
					<tr height="3px"><td></td></tr>					
					<tr height="18px" style="background-color: #F4F4F4;">
						<td width="20%" nowrap style="text-align: right;"><b>Nome:</b></td>
						<td style="vertical-align: top; width: 400px;">
							<input type="text" class="field" style="width: 400px;" name="name" id="database_name" value=""  maxlength="256" />
						</td>
						<td>
							<label id="labelAlertLogin">&nbsp;</label>
						</td>
						<td width="20px" style="vertical-align: top;">
							<img src="titan.php?target=loadFile&file=interface/icon/help.gif" border="0" style="vertical-align: middle;" title="header=[Nome] body=[Nome do Banco de Dados.] cssheader=[divHelpHeader] cssbody=[divHelpBody] fade=[on] offsetx=[-310]" />
						</td>
					</tr>
					<tr height="3px"><td></td></tr>
					<tr height="18px" style="background-color: #F4F4F4;">
						<td width="20%" nowrap style="text-align: right;"><b>Login:</b></td>
						<td style="vertical-align: top; width: 400px;">
							<input type="text" class="field" style="width: 400px;" name="user" id="database_login" value=""  maxlength="256" />
						</td>
						<td>
							<label id="labelAlertLogin">&nbsp;</label>
						</td>
						<td width="20px" style="vertical-align: top;">
							<img src="titan.php?target=loadFile&file=interface/icon/help.gif" border="0" style="vertical-align: middle;" title="header=[Login] body=[Login para conexão no servidor de Banco de Dados.] cssheader=[divHelpHeader] cssbody=[divHelpBody] fade=[on] offsetx=[-310]" />
						</td>
					</tr>
					<tr height="3px"><td></td></tr>
					<tr height="18px" style="background-color: #F4F4F4;">
						<td width="20%" nowrap style="text-align: right;"><b>Senha:</b></td>
						<td style="vertical-align: top; width: 400px;">
							<input type="password" class="field" style="width: 400px;" name="password" id="database_password" value=""  maxlength="256" />
						</td>
						<td>
							<label id="labelAlertPassword">&nbsp;</label>
						</td>
						<td width="20px" style="vertical-align: top;">
							<img src="titan.php?target=loadFile&file=interface/icon/help.gif" border="0" style="vertical-align: middle;" title="header=[Senha] body=[Senha para conexão no servidor de Banco de Dados.] cssheader=[divHelpHeader] cssbody=[divHelpBody] fade=[on] offsetx=[-310]" />
						</td>
					</tr>
					<tr height="3px"><td></td></tr>
				</table>
				</form>
			</td>
		</tr>
		<tr>
			<td></td>
			<td colspan="3">
				<table border="0" width="400px" cellpadding="0" cellspacing="0">
					<tr>
						<td>
							<label id="labelAlerts"></label>
							<label id="labelAlertsBottom"></label>
						</td>
					</tr>
					<tr height="3px"><td></td></tr>
					<tr>
						<td style="text-align: right;">
							<label id="labelButtons"></label>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr id="row_advance" style="display: none;">
			<td></td>
			<td colspan="3">
				<style type="text/css">
				.advanceButton
				{
					border: #009900 1px solid;
					width: 400px;
					height: 56px;
					vertical-align: middle;
				}
				.advanceButton label
				{
					float: left;
					font-family: Arial, Helvetica, sans-serif;
					font-size: 12px;
					font-weight: bold;
					color: #009900;
					margin-top: 20px;
					margin-left: 20px;
				}
				.advanceButton img
				{
					float: right;
					margin: 3px;
				}
				.advanceButton:hover
				{
					background-color: #CCEBCC;
					cursor: pointer;
				}
				</style>
				<div id="advanceWizard" class="advanceButton" onclick="JavaScript: goToWizard ();">
					<label>Avançar para o Wizard de Configuração da Instância</label>
					<img src="titan.php?target=loadFile&file=interface/image/arrow.green.png" border="0" />
				</div>
			</td>
		</tr>
	</table>
</div>
