<style type="text/css">
.fieldEdit
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	color: #330000;
	border: #330000 1px solid;
	width: 300px;
	padding: 3px;
	background: none;
}
.fieldNoEdit
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	color: #555555;
	border: #330000 0px solid;
	width: 300px;
	padding: 3px;
	background: url(titan.php?target=loadFile&file=interface/icon/editable.gif) right no-repeat;
}
.fieldNoEdit:hover
{
	border-width: 1px;
	background: none;
	color: #330000;
}
#idMenuArchitect
{
	margin: 0 auto;
	width: 557px;
}
#errorBanner
{
  margin: 0;
  font-size: 10px;
  font-weight: bold;
  text-align: center;
  position: fixed;
  top: auto;
  left: auto;
  width: 300px;
  right: 10px;
  bottom: 65px;
  padding: 7px;
  padding-bottom: 4px;
  background-color: #DDDDDD;
}

.buttonEnabled
{
	color: #330000;
	border-color: #330000;
}
.buttonDisabled
{
	color: #CCCCCC; border-color: #CCCCCC;
}
</style>
<script language="javascript" type="text/javascript">
var ajax = <?= XOAD_Client::register(new Ajax) ?>;

var unixName = '<?= $itemId ?>';

function editField (form, field)
{
	document.getElementById (form + '_' + field).className = 'fieldEdit';

	var button = document.getElementById ('saveButton_' + form);
	button.className = 'button buttonEnabled';
	button.onclick = function () { saveConfig (form) };
}

function saveConfig (category)
{
	var aux, i, alerts = '';

	showWait ();

	var form = xoad.html.exportForm ('form_' + category);

	try
	{
		if (!ajax.custTitanMain (unixName, form, category))
			throw 'Não foi possível salvar as configurações. Veja o erro no topo da página para maiores detalhes.';

		alerts += makeAlert ('SUCCESS', 'Configurações salvas com sucesso!');
	}
	catch (error)
	{
		alerts += makeAlert ('FAIL', error);
	}

	showArchError (alerts);

	ajax.showMessages ();

	switch (category)
	{
		case 'main':
			aux = new Array ('name', 'description', 'e_mail', 'use_chat', 'debug_mode', 'language', 'all_sections', 'only_firefox');
			break;

		case 'database':
			aux = new Array ('sgbd', 'host', 'port', 'name', 'user', 'password');
			break;

		case 'security':
			aux = new Array ('timeout');
			break;

		default:
			aux = new Array ();
	}

	for (i = 0 ; i < aux.length ; i++)
		document.getElementById (category + '_' + aux [i]).className = 'fieldNoEdit';

	var button = document.getElementById ('saveButton_' + category);

	button.className = 'button buttonDisabled';
	button.onclick = function () {};

	ajax.delay (function () { hideWait (); });
}

function verifyDB ()
{
	showWait ();

	var form = xoad.html.exportForm ('form_database');

	if (!ajax.verifyDB (unixName, form))
	{
		showArchError (makeAlert ('FAIL', 'Impossível conectar ao Banco de Dados. Veja o erro no topo da página para maiores detalhes.'));

		ajax.showMessages ();

		ajax.delay (function () { hideWait (); });

		return false;
	}

	showArchError (makeAlert ('SUCCESS', 'A conexão foi realizada com sucesso.'));

	ajax.delay (function () { hideWait (); });
}

function runOnLoad ()
{
	var file = document.getElementById ('field_skin_logo');

	file.onclick = function () { editFieldFile (); };
	file.className = "fieldNoEdit";

	var img = document.getElementById ('img_field_skin_logo');

	img.style.display = 'none';
}

function editFieldFile ()
{
	var file = document.getElementById ('field_skin_logo');

	file.className = 'fieldEdit';

	file.style.width = '295px';

	var img = document.getElementById ('img_field_skin_logo');

	img.style.display = '';

	var button = document.getElementById ('saveButton_skin');
	button.className = 'button buttonEnabled';
	button.onclick = function () { saveLogo (); };
}

