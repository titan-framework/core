<?php
if (strlen ($value) != 10)
	return NULL;

$array = explode ('/', $value);

return $array [2] .'-'. $array [0] .'-'. $array [1];
?>