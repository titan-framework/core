<script language="javascript" type="text/javascript">
'global.CloudFile'.namespace ();

global.CloudFile.ajax = <?= class_exists ('xCloudFile', FALSE) ? XOAD_Client::register (new xCloudFile) : 'null' ?>;

global.CloudFile.load = function (fileId, fieldId)
{
	$(fieldId + '_upload').style.display = 'none';
	
	$(fieldId + '_real_id').value = fileId;
	
	var file = $(fieldId + '_selected');
	
	var resume = global.CloudFile.ajax.getFileResume (fileId);
	
	resume += '<div style="cursor: pointer; width: 26px; height: 26px; float: right; position: absolute; top: 0px; right: 0px; z-index: 3; background: url(titan.php?target=loadFile&file=interface/icon/delete.gif) no-repeat right 4px top 4px;" onclick="JavaScript: global.CloudFile.upload (\'' + fieldId + '\');" title="<?=__ ('Unlink File')?>" alt="<?=__ ('Unlink File')?>"></div>';
	
	file.innerHTML = resume;
	
	file.style.display = '';
}

global.CloudFile.upload = function (fieldId)
{
	var iframe = $(fieldId + '_upload');
	
	$(fieldId + '_selected').style.display = 'none';
		
	iframe.style.display = '';
	
	$(fieldId + '_real_id').value = 0;
}

global.CloudFile.filterKey = new Array ();
global.CloudFile.filterValue = new Array ();
global.CloudFile.filterCount = 0;

global.CloudFile.getFilter = function (fieldId)
{
	for (var i = 0 ; i < global.CloudFile.filterCount ; i++)
		if (global.CloudFile.filterKey [i] == fieldId)
			return global.CloudFile.filterValue [i];
	
	return '';
}

global.CloudFile.addFilter = function (fieldId, mimes)
{
	global.CloudFile.filterKey [global.CloudFile.filterCount] = fieldId;
	global.CloudFile.filterValue [global.CloudFile.filterCount] = mimes;
	global.CloudFile.filterCount++;
}
</script>