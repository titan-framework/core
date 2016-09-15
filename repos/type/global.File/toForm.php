<?php ob_start () ?>
<input type="hidden" name="<?= $fieldName ?>" id="_TITAN_GLOBAL_FILE_HIDDEN_<?= $fieldId ?>" value="<?= $field->getValue () ?>" />
<div class="globalFileError" id="_TITAN_GLOBAL_FILE_ERROR_<?= $fieldId ?>" style="display: none;"></div>
<div class="globalFileUploaded" id="_TITAN_GLOBAL_FILE_UPLOADED_<?= $fieldId ?>" style="display: none;"></div>
<div class="globalFileUpload" id="_TITAN_GLOBAL_FILE_UPLOAD_<?= $fieldId ?>" style="display: block;">
	<iframe class="globalFileIframe" src="titan.php?target=tScript&type=File&file=upload&field=<?= $fieldId ?>&public=<?= $field->isPublic () ? '1' : '0' ?>&auth=1" border="0"></iframe>
	<div class="globalFileArchive" style="display: block;" onclick="JavaScript: global.File.archive ('<?= $fieldId ?>', <?= $field->ownerOnly () ? 'true' : 'false' ?>);" title="<?= __ ('Recovery from archive...') ?>"></div>
</div>
<?= $field->getTip () != '' ? '<div class="fieldTip" style="float: left; padding: 6px 8px;">'. $field->getTip () .'</div>' : '' ?>
<script language="javascript" type="text/javascript">
<?php
if ((int) $field->getValue ())
{
	?>
	global.File.load (<?= $field->getValue () ?>, '<?= $fieldId ?>');
	<?php
}
?>
global.File.addFilter ('<?= $fieldId ?>', '<?= $field->getFilter () ?>');

$('_TITAN_GLOBAL_FILE_HIDDEN_<?= $fieldId ?>').up ('form').addEventListener ('reset', function () {
	global.File.clear ('<?= $fieldId ?>');
	
	<?php
	if ((int) $field->getValue ())
	{
		?>
		global.File.load (<?= $field->getValue () ?>, '<?= $fieldId ?>');
		<?php
	}
	else
	{
		?>
		global.File.upload ('<?= $fieldId ?>');
		<?php
	}
	?>
}, false);
</script>
<?php return ob_get_clean () ?>