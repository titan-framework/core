<?php
return '<input type="text" class="field" style="'. $field->getStyle () .'" name="'. $fieldName .'" id="'. $fieldId .'" value="'. str_replace ('"', "'", $field->getValue ()) .'" '. ($field->getMaxLength () ? ' maxlength="'. $field->getMaxLength () .'"' : '') .' />'. ($field->getMaxLength () ? '<a class="fieldLimit" rel="protolimit['. $fieldId .'='. $field->getMaxLength () .']">'. $field->getMaxLength () .'</a>' : '') . ($field->getTip () != '' ? '<div class="fieldTip">'. $field->getTip () .'</div>' : '');
?>