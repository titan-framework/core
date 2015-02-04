<? ob_start () ?>
<input type="hidden" name="<?= $fieldName ?>" id="_CLOUD_HIDDEN_<?= $fieldId ?>" value="<?= $field->getValue () ?>" />
<div class="globalCloudFileError" id="_CLOUD_ERROR_<?= $fieldId ?>" style="display: none;"></div>
<div class="globalCloudFileUploaded" id="_CLOUD_UPLOADED_<?= $fieldId ?>" style="display: none;"></div>
<div class="globalCloudFileUpload" id="_CLOUD_UPLOAD_<?= $fieldId ?>" style="display: block;">
	<iframe class="globalCloudFileIframe" src="titan.php?target=tScript&type=CloudFile&file=upload&field=<?= $fieldId ?>&auth=1" border="0"></iframe>
</div>
<?= $field->getTip () != '' ? '<div class="fieldTip" style="float: left; padding: 6px 8px;">'. $field->getTip () .'</div>' : '' ?>
<script language="javascript" type="text/javascript">
<?
if ((int) $field->getValue ())
{
	?>
	global.CloudFile.load (<?= $field->getValue () ?>, '<?= $fieldId ?>');
	<?
}
?>
global.CloudFile.addFilter ('<?= $fieldId ?>', '<?= $field->getFilter () ?>');
</script>
<? return ob_get_clean () ?>