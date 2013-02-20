<script language="javascript" type="text/javascript">
'global.Select'.namespace ();

global.Select.showSearch = function (fieldId)
{
	var iframe = $(fieldId + '_SEARCH_');
	
	if (iframe.style.display == '')
		iframe.style.display = 'none';
	else
		iframe.style.display = '';
}

global.Select.choose = function (fieldId, itemId, text)
{
	var field = $(fieldId);
	var label = $(fieldId + '_LABEL_');
	var div = $(fieldId + '_SEARCH_');
	var del = $(fieldId + '_DELETE_');
	
	field.value = itemId;
	label.value = text;
	div.style.display = 'none';
	
	del.src = 'titan.php?target=loadFile&file=interface/icon/delete.gif';
	del.onclick = function () { global.Select.clear (fieldId); };
	del.className = 'icon';
}

global.Select.clear = function (fieldId)
{
	$(fieldId).value = 0;
	$(fieldId + '_LABEL_').value = '';
	
	var del = $(fieldId + '_DELETE_');
	del.src = 'titan.php?target=loadFile&file=interface/icon/grey/delete.gif';
	del.onclick = function () { return false; };
	del.className = '';
}
</script>