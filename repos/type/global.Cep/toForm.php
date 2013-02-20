<?
return '<input type="text" class="field" style="'. $field->getStyle () .'" name="'. $fieldName .'" id="'. $fieldId .'" maxlength="10" value="'. Cep::format ($field->getValue ()) .'" onkeypress="JavaScript: return global.Cep.format (this, event);" onkeyup="JavaScript: global.Cep.format (this,false);" />';
?>