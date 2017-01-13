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

	$('collectionLabelMessage_' + fieldId).innerHTML = '';

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

		edit.style.display = '';
	}
}

global.Collection.edit = function (fieldId, file, itemId, fatherColumn)
{
	$('collectionLabelMessage_' + fieldId).innerHTML = '';

	$('collection_create_' + fieldId).style.display = 'none';

	$('collection_form_create_' + fieldId).reset ();

	var form = global.Collection.ajax.loadEditForm (file, itemId, fatherColumn, fieldId);

	var edit = $('collection_edit_' + fieldId);

	edit.innerHTML = form;

	edit.style.display = '';
}

global.Collection.closeEdit = function (fieldId)
{
	$('collectionLabelMessage_' + fieldId).innerHTML = '';

	var edit = $('collection_edit_' + fieldId);

	edit.innerHTML = '';

	edit.style.display = 'none';
}

global.Collection.saveCreate = function (fatherId, fatherColumn, fieldId, file)
{
	$('collectionLabelMessage_' + fieldId).innerHTML = '';

	showWait ();

	var formData = xoad.html.exportForm ('collection_form_create_' + fieldId);

	var fields = new Array ();

	eval ("fields = new Array (" + tAjax.validate (file, formData, 0) + ");");

	if (fields.length)
	{
		global.Collection.ajax.showMessages (fieldId);

		hideWait ();

		return false;
	}

	var itemId = global.Collection.ajax.save (file, formData, fatherId, fatherColumn, 0);

	if (!itemId)
	{
		global.Collection.ajax.delay (function () {
			global.Collection.ajax.showMessages (fieldId);

			hideWait ();
		});

		return false;
	}

	global.Collection.ajax.delay (function () {
		global.Collection.addRow (itemId, fieldId, file);

		global.Collection.ajax.showMessages (fieldId);

		$('collection_form_create_' + fieldId).reset ();

		hideWait ();
	});

	return false;
}

global.Collection.saveEdit = function (itemId, fieldId, file)
{
	$('collectionLabelMessage_' + fieldId).innerHTML = '';

	showWait ();

	var formData = xoad.html.exportForm ('collection_form_edit_' + fieldId);

	var fields = new Array ();

	eval ("fields = new Array (" + tAjax.validate (file, formData, itemId) + ");");

	if (fields.length)
	{
		global.Collection.ajax.showMessages (fieldId);

		hideWait ();

		return false;
	}

	var itemId = global.Collection.ajax.save (file, formData, 0, '', itemId);

	if (!itemId)
	{
		global.Collection.ajax.delay (function () {
			global.Collection.ajax.showMessages (fieldId);

			hideWait ();
		});

		return false;
	}

	global.Collection.ajax.delay (function ()
	{
		global.Collection.changeRow (itemId, fieldId, file);

		global.Collection.ajax.showMessages (fieldId);

		global.Collection.closeEdit (fieldId);

		hideWait ();
	});

	return false;
}

global.Collection.addRow = function (itemId, fieldId, file)
{
	var columns = [];

	var aux = global.Collection.ajax.addRow (itemId, file, fieldId);

	eval (aux);

	var row = global.Collection.makeRow (itemId, columns);

	var spacer = global.Collection.makeSpacer (itemId, columns);

	$$('#collection_view_' + fieldId + ' tr').last ().insert ({ after: row });

	row.insert ({ after: spacer });
}

global.Collection.changeRow = function (itemId, fieldId, file)
{
	var columns = [];

	var aux = global.Collection.ajax.addRow (itemId, file, fieldId);

	eval (aux);

	var row = global.Collection.makeRow (itemId, columns);

	var previous = $('collection_row_' + itemId).previous ();

	$('collection_row_' + itemId).remove ();

	previous.insert ({ after: row });
}

global.Collection.makeRow = function (itemId, columns)
{
	var row = document.createElement ('tr');

	row.id = 'collection_row_' + itemId;

	row.className = 'cTableItem';

	for (var i = 0 ; i < columns.length ; i++)
	{
		column = document.createElement ('td');

		if (i == (columns.length - 1))
		{
			column.nowrap = 'nowrap';
			column.style.textAlign = 'right';
		}

		column.innerHTML = columns [i];

		row.appendChild (column);
	}

	return row;
}

global.Collection.makeSpacer = function (itemId, columns)
{
	var row = document.createElement ('tr');

	for (var i = 0 ; i < columns.length ; i++)
		row.appendChild (document.createElement ('td'));

	row.className = 'cSeparator';

	row.id = 'collection_row_' + itemId + '_space';

	return row;
}

global.Collection.delRow = function (fieldId, file, itemId)
{
	$('collectionLabelMessage_' + fieldId).innerHTML = '';

	if (!confirm ('Tem certeza que deseja apagar o item? Esta ação é irreversível.'))
		return false;

	showWait ();

	if (!(global.Collection.ajax.delRow (itemId, file), function () {
		global.Collection.ajax.showMessages (fieldId);

		hideWait ();
	}))
	{
		return false;
	}

	global.Collection.ajax.delay (function () {
		$('collection_row_' + itemId).style.display = 'none';
		$('collection_row_' + itemId + '_space').style.display = 'none';

		global.Collection.ajax.showMessages (fieldId);

		hideWait ();
	});

	return false;
}

global.Collection.up = function (icon)
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

global.Collection.down = function (icon)
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

global.Collection.saveSort = function (icon, file, fieldId)
{
	$('collectionLabelMessage_' + fieldId).innerHTML = '';

	showWait ();

	var table = icon.ancestors() [2];

	var hiddens = table.next ('tbody').select ('input[name="idForSort"]');

	var sort = [];

	for (var i = 0; i < hiddens.length; i++)
		sort [i] = hiddens [i].value;

	global.Collection.ajax.saveSort (file, sort, function ()
	{
		global.Collection.ajax.showMessages (fieldId);

		hideWait ();
	});
}
</script>
