<?
return '<textarea class="field" style="height: 150px; '. $field->getStyle () .'" name="'. $fieldName .'" id="'. $fieldId .'">'. $field->getValue () .'</textarea>'. ($field->getMaxLength () ? '<a class="fieldLimit" rel="protolimit['. $fieldId .'='. $field->getMaxLength () .']">'. $field->getMaxLength () .'</a>' : '');
?>