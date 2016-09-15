<?php
if ($field->isEmpty ())
	return NULL;

$array = $field->getValue ();

$time = $field->getTime ();

return implode ('-', array_reverse ($array)) . (array_sum ($time) ? ' '. implode (':', $time) : '');
?>