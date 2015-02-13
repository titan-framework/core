<script type="text/javascript" src="<?= CKEditor::getCKEditorPath () ?>ckeditor.js"></script>
<script language="javascript" type="text/javascript">
'global.CKEditor'.namespace ();

global.CKEditor.ajax = <?= class_exists ('xCKEditor', FALSE) ? XOAD_Client::register (new xCKEditor) : 'null' ?>;

global.CKEditor.toolbar = [
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

global.CKEditor.plugins = 'iframedialog,mediaembed';

CKEDITOR.on ('dialogDefinition', function (ev) {
	var dialogName = ev.data.name;
	var dialogDefinition = ev.data.definition;
	
	if (dialogName == 'image')
	{
		var tab = dialogDefinition.getContents ('info');
		
		tab.elements.splice (tab.elements.length - 2, 0, {
			type: 'html',
			id: 'tError',
			label: '',
			html: '<div id="_CKEDITOR_UPLOAD_ERROR_' + CKEDITOR.currentInstance.name + '_image" style="display: none; word-wrap: break-word; width: 405px; overflow-x: scroll; margin: 0 auto; border: #900 1px solid; padding: 6px; background-color: #EBCCCC; color: #900; font-weight: bold;"></div>'
		});
		
		tab.elements.splice (tab.elements.length - 2, 0, {
			type: 'iframe',
			id: 'tUpload',
			label: 'Upload',
			src: 'titan.php?target=tScript&type=CKEditor&file=upload&field=' + CKEDITOR.currentInstance.name + '&media=image&auth=1', 
			width: '100%', 
			height: '47px'
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
			html: '<div id="_CKEDITOR_UPLOAD_ERROR_' + CKEDITOR.currentInstance.name + '_all" style="display: none; word-wrap: break-word; width: 405px; overflow-x: scroll; margin: 0 auto; border: #900 1px solid; padding: 6px; background-color: #EBCCCC; color: #900; font-weight: bold;"></div>'
		});
		
		tab.add ({
			type: 'iframe',
			id: 'tUpload',
			label: 'Upload',
			src: 'titan.php?target=tScript&type=CKEditor&file=upload&field=' + CKEDITOR.currentInstance.name + '&media=all&auth=1', 
			width: '100%', 
			height: '47px'
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
			html: '<div id="_CKEDITOR_UPLOAD_ERROR_' + CKEDITOR.currentInstance.name + '_media" style="display: none; word-wrap: break-word; width: 405px; overflow-x: scroll; margin: 0 auto; border: #900 1px solid; padding: 6px; background-color: #EBCCCC; color: #900; font-weight: bold;"></div>'
		});
		
		tab.add ({
			type: 'iframe',
			id: 'tUpload',
			label: 'Upload',
			src: 'titan.php?target=tScript&type=CKEditor&file=upload&field=' + CKEDITOR.currentInstance.name + '&media=media&auth=1', 
			width: '100%', 
			height: '47px'
		});
	}
});

global.CKEditor.imageUploadClear = function (field, media)
{
	var div = $('_CKEDITOR_UPLOAD_ERROR_' + field + '_' + media);
	
	div.style.display = 'none';
	
	div.innerHTML = '';
}

global.CKEditor.imageUploadSuccess = function (field, media, id, hash)
{
	var dialog = CKEDITOR.dialog.getCurrent ();
	
	switch (media)
	{
		case 'image':
			dialog.setValueOf ('info', 'txtUrl', '<?= Instance::singleton ()->getUrl () ?>titan.php?target=tScript&type=CKEditor&file=open&id=' + id + '&hash=' + hash);
			break;
		
		case 'all':
			dialog.setValueOf ('info', 'url', '<?= Instance::singleton ()->getUrl () ?>titan.php?target=tScript&type=CKEditor&file=open&id=' + id + '&hash=' + hash);
			break;
		
		case 'media':
			var resume = global.CKEditor.ajax.getFileResume (id, hash);
			dialog.setValueOf ('iframe', 'embedArea', resume);
			break;
	}
}

global.CKEditor.imageUploadError = function (field, media, error)
{
	var div = $('_CKEDITOR_UPLOAD_ERROR_' + field + '_' + media);
	
	div.innerHTML = error;
	
	div.style.display = '';
};
</script>