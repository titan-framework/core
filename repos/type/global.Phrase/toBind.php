<?php
if ($field->isUnique () && $field->isEmpty ())
	return NULL;

return $field->getValue ();
?>