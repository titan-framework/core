<?php

$value = $field->getValue ();

$day = $value [0];
$month = $value [1];
$year = $value [2];

if (!$year)
	return '';

$days = array (0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

if (!$month)
	return $field->getTable () .'.'. $field->getColumn () .' >= \''. $year .'-1-1\' AND '.$field->getTable () .'.'. $field->getColumn () .' <= \''. $year .'-12-31\'';

if (!$day)
	return $field->getTable () .'.'. $field->getColumn () .' >= \''. $year .'-'. $month .'-1\' AND '.$field->getTable () .'.'. $field->getColumn () .' <= \''. $year .'-'. $month .'-'. $days [$month] .'\'';

return $field->getTable () .'.'. $field->getColumn () .' = \''. $year .'-'. $month .'-'. $day .'\'';
