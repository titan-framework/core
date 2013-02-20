<?
if ($field->getStyle () != '')
	return '<span style="'. $field->getStyle () .'">'. ($field->getMaxLength () ? String::limit ($field->getValue (), $field->getMaxLength ()) : $field->getValue ()) .'</span>';

return $field->getMaxLength () ? String::limit ($field->getValue (), $field->getMaxLength ()) : $field->getValue ();
?>