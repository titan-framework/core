<?
ob_start ();
?>
<input id="<?= $fieldId ?>_SEARCH_" class="tMapSearchBox" type="text" placeholder="<?= __ ('Search') ?>" />
<div id="<?= $fieldId ?>" style="margin: 0px; border: #CCC 2px solid; width: 100%; height: 400px;"></div>
<input type="hidden" id="<?= $fieldId ?>_LATITUDE_" name="<?= $fieldName ?>[]" value="<?= number_format ($field->getLatitude (), 6, '.', '') ?>" />
<input type="hidden" id="<?= $fieldId ?>_LONGITUDE_" name="<?= $fieldName ?>[]" value="<?= number_format ($field->getLongitude (), 6, '.', '') ?>" />
<script language="javascript" type="text/javascript">
pandora.Map.edit ('<?= $fieldId ?>', <?= number_format ($field->getLatitude (), 6, '.', '') ?>, <?= number_format ($field->getLongitude (), 6, '.', '') ?>)
</script>
<?
return ob_get_clean ();
?>