<?
if (strpos ($field->getValue (), $field->getPrefix ()) !== 0)
	$value = $field->getPrefix () . $field->getValue ();
else
	$value = $field->getValue ();

return '<a href="'. $value .'" style="'. $field->getStyle () .'" target="_blank">'. ($field->getMaxLength () ? Phrase::limit ($value, $field->getMaxLength ()) : $value) .'</a>';
?>