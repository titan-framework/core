<?
return '<input type="text" class="field" style="'. $field->getStyle () .'" name="'. $fieldName .'" id="'. $fieldId .'" value="'. str_replace ('"', "'", $field->getValue ()) .'" '. ($field->getMaxLength () ? ' maxlength="'. $field->getMaxLength () .'"' : '') .' /> <a class="fieldLimit" rel="protolimit['. $fieldId .'=140]">140</a>]';
?>