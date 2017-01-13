<script language="javascript" type="text/javascript">
'global.Collection'.namespace ();

global.Collection.ajax = <?= class_exists ('xCollection', FALSE) ? XOAD_Client::register (new xCollection) : 'null' ?>;

global.Collection.create = function (fieldId, fatherId)
{
	if (!fatherId)
	{
		message ('Atenção! Você deve salvar o formulário antes de inserir itens neste campo.', 350, 120, true);

		return false;
	}

	$('collection_edit_' + fieldId).style.display = 'none';

	var edit = $('collection_create_' + fieldId);

	if (edit.style.display == '')
	{
		edit.style.display = 'none';

		$('collection_form_' + fieldId).reset ();
	}
	else
	{
		$('collection_label_' + fieldId).innerHTML = '<?= __ ('Add New Item') ?>';

		$('collection_id_' + fieldId).value = 0;

		edit.style.display = '';
	}
}

global.Collection.edit = function (fieldId, file, itemId, fatherColumn)
{
	$('collection_create_' + fieldId).style.display = 'none';

	$('collection_form_' + fieldId).reset ();

	var form = global.Collection.ajax.loadEditForm (file, itemId, fatherColumn, fieldId);

	var edit = $('collection_edit_' + fieldId);

	edit.innerHTML = form;

	edit.style.display = '';
}

global.Collection.save = function (fatherId, fatherColumn, fieldId, file)
{
	showWait ();

	var formData = xoad.html.exportForm ('collection_form_' + fieldId);

	var fields = new Array ();

	eval ("fields = new Array (" + tAjax.validate (file, formData, itemId) + ");");

	if (fields.length)
	{
		tAjax.showMessages ();

		hideWait ();

		return false;
	}

	var auxId = $('collection_id_' + fieldId).value;

	var itemId = global.Collection.ajax.save (file, formData, fatherId, fatherColumn, auxId);

	if (!itemId)
	{
		tAjax.delay (function () {
			global.Collection.ajax.showMessages ();

			hideWait ();
		});

		return false;
	}

	global.Collection.ajax.delay (function () {
		global.Collection.addRow (itemId, fieldId, file);

		global.Collection.ajax.showMessages ();

		$('collection_form_' + fieldId).reset ();

		hideWait ();
	});

	return false;
}

global.Collection.addRow = function (itemId, fieldId, file)
{
	var column, row = document.createElement ('tr');

	row.id = 'collection_row_' + itemId;

	row.className = 'cTableItem';

	var aux = global.Collection.ajax.addRow (itemId, file, fieldId);

	eval (aux);

	for (var i = 0 ; i < columns.length ; i++)
	{
		column = document.createElement('td');

		if (i == (columns.length - 1))
		{
			column.nowrap = 'nowrap';
			column.style.textAlign = 'right';
		}

		column.innerHTML = columns [i];

		row.appendChild (column);
	}

	$('collection_view_' + fieldId).appendChild (row);

	row = document.createElement ('tr');

	for (var i = 0 ; i < columns.length ; i++)
		row.appendChild (document.createElement('td'));

	row.className = 'cSeparator';

	row.id = 'collection_row_' + itemId + '_space';

	$('collection_view_' + fieldId).appendChild (row);
}

global.Collection.delRow = function (fieldId, file, itemId)
{
	if (!confirm ('Tem certeza que deseja apagar o item? Esta ação é irreversível.'))
		return false;

	showWait ();

	if (!(global.Collection.ajax.delRow (itemId, file), function () {
		global.Collection.ajax.showMessages ();

		hideWait ();
	}))
	{
		return false;
	}

	global.Collection.ajax.delay (function () {
		$('collection_row_' + itemId).style.display = 'none';
		$('collection_row_' + itemId + '_space').style.display = 'none';

		global.Collection.ajax.showMessages ();

		hideWait ();
	});

	return false;
}

global.Collection.up = function (icon, fieldId)
{
	var row = icon.ancestors() [1];

	var previous = row.previous (1);

	if (!previous)
		return;

	var separator = row.previous (0);

	previous.remove ();
	separator.remove ();

	row.insert ({ after: previous });
	row.insert ({ after: separator });
}

global.Collection.down = function (icon, fieldId)
{
	var row = icon.ancestors() [1];

	var next = row.next (1);

	if (!next)
		return;

	var separator = row.next (2);

	next.remove ();
	separator.remove ();

	row.insert ({ before: next });
	row.insert ({ before: separator });
}

global.Collection.saveSort = function (icon, file)
{
	showWait ();

	var table = icon.ancestors() [2];

	var hiddens = table.next ('tbody').select ('input[name="idForSort"]');

	var sort = [];

	for (var i = 0; i < hiddens.length; i++)
		sort [i] = hiddens [i].value;

	global.Collection.ajax.saveSort (file, sort, function ()
	{
		global.Collection.ajax.showMessages ();

		hideWait ();
	});
}
</script>
