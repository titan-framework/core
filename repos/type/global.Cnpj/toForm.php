<?php
return '<input type="text" class="field" style="'. $field->getStyle () .'" name="'. $fieldName .'" id="'. $fieldId .'" maxlength="19" value="'. Cnpj::format ($field->getValue ()) .'" onkeypress="JavaScript: return global.Cnpj.format (this,event);" onkeyup="JavaScript: global.Cnpj.format (this,false);" />';
?>