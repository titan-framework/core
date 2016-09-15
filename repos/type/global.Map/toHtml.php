<?php
ob_start ();
?>
<div id="<?= $fieldId ?>" style="margin: 0px; border: #CCC 2px solid; width: 500px; height: 300px;"></div>
<script language="javascript" type="text/javascript">
pandora.Map.view ('<?= $fieldId ?>', <?= number_format ($field->getLatitude (), 6, '.', '') ?>, <?= number_format ($field->getLongitude (), 6, '.', '') ?>)
</script>
<?php
return ob_get_clean ();
?>