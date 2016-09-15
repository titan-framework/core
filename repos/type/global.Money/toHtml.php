<?php
if (!$field->getValue ())
	return $field->getCurrency () .' '.number_format (0 , $field->getPrecision() , ',', '.');

return $field->getCurrency () .' '. number_format ($field->getValue (), $field->getPrecision() , ',', '.');
?>