<?php
if (!$field->getValue ())
	return '&#48;';

return (string) $field->getValue ();
?>