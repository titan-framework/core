<script language="javascript" type="text/javascript">
'global.File'.namespace ();

global.File.load = function (fileId, fieldId)
{
	$(fieldId + '_upload').style.display = 'none';
	
	$(fieldId + '_real_id').value = fileId;
	
	var file = $(fieldId + '_selected');
	
	file.innerHTML = tAjax.getFileResume (fileId);
	file.style.display = '';
	
	global.File.enableUnset (fieldId);
}

global.File.enableUnset = function (fieldId)
{
	var icon = $('del_' + fieldId);
	
	icon.className = 'icon';
	icon.src = 'titan.php?target=loadFile&file=interface/icon/delete.gif';
	icon.onclick = function () { global.File.unset (fieldId, 1); };
}

global.File.disableUnset = function (fieldId)
{
	var icon = $('del_' + fieldId);
	
	icon.className = '';
	icon.src = 'titan.php?target=loadFile&file=interface/icon/grey/delete.gif';
	icon.onclick = function () {};
}

global.File.unset = function (fieldId, hiddenLabel)
{
	$(fieldId + '_upload').style.display = 'none';
	$(fieldId + '_selected').style.display = 'none';
	$(fieldId + '_real_id').value = 0;
	
	if (hiddenLabel)
		$(fieldId).value = '';
	
	global.File.disableUnset (fieldId);
}

global.File.upload = function (fieldId)
{
	var iframe = $(fieldId + '_upload');
	
	if (iframe.style.display == '')
		iframe.style.display = 'none';
	else
	{
		$(fieldId + '_selected').style.display = 'none';
		
		iframe.style.display = '';
	}
}

global.File.filterKey = new Array ();
global.File.filterValue = new Array ();
global.File.filterCount = 0;

global.File.getFilter = function (fieldId)
{
	for (var i = 0 ; i < global.File.filterCount ; i++)
		if (global.File.filterKey [i] == fieldId)
			return global.File.filterValue [i];
	
	return '';
}

global.File.addFilter = function (fieldId, mimes)
{
	global.File.filterKey [global.File.filterCount] = fieldId;
	global.File.filterValue [global.File.filterCount] = mimes;
	global.File.filterCount++;
}
</script>