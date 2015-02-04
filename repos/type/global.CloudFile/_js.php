<script language="javascript" type="text/javascript">
'global.CloudFile'.namespace ();

global.CloudFile.ajax = <?= class_exists ('xCloudFile', FALSE) ? XOAD_Client::register (new xCloudFile) : 'null' ?>;

global.CloudFile.load = function (id, field)
{
	$('_CLOUD_UPLOAD_' + field).style.display = 'none';
	
	$('_CLOUD_HIDDEN_' + field).value = id;
	
	var resume = global.CloudFile.ajax.getFileResume (id);
	
	resume += '<div style="cursor: pointer; width: 36px; height: 36px; float: right; position: absolute; top: 0px; right: 0px; z-index: 3; background: url(titan.php?target=tResource&type=CloudFile&file=delete.png) no-repeat right 5px top 5px;" onclick="JavaScript: global.CloudFile.upload (\'' + field + '\');" title="<?=__ ('Unlink File')?>" alt="<?=__ ('Unlink File')?>"></div>';
	
	var file = $('_CLOUD_UPLOADED_' + field);
	
	file.innerHTML = resume;
	
	file.style.display = '';
}

global.CloudFile.upload = function (field)
{
	$('_CLOUD_UPLOADED_' + field).style.display = 'none';
		
	$('_CLOUD_UPLOAD_' + field).style.display = '';
	
	$('_CLOUD_HIDDEN_' + field).value = 0;
}

global.CloudFile.error = function (error, field)
{
	$('_CLOUD_ERROR_' + field).innerHTML = error;
	
	$('_CLOUD_ERROR_' + field).style.display = '';
}

global.CloudFile.clear = function (field)
{
	$('_CLOUD_ERROR_' + field).style.display = 'none';
	
	$('_CLOUD_ERROR_' + field).innerHTML = '';
}

global.CloudFile.filterKey = new Array ();
global.CloudFile.filterValue = new Array ();
global.CloudFile.filterCount = 0;

global.CloudFile.getFilter = function (field)
{
	for (var i = 0 ; i < global.CloudFile.filterCount ; i++)
		if (global.CloudFile.filterKey [i] == field)
			return global.CloudFile.filterValue [i];
	
	return '';
}

global.CloudFile.addFilter = function (field, mimes)
{
	global.CloudFile.filterKey [global.CloudFile.filterCount] = field;
	global.CloudFile.filterValue [global.CloudFile.filterCount] = mimes;
	global.CloudFile.filterCount++;
}
</script>