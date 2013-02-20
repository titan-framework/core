<?
return '<input type="text" class="field" style="'. $field->getStyle () .'" name="'. $fieldName .'" id="'. $fieldId .'" maxlength="15" value="'. Rga::format ($field->getValue ()) .'" onkeypress="JavaScript: return global.Rga.format (this, event);" onkeyup="JavaScript: global.Rga.format (this,false);" />';
?>