<?
return '<input type="text" class="field" style="'. $field->getStyle () .'" name="'. $fieldName .'" id="'. $fieldId .'" value="'. $field->getValue () .'" onkeypress="JavaScript: return global.Integer.format (this, event);" onkeyup="JavaScript: global.Integer.format (this, false);" />' . ($field->getTip () != '' ? '<div class="fieldTip">'. $field->getTip () .'</div>' : '');
?>