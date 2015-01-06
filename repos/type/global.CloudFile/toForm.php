<? ob_start () ?>
<input type="hidden" name="<?= $fieldName ?>" id="<?= $fieldId ?>_real_id" value="<?= $field->getValue () ?>" />
<div class="borderFile" style="float: left; background: none; border-width: 0px;">
	<div id="<?= $fieldId ?>_selected" style="display: none; position: relative; width: 343px; height: 106px; border: #CCCCCC 1px solid; background-color: #FFF;"></div>
	<div id="<?= $fieldId ?>_upload" style="display: block; position: relative; width: 343px; height: 106px; border: #990000 1px solid;">
		<iframe class="iframeFile" src="titan.php?target=tScript&type=CloudFile&file=upload&fieldId=<?= $fieldId ?>&auth=1" border="0"></iframe>
	</div>
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