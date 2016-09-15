<?php
if ($field->isEmpty ())
	return "NULL";

return "'". $field->getValue () ."'";
?>