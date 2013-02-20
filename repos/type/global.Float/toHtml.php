<?
if ($field->getStyle () != '')
	return '<span style="'. $field->getStyle () .'">'. number_format ($field->getValue (), $field->getPrecision (), ',', '.') .'</span>';

return number_format ($field->getValue (), $field->getPrecision (), ',', '.');
?>