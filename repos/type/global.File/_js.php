<script language="javascript" type="text/javascript">
'global.File'.namespace ();

global.File.ajax = <?= class_exists ('xFile', FALSE) ? XOAD_Client::register (new xFile) : 'null' ?>;

global.File.load = function (id, field)
{
	$('_TITAN_GLOBAL_FILE_UPLOAD_' + field).style.display = 'none';
	
	$('_TITAN_GLOBAL_FILE_HIDDEN_' + field).value = id;
	
	var resume = global.File.ajax.getFileResume (id);
	
	resume += '<div style="cursor: pointer; width: 36px; height: 36px; float: right; position: absolute; top: 0px; right: 0px; z-index: 3; background: url(titan.php?target=tResource&type=File&file=delete.png) no-repeat right 5px top 5px;" onclick="JavaScript: global.File.upload (\'' + field + '\');" title="<?=__ ('Unlink File')?>" alt="<?=__ ('Unlink File')?>"></div>';
	
	var file = $('_TITAN_GLOBAL_FILE_UPLOADED_' + field);
	
	file.innerHTML = resume;
	
	file.style.display = '';
}

global.File.upload = function (field)
{
	$('_TITAN_GLOBAL_FILE_UPLOADED_' + field).style.display = 'none';
		
	$('_TITAN_GLOBAL_FILE_UPLOAD_' + field).style.display = '';
	
	$('_TITAN_GLOBAL_FILE_HIDDEN_' + field).value = 0;
}

global.File.error = function (error, field)
{
	$('_TITAN_GLOBAL_FILE_ERROR_' + field).innerHTML = error;
	
	$('_TITAN_GLOBAL_FILE_ERROR_' + field).style.display = '';
}

global.File.clear = function (field)
{
	$('_TITAN_GLOBAL_FILE_ERROR_' + field).style.display = 'none';
	
	$('_TITAN_GLOBAL_FILE_ERROR_' + field).innerHTML = '';
}

global.File.filterKey = new Array ();
global.File.filterValue = new Array ();
global.File.filterCount = 0;

global.File.getFilter = function (field)
{
	for (var i = 0 ; i < global.File.filterCount ; i++)
		if (global.File.filterKey [i] == field)
			return global.File.filterValue [i];
	
	return '';
}

global.File.addFilter = function (field, mimes)
{
	global.File.filterKey [global.File.filterCount] = field;
	global.File.filterValue [global.File.filterCount] = mimes;
	global.File.filterCount++;
}

global.File.archive = function (field, ownerOnly)
{
	var size = getWindowSize ();
	
	var h = size.height - 70;
	var w = size.width - 100;
	
	var source = '	<div style="margin: 0 auto; width: 420px; height: 36px; line-height: 36px; vertical-align: middle;">\
						<input type="text" class="globalFileSearchBox" id="_TITAN_GLOBAL_FILE_SEARCH_BOX_" onkeypress="JavaScript: global.File.search (this, event, \'' + field + '\', ' + (ownerOnly ? 'true' : 'false') + ');" />\
					</div>\
					<div class="globalFileSearchError" style="display: none;" id="_TITAN_GLOBAL_FILE_SEARCH_ERROR_"></div>\
					<div class="globalFileSearchInfo" id="_TITAN_GLOBAL_FILE_SEARCH_INFO_"></div>\
					<div class="globalFileSearchResult">\
						<ul id="_TITAN_GLOBAL_FILE_SEARCH_RESULT_"></ul>\
					</div>';
	
	Modalbox.show (source, { title: '<?= __ ('Recovery from archive...') ?>', width: w, height: h, afterLoad: function () {
		$('_TITAN_GLOBAL_FILE_SEARCH_INFO_').innerHTML = '<?= __ ('Showing recent files sent.') ?>'
		
		global.File.last (field, ownerOnly);
		
		$('_TITAN_GLOBAL_FILE_SEARCH_BOX_').focus ();
	} });
}

global.File.search = function (input, event, field, ownerOnly)
{
	var key = event.keyCode ? event.keyCode : evt.charCode ? evt.charCode : evt.which ? evt.which : 0;
	
    if (key != 13)
        return false;
	
	var term = input.value;
	
	if (term.trim ().length < 3)
		global.File.searchError ('<?= __ ('This search term is too short. Use at least 3 characters.') ?>');
	
	$('_TITAN_GLOBAL_FILE_SEARCH_INFO_').innerHTML = '<?= __ ('Showing results for the term') ?> \'' + term + '\'.';
	
	global.File.searchClear ();
	
	var filter = global.File.getFilter (field);
	
	global.File.showResult (global.File.ajax.search (term, filter, ownerOnly), field);
}

global.File.last = function (field, ownerOnly)
{
	global.File.searchClear ();
	
	var filter = global.File.getFilter (field);
	
	global.File.showResult (global.File.ajax.last (filter, ownerOnly), field);
}

global.File.showResult = function (json, field)
{
	eval ('var result = ' + json + ';');
	
	for (var i = 0; i < result.length; i++)
	{
		var div = document.createElement ('div');
		
		div.style.width = '100px';
		div.style.height = '100px';
		div.style.background = '#FFF url(titan.php?target=tResource&type=File&file=loading.png) no-repeat center';
		div.title = result [i].author;
		div.id = '_TITAN_GLOBAL_FILE_SEARCH_THUMBNAIL_' + result [i].id;
		
		var name = document.createElement ('div');
		
		name.className = 'name';
		
		name.innerHTML = result [i].name;
		
		var size = document.createElement ('div');
		
		size.className = 'size';
		
		size.innerHTML = result [i].size;
		
		var li = document.createElement ('li');
		
		li.onclick = function (id, fi) { return function () { global.File.clear (field); global.File.load (id, fi); Modalbox.hide (); } } (result [i].id, field);
		
		li.appendChild (div);
		
		li.appendChild (size);
		
		li.appendChild (name);
		
		$('_TITAN_GLOBAL_FILE_SEARCH_RESULT_').appendChild (li);
	}
	
	$('_TITAN_GLOBAL_FILE_SEARCH_INFO_').innerHTML = $('_TITAN_GLOBAL_FILE_SEARCH_INFO_').innerHTML + ' ' + result.length + ' <?= __ ('results found') ?>.'
	
	for (var i = 0; i < result.length; i++)
		$('_TITAN_GLOBAL_FILE_SEARCH_THUMBNAIL_' + result [i].id).style.background = '#FFF url(titan.php?target=tScript&type=File&file=thumbnail&fileId=' + result [i].id + '&width=100&height=100) no-repeat center';
}

global.File.searchError = function (error)
{
	$('_TITAN_GLOBAL_FILE_SEARCH_ERROR_').innerHTML = error;
	
	$('_TITAN_GLOBAL_FILE_SEARCH_ERROR_').style.display = 'block';
}

global.File.searchClear = function ()
{
	$('_TITAN_GLOBAL_FILE_SEARCH_ERROR_').innerHTML = '';
	
	$('_TITAN_GLOBAL_FILE_SEARCH_ERROR_').style.display = 'none';
	
	var ul = $('_TITAN_GLOBAL_FILE_SEARCH_RESULT_');
	
	while (ul.firstChild)
		ul.removeChild (ul.firstChild);
}
</script>