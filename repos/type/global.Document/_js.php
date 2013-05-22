<script language="javascript" type="text/javascript">
'global.Document'.namespace ();

global.Document.ajax = <?= class_exists ('xDocument', FALSE) ? XOAD_Client::register (new xDocument) : 'null' ?>;

global.Document.add = function (fieldId, icon)
{
	icon.style.display = 'none';
	
	$('_TERM_SELECT_' + fieldId).selectedIndex = 0;
	
	$('_TERM_SELECT_' + fieldId).style.display = 'block';
}

global.Document.showCreate = function (fieldId, sel)
{
	sel.style.display = 'none';
	
	var id = sel.options[sel.selectedIndex].value;
	
	$('_TERM_FORM_' + fieldId + '_' + id).reset ();
	
	$('_TERM_' + fieldId + '_' + id).style.display = 'block';
}

global.Document.cancel = function (fieldId, id)
{
	if (!confirm ('<?= __ ('Are you sure you want to cancel? All changes will be lost.') ?>'))
		return false;
	
	$('_TERM_' + fieldId + '_' + id).style.display = 'none';
	
	$('_TERM_ADD_' + fieldId).style.display = 'block';
}

global.Document.save = function (fieldId, fatherId, id, relation, template, label, validate)
{
	showWait ();
	
	$('_TERM_FORM_SAVE_' + fieldId + '_' + id).value = '<?= __ ('Wait...') ?>';
	$('_TERM_FORM_SAVE_' + fieldId + '_' + id).disabled = true;
	$('_TERM_FORM_CANCEL_' + fieldId + '_' + id).disabled = true;
	
	var formData = xoad.html.exportForm ('_TERM_FORM_' + fieldId + '_' + id);
	
	var version = global.Document.ajax.save (formData, fatherId, id, relation, template, validate);
	
	if (!version)
	{
		tAjax.delay (function () {
			global.Document.ajax.showMessages ();
			
			$('_TERM_FORM_SAVE_' + fieldId + '_' + id).value = '<?= __ ('Save') ?>';
			$('_TERM_FORM_SAVE_' + fieldId + '_' + id).disabled = false;
			$('_TERM_FORM_CANCEL_' + fieldId + '_' + id).disabled = false;
			
			hideWait (); 
		});
		
		return false;
	}
	
	global.Document.ajax.delay (function () {
		global.Document.addRow (fieldId, fatherId, id, relation, label, version, template);
		
		global.Document.ajax.showMessages ();
		
		$('_TERM_FORM_SAVE_' + fieldId + '_' + id).value = '<?= __ ('Save') ?>';
		$('_TERM_FORM_SAVE_' + fieldId + '_' + id).disabled = false;
		$('_TERM_FORM_CANCEL_' + fieldId + '_' + id).disabled = false;
		
		$('_TERM_' + fieldId + '_' + id).style.display = 'none';
		
		$('_TERM_ADD_' + fieldId).style.display = 'block';
		
		hideWait (); 
	});
	
	return false;
}

global.Document.addRow = function (fieldId, fatherId, id, relation, label, version, template)
{
	var old = $('_TERM_ROW_' + fieldId + '_' + id + '_' + (version - 1));
	
	if (old)
	{
		old.style.backgroundImage = 'url(titan.php?target=loadFile&file=interface/back/aba.gif)';
		$('_TERM_COLUMN_' + fieldId + '_' + id + '_' + (version - 1)).style.textDecoration = 'line-through';
		$('_TERM_IMG_' + fieldId + '_' + id + '_' + (version - 1)).src = 'titan.php?target=loadFile&file=interface/icon/grey/pdf.gif';
	}
	
	var column, icon, row = document.createElement ('tr');
	
	row.id = '_TERM_ROW_' + fieldId + '_' + id + '_' + version;
	
	row.className = 'cTableItem';
	
	column = document.createElement ('td');
	column.id = '_TERM_COLUMN_' + fieldId + '_' + id + '_' + version;
	column.style.textDecoration = 'none';
	column.innerHTML = label;
	row.appendChild (column);
	
	column = document.createElement ('td');
	column.innerHTML = version;
	row.appendChild (column);
	column = document.createElement ('td');
	column.innerHTML = '<?= __ ('You') ?>';
	row.appendChild (column);
	
	column = document.createElement ('td');
	column.innerHTML = '<?= __ ('Now') ?>';
	row.appendChild (column);
	
	column = document.createElement ('td');
	
	var hash = global.Document.ajax.register (relation, id, fatherId, version, template, label);
	
	if (hash && hash != '')
	{ 
		icon = document.createElement ('img');
		icon.id = '_TERM_IMG_' + fieldId + '_' + id + '_' + version;
		icon.src = 'titan.php?target=loadFile&file=interface/icon/pdf.gif';
		icon.border = '0';
		icon.title = '<?= __ ('Generate PDF') ?>';
		icon.style.cursor = 'pointer';
		icon.onclick = function () { global.Document.openDocument (hash); };
		
		column.style.textAlign = 'right';
		column.appendChild (icon);
	}
	
	row.appendChild (column);
	
	$('_TERM_VIEW_' + fieldId).appendChild (row);
	
	row = document.createElement ('tr');
	
	column = document.createElement ('td');
	
	column.colSpan = '5';
	
	row.appendChild (column);
	
	row.className = 'cSeparator';
	
	$('_TERM_VIEW_' + fieldId).appendChild (row);
}

global.Document.openDocument = function (hash)
{
	openPopup ('titan.php?target=tScript&type=Document&file=gen&auth=1&hash=' + hash);
}
</script>