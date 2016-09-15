<?php
$array = $field->getValue ();

return $array [2] . str_pad ($array [1], 2, '0', STR_PAD_LEFT) . str_pad ($array [0], 2, '0', STR_PAD_LEFT);
?>