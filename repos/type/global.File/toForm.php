<? ob_start () ?>
<div class="borderFile" style="float: left;">
	<input type="text" class="field" style="float: none; width: 300px; '. $field->getStyle () .'" id="<?= $fieldId ?>" title="Buscar arquivo" autocomplete="off" />
	<img src="titan.php?target=loadFile&amp;file=interface/icon/save.gif" class="icon" border="0" id="img_<?= $fieldId ?>" style="vertical-align: bottom;" title="<?=__ ('Send File')?>" alt="Upload" onclick="JavaScript: global.File.upload ('<?= $fieldId ?>');" />
	<img src="titan.php?target=loadFile&amp;file=interface/icon/grey/delete.gif" class="" border="0" id="del_<?= $fieldId ?>" style="vertical-align: bottom;" title="<?=__ ('Unlink File')?>" alt="Unset" />
	<input type="hidden" name="<?= $fieldName ?>" id="<?= $fieldId ?>_real_id" value="<?= $field->getValue () ?>" />
	<div id="<?= $fieldId ?>_selected" style="display: none; position: relative; width: 343px; height: 106px; border: #CCCCCC 1px solid; margin-top: 2px;"></div>
	<div id="<?= $fieldId ?>_upload" style="display: none; position: relative; width: 343px; height: 106px; border: #990000 1px solid; margin-top: 2px;">
		<iframe class="iframeFile" src="titan.php?target=upload&fieldId=<?= $fieldId ?>" border="0"></iframe>
	</div>
</div>
<?= $field->getTip () != '' ? '<div class="fieldTip" style="float: left; padding: 6px 8px;">'. $field->getTip () .'</div>' : '' ?>
<script language="javascript" type="text/javascript">
<?
if ((int) $field->getValue ())
{
	?>
	global.File.load (<?= $field->getValue () ?>, '<?= $fieldId ?>');
	<?
}
?>
var suggest_<?= $fieldId ?> = actb ('<?= $fieldId ?>', $('<?= $fieldId ?>'), $('<?= $fieldId ?>_real_id'), $('<?= $fieldId ?>_selected'), $('<?= $fieldId ?>_upload'), '<?= $field->getFilter () ?>', <?= $field->ownerOnly () ? '1' : '0' ?>);
global.File.addFilter ('<?= $fieldId ?>', '<?= $field->getFilter () ?>');
</script>
<? return ob_get_clean () ?>