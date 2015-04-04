<?
ob_start ();
?>
<textarea name="<?= $fieldName ?>" id="<?= $fieldId ?>" rows="10" cols="80"><?= $field->getValue () ?></textarea>
<script language="javascript" type="text/javascript">
CKEDITOR.replace ('<?= $fieldId ?>', {
	language: '<?= Fck::getLanguage () ?>',
	toolbar: global.Fck.toolbar,
	extraPlugins: global.Fck.plugins,
	extraAllowedContent: 'video[*]{*};audio[*]{*};source[*]{*};',
	baseFloatZIndex: 9900,
	titanType: 'Fck',
	titanPublic: <?= $field->isPublic () ? 'true' : 'false' ?>,
	titanOwnerOnly: <?= $field->ownerOnly () ? 'true' : 'false' ?>
});
</script>
<?
return ob_get_clean ();
?>