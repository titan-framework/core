<?php
return '<input type="text" class="field" style="'. $field->getStyle () .'" name="'. $fieldName .'" id="'. $fieldId .'" value="'. $field->getValue () .'" '. ($field->getMaxLength () ? ' maxlength="'. $field->getMaxLength () .'"' : '') .' />';
?>