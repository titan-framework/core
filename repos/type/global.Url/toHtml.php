<?
return '<a href="'. $field->getValue () .'" style="'. $field->getStyle () .'" target="_blank">'. ($field->getMaxLength () ? String::limit ($field->getValue (), $field->getMaxLength ()) : $field->getValue ()) .'</a>';
?>