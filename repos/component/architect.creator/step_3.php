<?php
$output ['list'] = '';
$output ['js'] = array ();

foreach ($array as $key => $element)
{
	$output ['list'] .= '<li id="item_'. $element ['name'] .'" ondblclick="JavaScript: showEditSection (\''. $element ['name'] .'\');">'. $element ['label'] .'</li>';

	$output ['js'][] = $element ['name'];

	$output ['father'][$element ['name']] = $element ['label'];
}
?>
<style type="text/css">
.fieldEdit
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	color: #330000;
	border: #330000 1px solid;
	width: 350px;
	padding: 3px;
	background: none;
}
.fieldNoEdit
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	color: #555555;
	border: #330000 0px solid;
	width: 350px;
	padding: 3px;
	background: url(titan.php?target=loadFile&file=interface/icon/editable.gif) right no-repeat;
}
.fieldNoEdit:hover
{
	border-width: 1px;
	background: none;
	color: #330000;
}
#editSection
{
	padding: 5px;
	margin: 20px auto;
	width: 500px;
	border: 2px solid #000099;
}
#createSection
{
	padding: 5px;
	margin: 20px auto;
	width: 500px;
	border: 2px solid #009900;
}
#sortableList {
	list-style-type: none;
	padding: 3px 5px 3px 5px;
	margin: 0 auto;
	width: 500px;
	border: 2px solid #DDDDDD;
}
#sortableList li {
	padding: 5px 15px 5px 5px;
	margin: 3px 0px 3px 0px;
	border: 1px solid #AAAAAA;
	background-color: #F4F4F4;
	font-weight: bold;
	text-align: justify;
	background: url(titan.php?target=loadFile&file=interface/icon/sort.gif) right no-repeat;
}
#sortableList li:hover
{
	cursor: move;
	background-color: #EBCCCC;
	border-color: #990000;
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
.multiply
{
	height: 150px;
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
	opacity: .75;
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
</style>
<script language="javascript" type="text/javascript" src="titan.php?target=js&files=prototype"></script>
<script language="javascript" type="text/javascript" src="titan.php?target=loadFile&file=js/scriptaculous.js"></script>
<script language="javascript" type="text/javascript">
var ajax = <?= XOAD_Client::register(new Ajax) ?>;

var unixName = '<?= $itemId ?>';

var sections = new Array ();

function editField (field)
{
	document.getElementById ('section_' + field).className = 'fieldEdit';

	document.getElementById ('editMenuSave').onclick = function () { saveSection () };
}

function showEditSection (name)
{
	showWait ();

	document.getElementById ('createSection').style.display = 'none';

	document.getElementById ('editSection').style.display = 'none';

	ajax.loadSection (name, function () {
		document.getElementById ('label_section_name').innerHTML = name;

		document.getElementById ('editMenuDelete').onclick = function () { deleteSection (name) };

		document.getElementById ('editMenuClose').onclick = function () { clearSection () };

		document.getElementById ('editSection').style.display = '';
	});

	ajax.delay (function () { hideWait (); });
}

function saveSection ()
{
	showWait ();

	var alerts = '';

	try
	{
		var form = xoad.html.exportForm ('form_edit_section');

		if (!ajax.saveSection (form))
			throw 'Não foi possível salvar as alterações!';

		if (!ajax.saveBusiness ())
			throw 'Impossível salvar alterações da seção na configuração da instância!';

		alerts += makeAlert ('SUCCESS', 'As alterações na seção foram salvas com sucesso!');

		document.getElementById ('item_' + form ['name']).innerHTML = form ['label'];

		aux = new Array ('label', 'description', 'father');

		for (i = 0 ; i < aux.length ; i++)
			document.getElementById ('section_' + aux [i]).className = 'fieldNoEdit';

		document.getElementById ('editMenuSave').onclick = function () {};
	}
	catch (error)
	{
		alerts += makeAlert ('FAIL', error);

		ajax.showMessages ();
	}

	showArchError (alerts);

	ajax.delay (function () { hideWait (); });
}

function clearSection ()
{
	document.getElementById ('editSection').style.display = 'none';

	aux = new Array ('label', 'description', 'father');

	for (i = 0 ; i < aux.length ; i++)
	{
		document.getElementById ('section_' + aux [i]).className = 'fieldNoEdit';
		document.getElementById ('section_' + aux [i]).value = '';
	}

	document.getElementById ('section_name').value = '';

	document.getElementById ('label_section_name').innerHTML = '';

	document.getElementById ('editMenuSave').onclick = function () {};

	document.getElementById ('editMenuDelete').onclick = function () {};

	document.getElementById ('editMenuClose').onclick = function () {};
}

function configureSection ()
{
	var combo = document.getElementById ('new_section');

	var index = combo.selectedIndex;

	if (index < 0)
	{
		alert ('Selecione um pacote para incluir a nova seção na sua instância!');

		return false;
	}

	showWait ();

	var name = combo.options[index].value;

	var source = '<table width="100%" border="0" cellpadding="0" cellspacing="0">\
		<tr>\
			<td style="text-align: left; background-color: #575556; padding: 5px; border-bottom: #36817C 3px solid; margin-bottom: 5px; font-weight: bold; color: #FFFFFF;">\
				Configuração da Seção\
			</td>\
		</tr>\
		<tr>\
			<td>\
				<iframe src="<?= Instance::singleton ()->getUrl () ?>titan.php?target=script&toSection=<?= $section->getName () ?>&file=configure&name=' + name + '&auth=1" style="border-width: 0px; width: 398px; height: 323px; overflow: auto;"></iframe>\
			</td>\
		</tr>\
	</table>';

	modalMsg = new DHTML_modalMessage ();
	modalMsg.setHtmlContent (source);
	modalMsg.setSize (400, 350);
	modalMsg.display ();

	ajax.delay (function () { hideWait (); });
}

function createSection (package)
{
	modalMsg.close ();

	showWait ();

	var alerts = '';

	try
	{
		var copy = ajax.copySection (package);

		if (!copy)
			throw 'Impossível copiar os arquivos do pacote!';

		alerts += makeAlert ('SUCCESS', copy + ' arquivos do pacote foram copiados com sucesso!');

		if (ajax.makeSectionDB (package))
			alerts += makeAlert ('SUCCESS', 'As alterações no Banco de Dados para a nova instância foram realizadas com sucesso!');
		else
			alerts += makeAlert ('WARNING', 'Não foi possível efetuar as alterações no Banco de Dados. Talvez as tabelas da seção já existam no BD ou existe algum problema na conexão com o Banco de Dados da instância!');

		if (!ajax.saveBusiness ())
			throw 'Impossível salvar nova seção na configuração da instância!';

		alerts += makeAlert ('SUCCESS', 'A nova seção foi atribuída à configuração da instância com sucesso!');

		var list = document.getElementById ('sortableList');

		eval ("var arrayNames = new Array (" + ajax.getNewSections (package, 'NAME') + ");");

		eval ("var arrayLabels = new Array (" + ajax.getNewSections (package, 'LABEL') + ");");

		var li, name;

		for (var i = 0 ; i < arrayNames.length ; i++)
		{
			name = arrayNames [i];

			li = document.createElement('li');

			li.id = 'item_' + name;

			li.innerHTML = arrayLabels [i]

			setEvent (li, name);

			list.appendChild (li);
		}

		Sortable.create ('sortableList',{tag:'li'});
	}
	catch (error)
	{
		alerts += makeAlert ('FAIL', error);
	}

	ajax.showMessages ();

	showArchError (alerts);

	ajax.delay (function () { hideWait (); });
}

function setEvent (obj, name)
{
	obj.ondblclick = function () { showEditSection (name); };
}

function deleteSection (name)
{
	showWait ();

	var alerts = '';

	try
	{
		if (!ajax.deleteSection (name))
			throw 'Impossível apagar arquivos da seção!';

		alerts += makeAlert ('SUCCESS', 'Arquivos da seção apagados com sucesso com sucesso!');

		if (!ajax.saveBusiness ())
			throw 'Impossível remover seção da configuração da instância! Talvez você tenha que editar o arquivo [configure/business.xml] manualmente para remover a seção.';

		alerts += makeAlert ('SUCCESS', 'A seção foi removida da configuração da instância com sucesso!');

		var list = document.getElementById ('sortableList');

		list.removeChild (document.getElementById ('item_' + name));

		Sortable.create ('sortableList',{tag:'li'});

		clearSection ();
	}
	catch (error)
	{
		alerts += makeAlert ('FAIL', error);
	}

	ajax.showMessages ();

	showArchError (alerts);

	ajax.delay (function () { hideWait (); });
}

function showCreateSection ()
{
	var div = document.getElementById ('createSection');

	if (div.style.display == '')
		div.style.display = 'none';
	else
	{
		document.getElementById ('editSection').style.display = 'none';

		div.style.display = '';
	}
}

function viewNotes ()
{
	var combo = document.getElementById ('new_section');

	var index = combo.selectedIndex;

	if (index < 0)
	{
		alert ('Selecione um pacote para ver as notas de versão!');

		return false;
	}

	showWait ();

	var name = combo.options[index].value;

	var notes = ajax.loadNotes (name);

	var source = '<table border="0" cellpadding="0" cellspacing="0">\
		<tr>\
			<td style="text-align: left; background-color: #E4E4E4; padding: 3px; margin-bottom: 5px; font-weight: bold;">\
				Notas de Versão\
			</td>\
			<td style="text-align: right; background-color: #E4E4E4; padding: 3px; margin-bottom: 5px;">\
				<a href="#" onclick="JavaScript: modalMsg.close ();">Fechar</a>\
			</td>\
		</tr>\
		<tr>\
			<td colspan="2"><div style="overflow: scroll; height: 323px; padding: 5px;">' + notes + '</div></td>\
		</tr>\
	</table>';

	modalMsg = new DHTML_modalMessage ();
	modalMsg.setHtmlContent (source);
	modalMsg.setSize (500, 350);
	modalMsg.display ();

	ajax.delay (function () { hideWait (); });
}

function saveSort ()
{
	showWait ();

	alerts = '';

	try
	{
		if (!ajax.saveSort (Sortable.serialize('sortableList')))
			throw 'Impossível salvar nova ordenação!';

		alerts += makeAlert ('SUCCESS', 'Nova ordenação salva com sucesso!');
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
<div id="errorBanner" style="display: none; z-index: 3;"></div>
<div id="idMenuArchitect">
	<?php swf (Business::singleton ()->getSection (Section::TCURRENT)->getComponentPath () .'_image/menu.swf', 557, 65) ?>
</div>
<div id="createSection" style="display: none;">
	<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
		<tr>
			<td>
				<select class="field multiply" style="height: 150px;" name="new_section" id="new_section" multiple="multiple">
					<?php
					ksort ($packs);

					foreach ($packs as $trash => $package)
					{
						$spaces = 61 - (strlen ($package ['name']) + 2);

						echo '<option value="'. $package ['name'] .'">'. str_pad ($package ['label'], $spaces, '.') .'['. $package ['name'] .']</option>';
					}
					?>
				</select>
			</td>
			<td style="vertical-align: top;">
				<ul class="createMenu">
					<li onclick="JavaScript: configureSection ();"><img src="titan.php?target=loadFile&file=interface/menu/create.png" title="Adicionar Seção" /></li>
					<li onclick="JavaScript: viewNotes ();"><img src="titan.php?target=loadFile&file=interface/menu/notes.png" title="Release Notes" /></li>
					<li onclick="JavaScript: document.getElementById ('createSection').style.display = 'none';"><img src="titan.php?target=loadFile&file=interface/menu/close.png" title="Fechar" /></li>
				</ul>
			</td>
		</tr>
	</table>
</div>
<div id="editSection" style="display: none;">
	<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
		<tr>
			<td>
				<form id="form_edit_section" method="post">
				<input type="hidden" name="name" id="section_name" value="" />
				<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
					<tr height="24px" style="background-color: #FFFFFF;">
						<td width="20%" nowrap style="text-align: right;"><b>Nome Unix:</b></td>
						<td><label id="label_section_name"></label></td>
						<td width="20px" style="vertical-align: top;">&nbsp;</td>
					</tr>
					<tr height="2px"><td></td></tr>
					<tr height="24px" style="background-color: #F4F4F4;">
						<td width="20%" nowrap style="text-align: right;"><b>Título:</b></td>
						<td><input type="text" class="fieldNoEdit" name="label" id="section_label" value=""  maxlength="256" onfocus="JavaScript: editField ('label');" /></td>
						<td width="20px" style="vertical-align: top;">&nbsp;</td>
					</tr>
					<tr height="2px"><td></td></tr>
					<tr height="24px" style="background-color: #FFFFFF;">
						<td width="20%" nowrap style="text-align: right;"><b>Descrição:</b></td>
						<td><input type="text" class="fieldNoEdit" name="description" id="section_description" value=""  maxlength="256" onfocus="JavaScript: editField ('description');" /></td>
						<td width="20px" style="vertical-align: top;">&nbsp;</td>
					</tr>
					<tr height="2px"><td></td></tr>
					<tr height="24px" style="background-color: #F4F4F4;">
						<td width="20%" nowrap style="text-align: right;"><b>Seção Pai:</b></td>
						<td><input type="text" class="fieldNoEdit" name="father" id="section_father" value=""  maxlength="256" onfocus="JavaScript: editField ('father');" /></td>
						<td width="20px" style="vertical-align: top;">&nbsp;</td>
					</tr>
				</table>
				</form>
			</td>
			<td style="vertical-align: top;">
				<ul class="createMenu">
					<li id="editMenuSave"><img src="titan.php?target=loadFile&file=interface/menu/save.png" title="Salvar Alterações" /></li>
					<li id="editMenuDelete"><img src="titan.php?target=loadFile&file=interface/menu/delete.png" title="Apagar Seção" /></li>
					<li id="editMenuClose"><img src="titan.php?target=loadFile&file=interface/menu/close.png" title="Fechar" /></li>
				</ul>
			</td>
		</tr>
	</table>
</div>
<div id="idForm">
	<ul id="sortableList">
		<?= $output ['list'] ?>
	</ul>
</div>
<script language="javascript" type="text/javascript">
Sortable.create ('sortableList',{tag:'li'});
</script>