function saveLogo ()
{
	showWait ();

	var alerts = '';

	var id = document.getElementById ('field_skin_logo_real_id').value;

	try
	{
		if (!id)
			throw 'Você deve selecionar uma imagem da base de dados ou fazer upload de uma nova.';

		if (!ajax.saveLogo (id, unixName))
			throw 'Impossível salvar a imagem escolhida como logo da sua aplicação. Veja mais detalhes no topo da página.';

		alerts += makeAlert ('SUCCESS', 'O logo foi vinculado à aplicação com sucesso.');

		var file = document.getElementById ('field_skin_logo');

		file.onclick = function () { editFieldFile (); };
		file.className = "fieldNoEdit";

		var img = document.getElementById ('img_field_skin_logo');

		img.style.display = 'none';

		var button = document.getElementById ('saveButton_skin');

		button.className = 'button buttonDisabled';
		button.onclick = function () {};
	}
	catch (error)
	{
		alerts += makeAlert ('FAIL', error);

		ajax.showMessages ();
	}

	showArchError (alerts);

	ajax.delay (function () { hideWait (); });
}
</script>
<div id="errorBanner" style="display: none;"></div>
<div id="idMenuArchitect">
	<? swf (Business::singleton ()->getSection (Section::TCURRENT)->getComponentPath () .'_image/menu.swf', 557, 65) ?>
