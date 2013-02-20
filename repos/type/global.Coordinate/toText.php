<?
$value = $field->getValue ();

if (!is_array ($value) || sizeof ($value) != 3)
	return 'Longitude: 0&deg; 0\' 0", Latitude: 0&deg; 0\' 0", Altitude: 0';

return 'Longitude: '. $value [0] .', Latitude: '. $value [1] .', Altitude: '. $value [2];
?>