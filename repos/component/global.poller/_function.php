<?
function colors ($key = FALSE)
{
	$colors = array ('FFD088', 'FF9E9C', 'DEAEDE', '9CC7DE', 'C6AE9C', '891234', '567891', '9CC79C', '234567', 'D6E7D6');
	
	if ($key === FALSE)
		return array_rand ($colors);
	
	return $colors [$key % sizeof ($colors)];
}
?>