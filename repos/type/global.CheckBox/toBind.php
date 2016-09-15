<?php
if ($field->isEmpty ())
	return NULL;

return '{ "'. implode ('", "', $field->getValue ()) .'" }';
?>