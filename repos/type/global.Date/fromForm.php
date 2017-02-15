<?php

if (!is_array ($value))
	$value = explode ('-', $value);

if (sizeof ($value) != 3 || !array_sum ($value))
	return array (0, 0, 0);

return $value;
