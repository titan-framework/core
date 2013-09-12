<?
$items = array ();

foreach ($field->getMapping () as $value => $label)
	$items [] = '<input type="checkbox" name="'. $fieldName .'[]" value="'. $value .'"'. (in_array ($value, $field->getValue ()) ? ' checked="checked"' : '') .' /> '. $label;

return implode ('<br />', $items);
?>