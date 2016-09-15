<?php
if (!$field->getValue ())
	return '&#48;';

return (string) number_format ($field->getValue (), 0, '', '.');
?>