</div>
<div id="idForm">
	<fieldset id="group_main" class="formGroup">
		<legend onclick="JavaScript: showGroup ('main'); return false;">
			Informações Principais:
		</legend>
		<div>
			<form id="form_main" method="post">
			<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
				<tr id="row_instance_name" height="24px" style="background-color: #F4F4F4;">
					<td width="20%" nowrap style="text-align: right;"><b>Nome:</b></td>
					<td><input type="text" class="fieldNoEdit" name="name" id="main_name" value="<?= $array ['main']['name'] ?>"  maxlength="256" onfocus="JavaScript: editField ('main', 'name');" /></td>
					<td width="20px" style="vertical-align: top;">&nbsp;</td>
				</tr>
				<tr height="2px"><td></td></tr>
				<tr id="row_instance_description" height="24px" style="background-color: #FFFFFF;">
					<td width="20%" nowrap style="text-align: right;"><b>Nome Unix: </b></td>
					<td><?= $itemId ?></td>
					<td width="20px" style="vertical-align: top;">&nbsp;</td>
				</tr>
				<tr height="2px"><td></td></tr>
				<tr id="row_instance_description" height="24px" style="background-color: #F4F4F4;">
					<td width="20%" nowrap style="text-align: right;"><b>Descrição:</b></td>
					<td><input type="text" class="fieldNoEdit" name="description" id="main_description" value="<?= $array ['main']['description'] ?>"  maxlength="512" onfocus="JavaScript: editField ('main', 'description');" /></td>
					<td width="20px" style="vertical-align: top;">&nbsp;</td>
				</tr>
				<tr height="2px"><td></td></tr>
				<tr id="row_instance_email" height="24px" style="background-color: #FFFFFF;">
					<td width="20%" nowrap style="text-align: right;"><b>E-mail:</b></td>
					<td><input type="text" class="fieldNoEdit" name="e_mail" id="main_e_mail" value="<?= $array ['main']['e-mail'] ?>"  maxlength="256" onfocus="JavaScript: editField ('main', 'e_mail');" /></td>
					<td width="20px" style="vertical-align: top;">&nbsp;</td>
				</tr>
				<tr height="2px"><td></td></tr>
				<tr id="row_instance_login-url" height="24px" style="background-color: #F4F4F4;">
					<td width="20%" nowrap style="text-align: right;"><b>URL da Tela de Login:</b></td>
					<td><a href="<?= $array ['main']['login-url'] ?>" target="_blank"><?= $array ['main']['login-url'] ?></a></td>
					<td width="20px" style="vertical-align: top;">&nbsp;</td>
				</tr>
				<tr height="2px"><td></td></tr>
				<tr id="row_instance_url" height="24px" style="background-color: #FFFFFF;">
					<td width="20%" nowrap style="text-align: right;"><b>URL da Instância:</b></td>
					<td><?= $array ['main']['url'] ?></td>
					<td width="20px" style="vertical-align: top;">&nbsp;</td>
				</tr>
				<tr height="2px"><td></td></tr>
				<tr id="row_instance_core-path" height="24px" style="background-color: #F4F4F4;">
					<td width="20%" nowrap style="text-align: right;"><b>Caminho para o Core:</b></td>
					<td><?= $array ['main']['core-path'] ?></td>
					<td width="20px" style="vertical-align: top;">&nbsp;</td>
				</tr>
				<tr height="2px"><td></td></tr>
				<tr id="row_instance_location" height="24px" style="background-color: #FFFFFF;">
					<td width="20%" nowrap style="text-align: right;"><b>Caminho para o Repositório:</b></td>
					<td><?= $array ['main']['repos-path'] ?></td>
					<td width="20px" style="vertical-align: top;">&nbsp;</td>
				</tr>
				<tr height="2px"><td></td></tr>
				<tr id="row_instance_core-path" height="24px" style="background-color: #F4F4F4;">
					<td width="20%" nowrap style="text-align: right;"><b>Caminho para o Cache:</b></td>
					<td><?= $array ['main']['cache-path'] ?></td>
					<td width="20px" style="vertical-align: top;">&nbsp;</td>
				</tr>
				<tr height="2px"><td></td></tr>
				<tr id="row_instance_use_chat" height="24px" style="background-color: #FFFFFF;">
					<td width="20%" nowrap style="text-align: right;"><b>Habilita chat:</b></td>
					<td>
						<select class="fieldNoEdit" style="width: 305px;" name="use_chat" id="main_use_chat" onfocus="JavaScript: editField ('main', 'use_chat');">
							<option value="false" <?= $array ['main']['use-chat'] == 'false' ? 'selected' : '' ?>>Não</option>
							<option value="true" <?= $array ['main']['use-chat'] == 'true' ? 'selected' : '' ?>>Sim (custo de performance)</option>
						</select>
					</td>
					<td width="20px" style="vertical-align: top;">&nbsp;</td>
				</tr>
				<tr id="row_instance_debug_mode" height="24px" style="background-color: #F4F4F4;">
					<td width="20%" nowrap style="text-align: right;"><b>Modo <i>debug</i>:</b></td>
					<td>
						<select class="fieldNoEdit" style="width: 305px;" name="debug_mode" id="main_debug_mode" onfocus="JavaScript: editField ('main', 'debug_mode');">
							<option value="false" <?= $array ['main']['debug-mode'] == 'false' ? 'selected' : '' ?>>Não</option>
							<option value="true" <?= $array ['main']['debug-mode'] == 'true' ? 'selected' : '' ?>>Sim (não usar em produção)</option>
						</select>
					</td>
					<td width="20px" style="vertical-align: top;">&nbsp;</td>
				</tr>
				<tr id="row_instance_language" height="24px" style="background-color: #FFFFFF;">
					<td width="20%" nowrap style="text-align: right;"><b>Idioma:</b></td>
					<td>
						<select class="fieldNoEdit" style="width: 305px;" name="language" id="main_language" onfocus="JavaScript: editField ('main', 'language');">
							<option value="pt_BR" <?= $array ['main']['language'] == 'pt_BR' ? 'selected' : '' ?>>Português</option>
							<option value="en_US" <?= $array ['main']['language'] == 'en_US' ? 'selected' : '' ?>>Inglês (experimental)</option>
						</select>
					</td>
					<td width="20px" style="vertical-align: top;">&nbsp;</td>
				</tr>
				<tr id="row_instance_all_sections" height="24px" style="background-color: #F4F4F4;">
					<td width="20%" nowrap style="text-align: right;"><b>Listar no menu apenas seções ativas:</b></td>
					<td>
						<select class="fieldNoEdit" style="width: 305px;" name="all_sections" id="main_all_sections" onfocus="JavaScript: editField ('main', 'all_sections');">
							<option value="false" <?= $array ['main']['all-sections'] == 'false' ? 'selected' : '' ?>>Não</option>
							<option value="true" <?= $array ['main']['all-sections'] == 'true' ? 'selected' : '' ?>>Sim</option>
						</select>
					</td>
					<td width="20px" style="vertical-align: top;">&nbsp;</td>
				</tr>
				<tr id="row_instance_only_firefox" height="24px" style="background-color: #FFFFFF;">
					<td width="20%" nowrap style="text-align: right;"><b>Somente Firefox:</b></td>
					<td>
						<select class="fieldNoEdit" style="width: 305px;" name="only_firefox" id="main_only_firefox" onfocus="JavaScript: editField ('main', 'only_firefox');">
							<option value="false" <?= $array ['main']['only-firefox'] == 'false' ? 'selected' : '' ?>>Não</option>
							<option value="true" <?= $array ['main']['only-firefox'] == 'true' ? 'selected' : '' ?>>Sim (experimental)</option>
						</select>
					</td>
					<td width="20px" style="vertical-align: top;">&nbsp;</td>
				</tr>
				<tr>
					<td></td>
					<td colspan="2">
						<input type="button" class="button buttonDisabled" id="saveButton_main" value="Salvar" />
					</td>
				</tr>
			</table>
			</form>
		</div>
	</fieldset>
	<fieldset id="group_skin" class="formGroup">
		<legend onclick="JavaScript: showGroup ('skin'); return false;">
			Aparência:
		</legend>
		<div>
			<form id="form_skin" method="post">
			<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
				<tr id="row_instance_skin" height="24px" style="background-color: #F4F4F4;">
					<td width="20%" nowrap style="text-align: right;"><b>Logo da Aplicação:</b></td>
					<td>
						<?
						$table = 'instance';

						$aux = array (  'type'   => 'File',
										'column' => 'logo',
										'label'  => 'Arquivo',
										'id'     => 'skin_logo');

						$field = Type::factory ($table, $aux);

						if ($logoId) $field->setValue ($logoId);

						echo Form::toForm ($field);
						?>
					</td>
					<td width="20px" style="vertical-align: top;">&nbsp;</td>
				</tr>
				<tr height="2px"><td></td></tr>
				<tr>
					<td></td>
					<td colspan="2">
						<input type="button" class="button buttonDisabled" id="saveButton_skin" value="Salvar" />
					</td>
				</tr>
			</table>
			</form>
		</div>
	</fieldset>
	<fieldset id="group_database" class="formGroup">
		<legend onclick="JavaScript: showGroup ('database'); return false;">
			Banco de Dados:
		</legend>
		<div>
			<form id="form_database" method="post">
			<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
				<tr id="row_instance_name" height="24px" style="background-color: #FFFFFF;">
					<td width="20%" nowrap style="text-align: right;"><b>SGBD:</b></td>
					<td>
						<select class="fieldNoEdit" style="width: 305px;" name="sgbd" id="database_sgbd" onfocus="JavaScript: editField ('database', 'sgbd');">
							<option value="PostgreSQL" <?= $array ['database']['sgbd'] == 'PostgreSQL' ? 'selected' : '' ?>>PostgreSQL</option>
						</select>
					</td>
					<td width="20px" style="vertical-align: top;">&nbsp;</td>
				</tr>
				<tr height="2px"><td></td></tr>
				<tr id="row_instance_description" height="24px" style="background-color: #F4F4F4;">
					<td width="20%" nowrap style="text-align: right;"><b>Servidor:</b></td>
					<td><input type="text" class="fieldNoEdit" name="host" id="database_host" value="<?= $array ['database']['host'] ?>"  maxlength="256" onfocus="JavaScript: editField ('database', 'host');" /></td>
					<td width="20px" style="vertical-align: top;">&nbsp;</td>
				</tr>
				<tr height="2px"><td></td></tr>
				<tr id="row_instance_port" height="24px" style="background-color: #FFFFFF;">
					<td width="20%" nowrap style="text-align: right;"><b>Porta:</b></td>
					<td><input type="text" class="fieldNoEdit" name="port" id="database_port" value="<?= $array ['database']['port'] ?>"  maxlength="512" onfocus="JavaScript: editField ('database', 'port');" /></td>
					<td width="20px" style="vertical-align: top;">&nbsp;</td>
				</tr>
				<tr height="2px"><td></td></tr>
				<tr id="row_instance_description" height="24px" style="background-color: #F4F4F4;">
					<td width="20%" nowrap style="text-align: right;"><b>Nome:</b></td>
					<td><input type="text" class="fieldNoEdit" name="name" id="database_name" value="<?= $array ['database']['name'] ?>"  maxlength="512" onfocus="JavaScript: editField ('database', 'name');" /></td>
					<td width="20px" style="vertical-align: top;">&nbsp;</td>
				</tr>
				<tr height="2px"><td></td></tr>
				<tr id="row_instance_email" height="24px" style="background-color: #FFFFFF;">
					<td width="20%" nowrap style="text-align: right;"><b>Usuário:</b></td>
					<td><input type="text" class="fieldNoEdit" name="user" id="database_user" value="<?= $array ['database']['user'] ?>"  maxlength="256" onfocus="JavaScript: editField ('database', 'user');" /></td>
					<td width="20px" style="vertical-align: top;">&nbsp;</td>
				</tr>
				<tr height="2px"><td></td></tr>
				<tr id="row_instance_login-url" height="24px" style="background-color: #F4F4F4;">
					<td width="20%" nowrap style="text-align: right;"><b>Senha:</b></td>
					<td><input type="password" class="fieldNoEdit" name="password" id="database_password" value="<?= $array ['database']['password'] ?>"  maxlength="256" onfocus="JavaScript: editField ('database', 'password');" /></td>
					<td width="20px" style="vertical-align: top;">&nbsp;</td>
				</tr>
				<tr height="2px"><td></td></tr>
				<tr>
					<td></td>
					<td colspan="2">
						<input type="button" class="button buttonDisabled" id="saveButton_database" value="Salvar" />
						<input type="button" class="button" style="color: #330000; border-color: #330000;" value="Verificar Configuração" onclick="JavaScript: verifyDB ();" />
					</td>
				</tr>
			</table>
			</form>
		</div>
	</fieldset>
	<fieldset id="group_security" class="formGroup">
		<legend onclick="JavaScript: showGroup ('security'); return false;">
			Segurança:
		</legend>
		<div>
			<form id="form_security" method="post">
			<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
				<tr id="row_instance_name" height="24px" style="background-color: #F4F4F4;">
					<td width="20%" nowrap style="text-align: right;"><b>Timeout:</b></td>
					<td>
						<select class="fieldNoEdit" style="width: 305px;" name="timeout" id="security_timeout" onfocus="JavaScript: editField ('security', 'timeout');">
							<option value="900" <?= $array ['security']['timeout'] == '900' ? 'selected' : '' ?>>15 Minutos</option>
							<option value="1800" <?= $array ['security']['timeout'] == '1800' ? 'selected' : '' ?>>30 Minutos</option>
							<option value="3600" <?= $array ['security']['timeout'] == '3600' ? 'selected' : '' ?>> 1 Hora</option>
							<option value="43200" <?= $array ['security']['timeout'] == '43200' ? 'selected' : '' ?>>12 Horas</option>
							<option value="86400" <?= $array ['security']['timeout'] == '86400' ? 'selected' : '' ?>>24 Horas</option>
						</select>
					</td>
					<td width="20px" style="vertical-align: top;">&nbsp;</td>
				</tr>
				<tr height="2px"><td></td></tr>
				<tr>
					<td></td>
					<td colspan="2">
						<input type="button" class="button buttonDisabled" id="saveButton_security" value="Salvar" />
					</td>
				</tr>
			</table>
			</form>
		</div>
	</fieldset>
</div>
