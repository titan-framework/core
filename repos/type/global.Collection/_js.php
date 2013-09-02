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
	
	var edit = $('collection_edit_' + fieldId);
	
	if (edit.style.display == '')
	{
		edit.style.display = 'none';
		
		$('collection_form_' + fieldId).reset ();
	}
	else
	{
		$('collection_label_' + fieldId).innerHTML = 'Inserir Novo Item';
		
		$('collection_id_' + fieldId).value = 0;
		
		edit.style.display = '';
	}
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
</script>