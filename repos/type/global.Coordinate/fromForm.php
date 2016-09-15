<?php
if (!is_array ($value) || !sizeof ($value) || array_sum ($value) < 0)
	return array ('', '', 0);

return $value;
?>