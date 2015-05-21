<script type="text/javascript" src="<?= Fck::getCKEditorPath () ?>ckeditor.js"></script>
<script language="javascript" type="text/javascript">
'global.Fck'.namespace ();

global.Fck.ajax = <?= class_exists ('xFck', FALSE) ? XOAD_Client::register (new xFck) : 'null' ?>;

global.Fck.toolbar = [
	{ name: 'document', groups: [ 'mode', 'document', 'doctools' ], items: [ 'Preview', 'Print', '-', 'Templates' ] },
	{ name: 'clipboard', groups: [ 'clipboard', 'undo' ], items: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
	{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ], items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat' ] },
	{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker' ], items: [ 'Find', 'Replace', '-', 'SelectAll', '-', 'Scayt' ] },
	{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ], items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl' ] },
	{ name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] },
	{ name: 'insert', items: [ 'Image', 'MediaEmbed', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak' ] },
	{ name: 'styles', items: [ 'Styles', 'Format', 'Font', 'FontSize' ] },
	{ name: 'colors', items: [ 'TextColor', 'BGColor' ] },
	{ name: 'tools', items: [ 'Maximize', 'ShowBlocks', '-', 'Source' ] },
	{ name: 'about', items: [ 'About' ] }
];

global.Fck.plugins = 'iframedialog,mediaembed';

CKEDITOR.on ('dialogDefinition', function (ev)
{	
	if (CKEDITOR.currentInstance.config.titanType != 'Fck')
		return;
	
	var public = CKEDITOR.currentInstance.config.titanPublic;
	var ownerOnly = CKEDITOR.currentInstance.config.titanOwnerOnly;
	
	var dialogName = ev.data.name;
	var dialogDefinition = ev.data.definition;
	
	if (dialogName == 'image')
	{
		var tab = dialogDefinition.getContents ('info');
		
		tab.elements.splice (tab.elements.length - 2, 0, {
			type: 'html',
			id: 'tError',
			label: '',
			html: '<div id="_FCK_UPLOAD_ERROR_' + CKEDITOR.currentInstance.name + '_image" style="display: none; word-wrap: break-word; width: 405px; overflow-x: scroll; margin: 0 auto; border: #900 1px solid; padding: 6px; background-color: #EBCCCC; color: #900; font-weight: bold;"></div>'
		});
		
		tab.elements.splice (tab.elements.length - 2, 0, {
			type: 'iframe',
			id: 'tUpload',
			label: 'Upload',
			src: 'titan.php?target=tScript&type=Fck&file=upload&field=' + CKEDITOR.currentInstance.name + '&media=image&public=' + (public ? '1' : '0') + '&owner=' + (ownerOnly ? '1' : '0') + '&auth=1', 
			width: '100%', 
			height: '63px'
		});
	}
	
	if (dialogName == 'link')
	{
		ev.data.definition.minWidth = 440;
		
		var tab = dialogDefinition.getContents ('info');
		
		tab.add ({
			type: 'html',
			id: 'tError',
			label: '',
			html: '<div id="_FCK_UPLOAD_ERROR_' + CKEDITOR.currentInstance.name + '_all" style="display: none; word-wrap: break-word; width: 405px; overflow-x: scroll; margin: 0 auto; border: #900 1px solid; padding: 6px; background-color: #EBCCCC; color: #900; font-weight: bold;"></div>'
		});
		
		tab.add ({
			type: 'iframe',
			id: 'tUpload',
			label: 'Upload',
			src: 'titan.php?target=tScript&type=Fck&file=upload&field=' + CKEDITOR.currentInstance.name + '&media=all&public=' + (public ? '1' : '0') + '&owner=' + (ownerOnly ? '1' : '0') + '&auth=1', 
			width: '100%', 
			height: '63px'
		});
	}
	
	if (dialogName == 'MediaEmbedDialog')
	{
		ev.data.definition.minWidth = 440;
		
		var tab = dialogDefinition.getContents ('iframe');
		
		tab.add ({
			type: 'html',
			id: 'tError',
			label: '',
			html: '<div id="_FCK_UPLOAD_ERROR_' + CKEDITOR.currentInstance.name + '_media" style="display: none; word-wrap: break-word; width: 405px; overflow-x: scroll; margin: 0 auto; border: #900 1px solid; padding: 6px; background-color: #EBCCCC; color: #900; font-weight: bold;"></div>'
		});
		
		tab.add ({
			type: 'iframe',
			id: 'tUpload',
			label: 'Upload',
			src: 'titan.php?target=tScript&type=Fck&file=upload&field=' + CKEDITOR.currentInstance.name + '&media=media&public=' + (public ? '1' : '0') + '&owner=' + (ownerOnly ? '1' : '0') + '&auth=1', 
			width: '100%', 
			height: '63px'
		});
	}
});

global.Fck.imageUploadClear = function (field, media)
{
	var div = $('_FCK_UPLOAD_ERROR_' + field + '_' + media);
	
	div.style.display = 'none';
	
	div.innerHTML = '';
};

global.Fck.load = function (field, media, id, hash)
{
	var dialog = CKEDITOR.dialog.getCurrent ();
	
	switch (media)
	{
		case 'image':
			dialog.setValueOf ('info', 'txtUrl', '<?= Instance::singleton ()->getUrl () ?>titan.php?target=tScript&type=File&file=open&id=' + id + (hash.length > 0 ? '&hash=' + hash : ''));
			break;
		
		case 'all':
			dialog.setValueOf ('info', 'url', '<?= Instance::singleton ()->getUrl () ?>titan.php?target=tScript&type=File&file=open&id=' + id + (hash.length > 0 ? '&hash=' + hash : ''));
			break;
		
		case 'media':
			var resume = global.Fck.ajax.getFileResume (id, hash);
			dialog.setValueOf ('iframe', 'embedArea', resume);
			break;
	}
};

global.Fck.imageUploadError = function (field, media, error)
{
	var div = $('_FCK_UPLOAD_ERROR_' + field + '_' + media);
	
	div.innerHTML = error;
	
	div.style.display = '';
};

global.Fck.archive = function (field, ownerOnly, media)
{
	var size = getWindowSize ();
	
	var h = size.height - 70;
	var w = size.width - 100;
	
	var source = '	<div style="margin: 0 auto; width: 420px; height: 36px; line-height: 36px; vertical-align: middle;">\
						<input type="text" class="globalFileSearchBox" id="_TITAN_GLOBAL_FCK_SEARCH_BOX_" onkeypress="JavaScript: global.Fck.search (this, event, \'' + field + '\', ' + (ownerOnly ? 'true' : 'false') + ', \'' + media + '\');" />\
					</div>\
					<div class="globalFileSearchError" style="display: none;" id="_TITAN_GLOBAL_FCK_SEARCH_ERROR_"></div>\
					<div class="globalFileSearchInfo" id="_TITAN_GLOBAL_FCK_SEARCH_INFO_"></div>\
					<div class="globalFileSearchResult">\
						<ul id="_TITAN_GLOBAL_FCK_SEARCH_RESULT_"></ul>\
					</div>';
	
	Modalbox.show (source, { title: '<?= __ ('Recovery from archive...') ?>', width: w, height: h, afterLoad: function () {
		$('_TITAN_GLOBAL_FCK_SEARCH_INFO_').innerHTML = '<?= __ ('Showing recent files sent.') ?>'
		
		global.Fck.last (field, ownerOnly, media);
		
		$('_TITAN_GLOBAL_FCK_SEARCH_BOX_').focus ();
	} });
};

global.Fck.search = function (input, event, field, ownerOnly, media)
{
	var key = event.keyCode ? event.keyCode : evt.charCode ? evt.charCode : evt.which ? evt.which : 0;
	
    if (key != 13)
        return false;
	
	var term = input.value;
	
	if (term.trim ().length < 3)
		global.Fck.searchError ('<?= __ ('This search term is too short. Use at least 3 characters.') ?>');
	
	$('_TITAN_GLOBAL_FCK_SEARCH_INFO_').innerHTML = '<?= __ ('Showing results for the term') ?> \'' + term + '\'.';
	
	global.Fck.searchClear ();
	
	var filter = global.Fck.getFilter (media);
	
	global.Fck.showResult (global.File.ajax.search (term, filter, ownerOnly), field, media);
};

global.Fck.last = function (field, ownerOnly, media)
{
	global.Fck.searchClear ();
	
	var filter = global.Fck.getFilter (media);
	
	global.Fck.showResult (global.File.ajax.last (filter, ownerOnly), field, media);
};

global.Fck.showResult = function (json, field, media)
{
	eval ('var result = ' + json + ';');
	
	for (var i = 0; i < result.length; i++)
	{
		var div = document.createElement ('div');
		
		div.style.width = '100px';
		div.style.height = '100px';
		div.style.background = '#FFF url(titan.php?target=tResource&type=File&file=loading.png) no-repeat center';
		div.title = result [i].author;
		div.id = '_TITAN_GLOBAL_FCK_SEARCH_THUMBNAIL_' + result [i].id;
		
		var name = document.createElement ('div');
		
		name.className = 'name';
		
		name.innerHTML = result [i].name;
		
		var size = document.createElement ('div');
		
		size.className = 'size';
		
		size.innerHTML = result [i].size;
		
		var li = document.createElement ('li');
		
		li.onclick = function (id, fi, me) { return function () { global.Fck.imageUploadClear (fi, me); global.Fck.load (fi, me, id, ''); Modalbox.hide (); } } (result [i].id, field, media);
		
		li.appendChild (div);
		
		li.appendChild (size);
		
		li.appendChild (name);
		
		$('_TITAN_GLOBAL_FCK_SEARCH_RESULT_').appendChild (li);
	}
	
	$('_TITAN_GLOBAL_FCK_SEARCH_INFO_').innerHTML = $('_TITAN_GLOBAL_FCK_SEARCH_INFO_').innerHTML + ' ' + result.length + ' <?= __ ('results found') ?>.'
	
	for (var i = 0; i < result.length; i++)
		$('_TITAN_GLOBAL_FCK_SEARCH_THUMBNAIL_' + result [i].id).style.background = '#FFF url(titan.php?target=tScript&type=File&file=thumbnail&fileId=' + result [i].id + '&width=100&height=100) no-repeat center';
};

global.Fck.searchError = function (error)
{
	$('_TITAN_GLOBAL_FCK_SEARCH_ERROR_').innerHTML = error;
	
	$('_TITAN_GLOBAL_FCK_SEARCH_ERROR_').style.display = 'block';
};

global.Fck.searchClear = function ()
{
	$('_TITAN_GLOBAL_FCK_SEARCH_ERROR_').innerHTML = '';
	
	$('_TITAN_GLOBAL_FCK_SEARCH_ERROR_').style.display = 'none';
	
	var ul = $('_TITAN_GLOBAL_FCK_SEARCH_RESULT_');
	
	while (ul.firstChild)
		ul.removeChild (ul.firstChild);
};

global.Fck.getFilter = function (media)
{
	switch (media)
	{
		case 'image':
			return '<?= implode (',', Archive::singleton ()->getMimesByType (Archive::IMAGE)) ?>';
		
		case 'video':
			return '<?= implode (',', Archive::singleton ()->getMimesByType (Archive::VIDEO)) ?>';
		
		case 'audio':
			return '<?= implode (',', Archive::singleton ()->getMimesByType (Archive::AUDIO)) ?>';
		
		case 'media':
			return '<?= implode (',', array_merge (Archive::singleton ()->getMimesByType (Archive::VIDEO), Archive::singleton ()->getMimesByType (Archive::AUDIO))) ?>';
	}
	
	return '';
};
</script>