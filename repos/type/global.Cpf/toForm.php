<?
return '<input type="text" class="field" style="'. $field->getStyle () .'" name="'. $fieldName .'" id="'. $fieldId .'" maxlength="14" value="'. Cpf::format ($field->getValue ()) .'" onkeypress="JavaScript: return global.Cpf.format (this, event);" onkeyup="JavaScript: global.Cpf.format (this,false);" />';
?>