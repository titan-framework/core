<?php

$value = $field->getValue ();

if (sizeof ($value) != 2 || $field->isEmpty ())
	return '';

return $field->getTable () .'.'. $field->getColumn () .' >= \''. $value [0][2] .'-'. $value [0][1] .'-'. $value [0][0] .'\' AND '.$field->getTable () .'.'. $field->getColumn () .' <= \''. $value [1][2] .'-'. $value [1][1] .'-'. $value [1][0] .'\'';
