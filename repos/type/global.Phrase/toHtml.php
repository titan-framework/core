<?php
if (is_null ($field->getValue ()) || trim ($field->getValue ()) == '')
	return '&nbsp;';

if ($field->getStyle () != '')
	return '<span style="'. $field->getStyle () .'">'. ($field->getMaxLength () ? Phrase::limit ($field->getValue (), $field->getMaxLength ()) : $field->getValue ()) .'</span>';

return $field->getMaxLength () ? Phrase::limit ($field->getValue (), $field->getMaxLength ()) : $field->getValue ();
?>