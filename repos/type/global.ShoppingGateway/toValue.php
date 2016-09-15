<?php
if (trim ((string) $field->getValue ()) == '')
	return NULL;

return $field->getValue ();
?>