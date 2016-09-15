<?php
ob_start ();
?>
<input type="text" class="field" style="width: 100px; margin: 3px 10px 3px 0px;" readonly="readonly" name="<?= $fieldName ?>" id="<?= $fieldId ?>" value="<?= $field->getValue () ?>" />
<input type="range" min="<?= $field->getMinimum () ?>" max="<?= $field->getMaximum () ?>" value="<?= $field->getValue () ?>" step="<?= $field->getStep () ?>" style="<?= $field->getStyle () ?>" id="<?= $fieldId ?>_SLIDER_" oninput="JavaScript: $('<?= $fieldId ?>').value = this.value;" />
<?php
echo $field->getTip () != '' ? '<div class="fieldTip">'. $field->getTip () .'</div>' : '';

return ob_get_clean ();
?>