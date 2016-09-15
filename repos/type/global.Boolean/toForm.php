<?php
if ($field->isQuestion ())
{
	$aux  = '<input type="radio" name="'. $fieldName .'" id="'. $fieldId .'_YES_" value="1" '. ($field->getValue () ? 'checked="checked"' : '') .' /> '. __ ('Yes') .' &nbsp;&nbsp; ';
	$aux .= '<input type="radio" name="'. $fieldName .'" id="'. $fieldId .'_NO_" value="0" '. ($field->getValue () ? '' : 'checked="checked"') .' /> '. __ ('No');
}
else
{
	$aux  = '<input type="checkbox" name="check_'. $fieldId .'" '. ($field->getValue () ? 'checked="checked"' : '') .' onChange="JavaScript: global.Boolean.alter (\''. $fieldId .'\');">';
	$aux .= '<input type="hidden" name="'. $fieldName .'" id="'. $fieldId .'" value="'. ($field->getValue () ? '1' : '0') .'">';
}

return $aux;
?>