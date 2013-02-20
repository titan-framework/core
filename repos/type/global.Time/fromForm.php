<?
if (is_array ($value) && (sizeof ($value) != 3 || array_sum ($value) < 0))
	return array (-1, -1, -1);

return $value;
?>