<?php
ob_start ();
?>
<textarea name="<?= $fieldName ?>" id="<?= $fieldId ?>" rows="10" cols="80"><?= $field->getValue () ?></textarea>
<script language="javascript" type="text/javascript">
CKEDITOR.replace ('<?= $fieldId ?>', {
	language: '<?= CKEditor::getLanguage () ?>',
	toolbar: global.CKEditor.toolbar,
	extraPlugins: global.CKEditor.plugins,
	extraAllowedContent: 'video[*]{*};audio[*]{*};source[*]{*};',
	titanType: 'CKEditor'
});
</script>
<?php
return ob_get_clean ();
?>