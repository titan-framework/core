<?php

if (!is_array ($value) || sizeof ($value) != 2)
	return array (array (0, 0, 0), array (0, 0, 0));

if (!is_array ($value [0]))
	$value [0] = explode ('-', $value [0]);

if (!is_array ($value [1]))
	$value [1] = explode ('-', $value [1]);

if (sizeof ($value [0]) != 3 || sizeof ($value [1]) != 3)
	return array (array (0, 0, 0), array (0, 0, 0));

return $value;
