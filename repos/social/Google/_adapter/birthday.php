<?
$array = explode ('-', $value);

if ($array [0] == '0000')
	$array [0] = date ('Y');

return $array [0] .'-'. $array [1] .'-'. $array [2];
?>