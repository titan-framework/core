<?php
return '<input type="text" class="field" style="'. $field->getStyle () .'" name="'. $fieldName .'" id="'. $fieldId .'" value="'. $field->getValue () .'" '. ($field->getMaxLength () ? ' maxlength="'. $field->getMaxLength () .'"' : '') .' onkeypress="JavaScript: return global.Email.format (this, event);" onkeyup="JavaScript: global.Email.format (this,false);" onfocus="JavaScript: global.Email.saveOriginalColor (this);" onblur="JavaScript: global.Email.setOriginalColor (this);" />'. ($field->getMaxLength () ? '<a class="fieldLimit" rel="protolimit['. $fieldId .'='. $field->getMaxLength () .']">'. $field->getMaxLength () .'</a>' : '') . ($field->getTip () != '' ? '<div class="fieldTip">'. $field->getTip () .'</div>' : '');
?>