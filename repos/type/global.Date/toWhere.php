<?php
$value = $field->getValue ();

$day = $value [0];
$month = $value [1];
$year = $value [2];

if (!$year)
	return '';

$days = array (0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

if (!$month)
	return $field->getTable () .'.'. $field->getColumn () .' >= \'1/1/'. $year .'\' AND '.$field->getTable () .'.'. $field->getColumn () .' <= \'31/12/'. $year .'\'';

if (!$day)
	return $field->getTable () .'.'. $field->getColumn () .' >= \'1/'. $month .'/'. $year .'\' AND '.$field->getTable () .'.'. $field->getColumn () .' <= \''. $days [$month] .'/'. $month .'/'. $year .'\'';

return $field->getTable () .'.'. $field->getColumn () .' = \''. $day .'/'. $month .'/'. $year .'\'';
?>