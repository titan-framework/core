<script language="javascript" type="text/javascript" src="titan.php?target=loadFile&file=js/multifile.js"></script>
<script language="javascript" type="text/javascript">
function showFile (id)
{
	var row = document.getElementById ('row_' + id);

	if (row.style.display == '')
		row.style.display = 'none';
	else
	{
		var img = document.getElementById ('image_' + id);

		img.src = 'titan.php?target=viewThumb&fileId=' + id + '&width=100&height=100';

		row.style.display = '';
	}
}
function editField (id, column)
{
	var field = document.getElementById('field_' + id + '_' + column);

	field.style.borderColor = '#330000';

	if (field.value == 'Sem descrição.')
		field.value = '';

	var button = document.getElementById('button_' + id + '_' + column);

	button.style.borderColor = '#330000';

	button.style.backgroundColor = '#FFFFFF';

	button.value = 'Salvar'
}
function noEditField (id, column)
{
	var field = document.getElementById ('field_' + id + '_' + column);

	field.style.borderColor = '#FFFFFF';

	if (field.value.replace(/^\s+/g, '').replace(/\s+$/g, '') == '')
		field.value = 'Sem descrição.'

	var button = document.getElementById('button_' + id + '_' + column);

	button.style.borderColor = '#FFFFFF';

	button.style.backgroundColor = '#FFFFFF';

	button.value = '';
}
function saveField (id, column)
{
	showWait ();

	var value = document.getElementById ('field_' + id + '_' + column).value;

	value = value.replace(/^\s+/g, '').replace(/\s+$/g, '');

	if (value == '')
		alert ('Atenção! É necessário inserir um nome válido para o arquivo!');
	else
		ajax.saveFieldFile (id, column, value);

	ajax.delay (function () { hideWait (); });
}
function saveFieldDesc (id, column)
{
	showWait ();

	var value = document.getElementById ('field_' + id + '_' + column).value;

	value = value.replace(/^\s+/g, '').replace(/\s+$/g, '');

	ajax.saveFieldFile (id, column, value);

	ajax.delay (function () { hideWait (); });
}

var delArray = new Array ();

function deleteFiles ()
{
	showWait ();

	modalMsg.close ();

	var success, obj;

	eval ('success = new Array (' + ajax.deleteFiles (delArray) + ');');

	obj = document.getElementById ('tableFiles');

	for (i = 0 ; i < success.length ; i++)
	{
		document.getElementById('row_' + success[i]).style.display = 'none';
		document.getElementById('row_' + success[i] + '_1').style.display = 'none';
		document.getElementById('row_' + success[i] + '_2').style.display = 'none';
		document.getElementById('row_' + success[i] + '_3').style.display = 'none';
	}

	ajax.delay (function () { hideWait (); });
}
function addDeleteFile (id)
{
	var emptyCell = -1;

	for (i = 0 ; i < delArray.length ; i++)
	{
		if (id == delArray [i])
		{
			delArray [i] = 0;
			return false;
		}
		else if (delArray [i] == 0)
			emptyCell = i;
	}

	if (emptyCell < 0)
		delArray [i] = id;
	else
		delArray [emptyCell] = id;

	return false;
}
function deleteMessage ()
{
	var source = '<table border="0">\
		<tr>\
			<td rowspan="2">\
				<img src="titan.php?target=loadFile&file=interface/image/warning.png" border="0" style="margin-right: 10px;" />\
			</td>\
			<td style="text-align: center; font-weight: bold;">\
				Tem certeza que deseja apagar os arquivos selecionados? <label style="color: #990000;">Esta a&ccedil;&atilde;o &eacute; irrevers&iacute;vel.</label>\
			</td>\
		</tr>\
		<tr>\
			<td style="text-align: center;">\
				<input type="button" class="button" value="Apagar Arquivos" onclick="JavaScript: deleteFiles ();">\
				<input type="button" class="button" value="Cancelar" onclick="JavaScript: modalMsg.close ();">\
			</td>\
		</tr>\
	</table>';

	modalMsg = new DHTML_modalMessage ();
	modalMsg.setHtmlContent (source);
	modalMsg.setSize (390, 85);
	modalMsg.display ();
}
</script>