<script language="javascript" type="text/javascript">
'global.Select'.namespace ();

global.Select.showSearch = function (fieldId)
{
	var iframe = $('_SEARCH_AT_SELECT_TYPE_' + fieldId);
	
	if (iframe.style.display == 'block')
		iframe.style.display = 'none';
	else
		iframe.style.display = 'block';
}

global.Select.choose = function (fieldId, itemId, text)
{
	var field = $(fieldId);
	var label = $('_LABEL_AT_SELECT_TYPE_' + fieldId);
	var div = $('_SEARCH_AT_SELECT_TYPE_' + fieldId);
	var del = $('_CLEAR_AT_SELECT_TYPE_' + fieldId);
	
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
	$('_LABEL_AT_SELECT_TYPE_' + fieldId).value = '';
	
	var del = $('_CLEAR_AT_SELECT_TYPE_' + fieldId);
	del.src = 'titan.php?target=loadFile&file=interface/icon/grey/delete.gif';
	del.onclick = function () { return false; };
	del.className = '';
}
</script>