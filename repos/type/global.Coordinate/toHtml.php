<?php
$value = $field->getValue ();

if (!is_array ($value) || sizeof ($value) != 3)
	return 'Longitude: 0&deg; 0\' 0", Latitude: 0&deg; 0\' 0", Altitude: 0';

return '<a href="titan.php?target=earth&amp;latitude='. urlencode ($value [1]) .'&amp;longitude='. urlencode ($value [0]) .'&amp;altitude='. $value [2] .'" target="_blank">Longitude: '. $value [0] .', Latitude: '. $value [1] .', Altitude: '. $value [2] .'</a>';
?>