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
	width: 200px;
	padding: 3px;
	background: url(titan.php?target=loadFile&file=interface/icon/editable.gif) right no-repeat;
}
.fieldNoEdit:hover
{
	border-width: 1px;
	background: none;
	color: #330000;
}
#existingUsers
{
	padding: 5px;
	margin: 20px auto;
	width: 500px;
	border: 2px solid #009900;
}
#editUser
{
	padding: 5px;
	margin: 20px auto;
	width: 500px;
	border: 2px solid #990000;
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
.multiply
{
	height: 100px;
	width: 450px;
}
.multiply option
{
	padding: 2px;
	color: #000000;
	font-size: 12px;
	font-family: monospace;
}
.createMenu
{
	margin: 0px;
	padding: 0px;
	list-style-type: none;
}
.createMenu li
{
	padding: 3px 3px 2px 3px;
	margin-bottom: 8px;
	border: #333333 1px solid;
	opacity: 0.75;
	filter: alpha(opacity=75);
	-moz-opacity: 0.75;
	background-color: #EEEEEE;
	width: 34px;
	height: 34px;
	overflow: hidden;
}
.createMenu li:hover
{
	cursor: pointer;
	opacity: 1;
	filter: none;
	-moz-opacity: 1;
	background-color: #B8E0DC;
	border: #36817C 1px solid;
}
.createMenu img
{
	border: none;
}
.buttonEnabled
{
	color: #330000;
	border-color: #330000;
}
.buttonDisabled
{
	color: #CCCCCC; 
	border-color: #CCCCCC;
}
</style>
<script language="javascript" type="text/javascript">
var ajax = <?= XOAD_Client::register(new Ajax) ?>;

var unixName = '<?= $itemId ?>';

function showUseLdap ()
{
	var div = document.getElementById ('useLDAP');

	if (div.style.display == '')
		div.style.display = 'none';
	else
	{
		document.getElementById ('useLDAP').style.display = 'none';
		
		div.style.display = '';
	}
}
function showCreateUser ()
{
	var div = document.getElementById ('createUser');

	if (div.style.display == '')
		div.style.display = 'none';
	else
	{
		document.getElementById ('createUser').style.display = 'none';
		
		div.style.display = '';
	}
}
function configureUser()
{  
	if (!confirm ("Antes de prosseguir certifique-se de que já fez TODAS as configurações necessárias.\nApós criado, os arquivos XML do novo tipo somente poderão ser editados manualmente."))
		return false;
	
	showWait ();
	
	var alerts = '';

	document.getElementById("editUser").style.display = 'none';
  
	try
	{	   
		var field = document.getElementById('user_name_unix');

		if (field.value == '') throw 'O campo nome não pode ficar vazio';
		
		if (document.getElementById('user_title').value == '') throw 'O campo titulo não pode ficar vazio';

		if (field.value.replace(/ /g, '') == '')
			throw 'Você deve inserir um Nome Unix para sua instância.';

		var name = ajax.nameValidate (field.value);

		field.value = name;
		
		var form = xoad.html.exportForm ('form_createUser');
		
		if (!ajax.copyUser(form))
			throw 'Impossível criar usuário!';
		    
		alerts += makeAlert ('SUCCESS', 'Arquivos de usuário criados com sucesso!');
		
		if (!ajax.saveSecurity (form))
			throw 'Impossível salvar alterações do usuário no arquivo security.xml';

		alerts += makeAlert ('SUCCESS', 'Novo usuário atribuído à configuração da instância com sucesso!');
		
		if (!ajax.saveBusiness ())
			throw 'Impossível salvar alterações do usuário no arquivo business.xml';
		  
		if (document.getElementById('user_ldap').checked)
		{
		     if ((document.getElementById('ldap_id').value == '') || (document.getElementById('ldap_host').value == '') || (document.getElementById('ldap_user').value == '') || (document.getElementById('ldap_password').value == '') || (document.getElementById('ldap_dn').value == '') || (document.getElementById('ldap_ou').value == '') || (document.getElementById('ldap_gid').value == ''))
			 	throw 'Verifique campos de LDAP não preenchidos.';
		     
			 var formLdap = xoad.html.exportForm ('form_useLDAP');
			 
		     if (!ajax.saveLDAP (formLdap))
			 	throw 'Impossível salvar alterações do usuário no arquivo ldap.xml';
		     
			 alerts += makeAlert ('SUCCESS', 'Arquivo de ldap salvo com sucesso!');
		}		  
	     
		var formOptions = xoad.html.exportForm ('form_options');
		  
		if (!ajax.saveUserFiles (formOptions, name))
			throw 'Impossível salvar alterações do usuário nos arquivos da seção.';    
		
		alerts += makeAlert ('SUCCESS', 'Usuário [' + name + '] criado com sucesso!');

		document.getElementById("createUser").style.display = 'none';
		
		var selectbox = document.getElementById("remove_user");
	
		var description = document.getElementById("user_title").value;

		var optn = document.createElement("OPTION");
		      
		var spaces = 59 - ((name+description).length);
		
		var string = '';
		
		for (i = 0; i < spaces; i++)
			string = string + '.';

		optn.text = description + string + '[' + name + ']';
		
		optn.value = name;

		selectbox.options.add (optn);
	}
	catch (error)
	{
		alerts += makeAlert ('FAIL', error);

		ajax.showMessages ();
	}

	showArchError (alerts);
	
	ajax.delay (function () { hideWait (); });
}
function closeEditUser ()
{
	document.getElementById ("editUser").style.display = 'none';
}
function showEditUser ()
{
	var combo = document.getElementById ('remove_user');

	var index = combo.selectedIndex;

	if (index < 0)
	{
		alert ('Selecione um pacote para editar!');
	}
	else
	{
		var name = combo.options[index].value;
		
		if (!ajax.loadUser(name))
			throw 'Impossível carregar dados do usuário.';
		
		document.getElementById("ed_user_caption").innerHTML = name;
		
		document.getElementById("createUser").style.display = 'none';
		
		document.getElementById("editUser").style.display = '';
	}    
}
function editUser()
{
	var form = xoad.html.exportForm ('form_edit_user');
	      
	showWait ();

	var alerts = '';

	try
	{
		if (!ajax.editSecurity(form))
			throw 'Impossível salvar as mudanças';
		
		alerts += makeAlert ('SUCCESS', 'Edições salvas com sucesso!');
		
		var description = document.getElementById("ed_user_label").value;
		var user = document.getElementById("ed_user_name").value;
 
		var selectbox = document.getElementById("remove_user");
	
		var index = selectbox.selectedIndex;
 
		selectbox.remove(index);
		
		var optn = document.createElement("OPTION");
		      
		var spaces = 59 - ((user+description).length);
		
		var string = '';
		for (i = 0; i < spaces; i++) {
		     string = string+'.';
		}

		optn.text = description + string + '[' + user + ']';
		
		optn.value = user;
		
		selectbox.options.add (optn);
	}
	catch (error)
	{
		alerts += makeAlert ('FAIL', error);
	}

	ajax.showMessages ();

	showArchError (alerts);

	ajax.delay (function () { hideWait (); });
}
function deleteUser() {

	var combo = document.getElementById ('remove_user');

	var index = combo.selectedIndex;

	if (index < 0)
		alert ('Selecione um pacote para excluir!');

	showWait ();

	var name = combo.options[index].value;
	
	var alerts = '';

	try 
	{
		if (!ajax.deleteUser (name))
			throw 'Impossível excluir tipo de usuário';
			  
		if (!ajax.saveSecurity ())
			throw 'Impossível salvar alterações do usuário no arquivo security.xml';
			 
		combo.remove (index);
			  
		alerts += makeAlert ('SUCCESS', 'Usuário removido da configuração da instância com sucesso!');
	}
	catch (error)
	{
		alerts += makeAlert ('FAIL', error);
	}

	ajax.showMessages ();

	showArchError (alerts);

	ajax.delay (function () { hideWait (); });
	      
}
function enableCheckBox (val)
{
	var columns = new Array('_name', '_login', '_email', 'photo', 'birth_date', 'phone', 'mobile', 'cpf', 'url', 'street', 'number', 'quarter', 'complement', 'cep', 'state', 'city', 'msn', 'skype');
	
	var x;
	
	if (val == 'private')
		for (x in columns)
		{
			document.getElementById(columns[x] + '_reg').disabled = true;
			document.getElementById(columns[x] + '_reg').checked = false;
		}
	else
		for (x in columns)
			document.getElementById(columns[x] + '_reg').disabled = false;
}
function checkBox (field)
{
	var bool = field.checked;
	
	var val = field.id;
	
	if (bool)
	{
		document.getElementById(val + '_reg').checked = true; 
		document.getElementById(val + '_cri').checked = true;	 
	} 
	else 
	{ 
		document.getElementById(val + '_reg').checked = false; 
		document.getElementById(val + '_cri').checked = false;	 
	}
}
function showNotes ()
{
	showWait ();
	
	var source = '<table width="100%" border="0" cellpadding="0" cellspacing="0">\
		<tr>\
			<td style="text-align: left; background-color: #575556; padding: 5px; border-bottom: #36817C 3px solid; margin-bottom: 5px; font-weight: bold; color: #FFFFFF;">\
				Editar security\
			</td>\
		</tr>\
				[<a href="#" onclick="JavaScript: modalMsg.close ();">Fechar</a>]\
		<tr>\
			<td>\
				<td style="border-width: 0px; width: 380px; height: 280px; overflow: auto;"><b>Esta opção permite ao usuário editar o arquivo de gerência de usuários. (Security.XML)</b></td>\
			</td>\
		</tr>\
	</table>';

	modalMsg = new DHTML_modalMessage ();
	modalMsg.setHtmlContent (source);
	modalMsg.setSize (400, 350);
	modalMsg.display ();

	ajax.delay (function () { hideWait ();  });
}
function closeCreateUser ()
{
	document.getElementById('createUser').style.display = 'none';
}
</script>
<div id="errorBanner" style="display: none;"></div>
<div id="idMenuArchitect">
	<? swf (Business::singleton ()->getSection (Section::TCURRENT)->getComponentPath () .'_image/menu.swf', 557, 65) ?>
</div>
<div id="idForm">
	<fieldset id="createUser" style="display: none; padding: 5px; margin: 20px auto; border: 2px solid #000099;">
		<div>
			<form id="form_createUser" method="post" style="border: 1px #F4F4F4;">
				<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
					<tr id="row_user_name_unix" height="24px" style="background-color: #F4F4F4;">
						<td width="408" nowrap style="text-align: right;"><b>Nome Unix:</b></td>
						<td width="497">
							<div align="left">
								<input type="text" class="fieldNoEdit" id="user_name_unix" onblur="JavaScript: if (this.value != '') { document.getElementById('group_options').style.display = ''; document.getElementById('ldap_id').value = this.value; }" maxlength="256" />
							</div>
						</td>
						<td width="65" style="vertical-align: top;">&nbsp;</td>
					</tr>
					<tr height="2px"><td></td></tr>
					<tr id="row_user_title" height="24px" style="background-color: #FFFFFF;">
						<td width="408" nowrap style="text-align: right;"><b>Título:</b></td>
						<td>
							<div align="left">
								<input type="text" class="fieldNoEdit" id="user_title" maxlength="256" />
							</div>
						</td>
						<td width="65" style="vertical-align: top;">&nbsp;</td>
					</tr>
					<tr height="2px"><td></td></tr>
					<tr id="row_user_description" height="24px" style="background-color: #F4F4F4;">
						<td width="408" nowrap style="text-align: right;"><b>Descrição:</b></td>
						<td>
							<div align="left">
								<input type="text" class="fieldNoEdit" id="user_description" maxlength="512" />
							</div>
						</td>
						<td width="65" style="vertical-align: top;">&nbsp;</td>
					</tr>
					<tr height="2px"><td></td></tr>
					<tr id="row_user_type" height="24px" style="background-color: #FFFFFF;">
						<td width="408" nowrap style="text-align: right;"><b>Tipo:</b></td>
						<td>
							<div align="left">
								<select name="user_type" class="fieldNoEdit" id="user_type" onblur="JavaScript: enableCheckBox (this.value);" >
								<option value="private">Privado</option>				  
								<option value="protected">Protegido</option>								
								<option value="public">Público</option>
								</select>
							</div>
						</td>
						<td width="65" style="vertical-align: top;">&nbsp;</td>
					</tr>
					<tr height="2px"><td></td></tr>
					<tr id="row_user_ldap" height="24px" style="background-color: #F4F4F4;">
						<td width="408" nowrap style="text-align: right;"><b>Usar LDAP:</b></td>
						<td>
							<div align="left">
								<input type="checkbox" id="user_ldap" name="user_ldap" onclick="JavaScript: showUseLdap ();" />
							</div>
						</td>
						<td width="65" style="vertical-align: top;">&nbsp;</td>
					</tr>
				</table>
			</form>
		</div>
		<fieldset style="border: 1px dashed #009900; display: none;" id="useLDAP" class="formGroup">
			<div>
				<form id="form_useLDAP" method="post">
					<table align="left" border="0" width="100%" cellpadding="2" cellspacing="0">
						<tr id="row_ldap_id" height="24px" style="background-color: #FFFFFF;">
							<td width="421" nowrap style="text-align: right;"><b>ID:</b></td>
							<td width="515"><input type="text" class="fieldNoEdit" id="ldap_id" maxlength="256" /></td>
							<td width="32" style="vertical-align: top;">&nbsp;</td>
						</tr>
						<tr height="2px"><td></td></tr>
						<tr id="row_ldap_host" height="24px" style="background-color: #F4F4F4;">
							<td width="421" nowrap style="text-align: right;"><b>Host:</b></td>
							<td><input type="text" class="fieldNoEdit" id="ldap_host" maxlength="256" /></td>
							<td width="32" style="vertical-align: top;">&nbsp;</td>
						</tr>
						<tr height="2px"><td></td></tr>
						<tr id="row_ldap_user" height="24px" style="background-color: #FFFFFF;">
							<td width="421" nowrap style="text-align: right;"><b>Usuário:</b></td>
							<td><input type="text" class="fieldNoEdit" id="ldap_user" maxlength="256" /></td>
							<td width="32" style="vertical-align: top;">&nbsp;</td>
						</tr>
						<tr height="2px"><td></td></tr>
						<tr id="row_ldap_password" height="24px" style="background-color: #F4F4F4;">
							<td width="421" nowrap style="text-align: right;"><b>Senha:</b></td>
							<td><input type="text" class="fieldNoEdit" id="ldap_password" maxlength="256" /></td>
							<td width="32" style="vertical-align: top;">&nbsp;</td>
						</tr>
						<tr height="2px"><td></td></tr>
						<tr id="row_ldap_dn" height="24px" style="background-color: #FFFFFF;">
							<td width="421" nowrap style="text-align: right;"><b>DN:</b></td>
							<td><input type="text" class="fieldNoEdit" id="ldap_dn" maxlength="256" /></td>
							<td width="32" style="vertical-align: top;">&nbsp;</td>
						</tr>
						<tr height="2px"><td></td></tr>
						<tr id="row_ldap_ou" height="24px" style="background-color: #F4F4F4;">
							<td width="421" nowrap style="text-align: right;"><b>OU:</b></td>
							<td><input type="text" class="fieldNoEdit" id="ldap_ou" maxlength="256" /></td>
							<td width="32" style="vertical-align: top;">&nbsp;</td>
						</tr>
						<tr height="2px"><td></td></tr>
						<tr id="row_ldap_gid" height="24px" style="background-color: #FFFFFF;">
							<td width="421" nowrap style="text-align: right;"><b>GID:</b></td>
							<td><input type="text" class="fieldNoEdit" id="ldap_gid" maxlength="256" /></td>
							<td width="32" style="vertical-align: top;">&nbsp;</td>
						</tr>
						<tr height="2px"><td></td></tr>
						<tr id="row_ldap_update" height="24px" style="background-color: #F4F4F4;">
							<td width="421" nowrap style="text-align: right;"><b>Update:</b></td>
							<td>
								<select class="fieldNoEdit" id="ldap_update" maxlength="256">
									<option value="true">Sim</option>
									<option value="false">Não</option>
								</select>
							</td>
							<td width="32" style="vertical-align: top;">&nbsp;</td>
						</tr>
					</table>
				</form>
			</div>
		</fieldset>
		<fieldset style="display: none" id="group_options" class="formGroup">
			<legend onclick="JavaScript: showGroup ('options'); return false;">
				Configurações de Campos
			</legend>
			<div>
				<form id="form_options" method="post">
					<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
						<!-- <tr><td><? //= print_r ($fieldArray) ?></td></tr> -->
						<tr id="row_title" height="24px" style="background-color: #F4F4F4;">
							<td>
								<table align="center" border="0" width="97%" cellpadding="2" cellspacing="0">
									<tr height="24px">
										<td style="background-color: #CCC; font-weight: bold; color: #FFF;" colspan="2">Rótulo do Campo</td>
										<td style="background-color: #9AA; font-weight: bold; color: #FFF;" colspan="3">Dados Pessoais</td>
										<td style="background-color: #A7A; font-weight: bold; color: #FFF;" colspan="5">Gerência</td>
										<td style="background-color: #AA7; font-weight: bold; color: #FFF;" colspan="4">Propriedades</td>
									</tr>
									<?
									$color = '#F4F4F4';
									
									foreach ($fieldArray as $trash => $field)
									{
										$color = $color != '#FFF' ? '#FFF' : '#F4F4F4';

										if (array_key_exists ($field ['column'], $defaultArray))
											$dFiles = $defaultArray [$field ['column']];
										else
											$dFiles = array ();
										?>
										<tr id="row_<?= $field ['column'] ?>" height="24px" style="background-color: <?= $color ?>;">
											<td><i><?= $field ['column'] ?></i></td>
											<td><input type="text" value="<?= $field ['label'] ?>" class="fieldNoEdit" id="label_<?= $field ['column'] ?>" /></td>
											<td><input type="checkbox" id="register_<?= $field ['column'] ?>" <?= array_key_exists ('register', $dFiles) && (int) $dFiles ['register'] ? 'disabled="disabled" checked="checked"' : '' ?> />Registro</td>
											<td><input type="checkbox" id="modify_<?= $field ['column'] ?>" <?= array_key_exists ('modify', $dFiles) && (int) $dFiles ['modify'] ? 'disabled="disabled" checked="checked"' : '' ?> />Modificar</td>
											<td><input type="checkbox" id="profile_<?= $field ['column'] ?>" <?= array_key_exists ('profile', $dFiles) && (int) $dFiles ['profile'] ? 'disabled="disabled" checked="checked"' : '' ?> />Perfil</td>
											<td><input type="checkbox" id="create_<?= $field ['column'] ?>" <?= array_key_exists ('create', $dFiles) && (int) $dFiles ['create'] ? 'disabled="disabled" checked="checked"' : '' ?> />Criar</td>
											<td><input type="checkbox" id="edit_<?= $field ['column'] ?>" <?= array_key_exists ('edit', $dFiles) && (int) $dFiles ['edit'] ? 'disabled="disabled" checked="checked"' : '' ?> />Editar</td>
											<td><input type="checkbox" id="list_<?= $field ['column'] ?>" <?= array_key_exists ('list', $dFiles) && (int) $dFiles ['list'] ? 'disabled="disabled" checked="checked"' : '' ?> />Listar</td>
											<td><input type="checkbox" id="search_<?= $field ['column'] ?>" <?= array_key_exists ('search', $dFiles) && (int) $dFiles ['search'] ? 'disabled="disabled" checked="checked"' : '' ?> />Pesquisar</td>
											<td><input type="checkbox" id="view_<?= $field ['column'] ?>" <?= array_key_exists ('view', $dFiles) && (int) $dFiles ['view'] ? 'disabled="disabled" checked="checked"' : '' ?> />Visualizar</td>
											<td><input type="checkbox" id="unique_<?= $field ['column'] ?>" <?= array_key_exists ('unique', $field) && trim ($field ['unique']) == 'true' ? 'disabled="disabled" checked="checked"' : '' ?> />Único</td>
											<td><input type="checkbox" id="required_<?= $field ['column'] ?>" <?= array_key_exists ('required', $field) && trim ($field ['required']) == 'true' ? 'disabled="disabled" checked="checked"' : '' ?> />Obrigatório</td>
											<td><input type="checkbox" onchange="JavaScript: if (this.checked == false) { $('ldap_<?= $field ['column'] ?>').value = ''; return false; }; var value1 = prompt('Digite o nome do campo correspondente no servidor LDAP:',$('ldap_<?= $field ['column'] ?>').value); if (!value1) { this.checked = false; $('ldap_<?= $field ['column'] ?>').value = ''; } else { $('ldap_<?= $field ['column'] ?>').value = value1; };" <?= array_key_exists ('on-ldap-as', $field) && trim ($field ['on-ldap-as']) != '' ? 'checked="checked"' : '' ?> /><input type="hidden" id="ldap_<?= $field ['column'] ?>" value="<?= array_key_exists ('on-ldap-as', $field) ? $field ['on-ldap-as'] : '' ?>" />LDAP</td>
											<td><input type="checkbox" onchange="JavaScript: if (this.checked == false) { $('help_<?= $field ['column'] ?>').value = ''; return false; }; var value2 = prompt('Digite o texto de ajuda:',$('help_<?= $field ['column'] ?>').value); if (!value2) { this.checked = false; $('help_<?= $field ['column'] ?>').value = ''; } else { $('help_<?= $field ['column'] ?>').value = value2; };" <?= array_key_exists ('help', $field) && trim ($field ['help']) != '' ? 'checked="checked"' : '' ?> /><input type="hidden" id="help_<?= $field ['column'] ?>" value="<?= array_key_exists ('help', $field) ? $field ['help'] : '' ?>" />Help</td>
										</tr>
										<?
									}
									?>
								</table>
							</td>
						</tr>
						<tr>
							<td style="text-align: center;">
								<style type="text/css">
								.advanceButton
								{
									border: #009900 1px solid;
									margin: 10px auto 0px;
									width: 250px;
									height: 56px;
									vertical-align: middle;
									background-color: #FFF;
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
								<div id="advanceWizard" class="advanceButton" onclick="JavaScript: configureUser ();">
									<label>Criar novo Tipo de Usuário</label>
									<img src="titan.php?target=loadFile&file=interface/image/arrow.green.png" border="0" />
								</div>
							</td>
						</tr>
					</table>
				</form>
			</div>
		</fieldset>
	</fieldset>
</div>
<div id="editUser" style="display: none;">
	<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
		<tr>
			<td>
				<form id="form_edit_user" method="post">
					<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
						<tr height="24px" style="background-color: #FFFFFF;">
							<td width="20%" nowrap style="text-align: right;"><b>Nome Unix:</b></td>
							<td><label id="ed_user_caption"></label></td>
							<td><input type="hidden" class="fieldNoEdit" name="name" id="ed_user_name"></input></td>
						</tr>
						<tr height="2px"><td></td></tr>
						<tr height="24px" style="background-color: #F4F4F4;">
							<td width="20%" nowrap style="text-align: right;"><b>Título:</b></td>
							<td><input type="text" class="fieldNoEdit" name="label" id="ed_user_label" value=""  maxlength="256" /></td>
							<td width="20px" style="vertical-align: top;">&nbsp;</td>
						</tr>
						<tr height="2px"><td></td></tr>
						<tr height="24px" style="background-color: #FFFFFF;">
							<td width="20%" nowrap style="text-align: right;"><b>Descrição:</b></td>
							<td><input type="text" class="fieldNoEdit" name="description" id="ed_user_description" value=""  maxlength="256" /></td>
							<td width="20px" style="vertical-align: top;">&nbsp;</td>
						</tr>
						<tr height="2px"><td></td></tr>
						<tr height="24px" style="background-color: #F4F4F4;">
							<td width="20%" nowrap style="text-align: right;"><b>Tipo:</b></td>
							<td><input type="text" class="fieldNoEdit" name="type" id="ed_user_type" value=""  maxlength="256" /></td>
							<td width="20px" style="vertical-align: top;">&nbsp;</td>
						</tr>					
					</table>
				</form>
			</td>
			<td style="vertical-align: top;">
				<ul class="createMenu">
					<li onclick="JavaScript: editUser(); closeEditUser();"><img src="titan.php?target=loadFile&file=interface/menu/save.png" title="Salvar Alterações" /></li>
					<li onclick="JavaScript: showNotes();"><img src="titan.php?target=loadFile&file=interface/menu/notes.png" title="Notas" /></li>					
					<li onclick="JavaScript: closeEditUser();"><img src="titan.php?target=loadFile&file=interface/menu/close.png" title="Fechar" /></li>
				</ul>
			</td>
		</tr>
	</table>
</div>
<div id="existingUsers">
	<span style="font-weight: bold; color: #900;">Tipos de usuários da instância já criados:</span>
	<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
		<tr>
			<td>
				<select class="field multiply" style="height: 100px;" name="remove_user" id="remove_user" multiple="multiple">
					<?
					ksort ($userArray);

					foreach ($userArray as $trash => $package)
					{
						$spaces = 61 - (strlen ($package ['name']) + 2);
						
						echo '<option value="'. $package ['name'] .'">'. str_pad ($package ['label'], $spaces, '.') .'['. $package ['name'] .']</option>';
					}
					?>
				</select>
			</td>
			<td style="vertical-align: top;">
				<ul class="createMenu">
					<li onclick="JavaScript: showEditUser();"><img src="titan.php?target=loadFile&file=interface/menu/edit.png" title="Editar Usuário" /></li>
					<li onclick="JavaScript: deleteUser();"><img src="titan.php?target=loadFile&file=interface/menu/delete.png" title="Remover Usuário" /></li>
				</ul>
			</td>
		</tr>
	</table>
</div>