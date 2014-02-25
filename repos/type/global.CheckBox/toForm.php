<?
$buffer = '<span style="padding: 4px;">[<a href="#" onclick="JavaScript: global.CheckBox.selectAll (\''. $fieldName .'\');" />'. __ ('All') .'</a> | <a href="#" onclick="JavaScript: global.CheckBox.selectNone (\''. $fieldName .'\');" />'. __ ('None') .'</a>]</span><br /><br />';

$items = array ();

foreach ($field->getMapping () as $value => $label)
	$items [] = '<input type="checkbox" name="'. $fieldName .'[]" value="'. $value .'"'. (in_array ($value, $field->getValue ()) ? ' checked="checked"' : '') .' /> '. $label;

return $buffer . implode ('<br />', $items);
?>