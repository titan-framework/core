<?
$maxLength = $field->getMaxLength () - strlen ($field->getPrefix ());

$width = 500 - (strlen ($field->getPrefix ()) * 7);

return '<span style="float: left; width: '. (500 - $width) .'px; font-family: \'Courier New\', Courier, monospace; text-align: right; font-size: 12px; margin-right: 2px; line-height: 20px; vertical-align: middle; color: #000;">'. $field->getPrefix () .'</span><input type="text" class="field" style="width: '. ($width - 2) .'px;'. $field->getStyle () .'" name="'. $fieldName .'" id="'. $fieldId .'" value="'. str_replace (array ('"', "'"), '', substr ($field->getValue (), strlen ($field->getPrefix ()))) .'" '. ($maxLength > 0 ? ' maxlength="'. $maxLength .'"' : '') .' />'. ($maxLength > 0 ? '<a class="fieldLimit" rel="protolimit['. $fieldId .'='. $maxLength .']">'. $maxLength .'</a>' : '') . ($field->getTip () != '' ? '<div class="fieldTip">'. $field->getTip () .'</div>' : '');
?>