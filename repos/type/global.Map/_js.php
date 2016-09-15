<script language="javascript" type="text/javascript">
'pandora.Map'.namespace ();

pandora.Map.ajax = <?= class_exists ('xMap', FALSE) ? XOAD_Client::register (new xMap) : 'null' ?>;

<?php
if (Map::usingMap ())
	$coordinates = GoogleMaps::geolocate ();
else
	$coordinates = array (0, 0);
?>
pandora.Map.defaultLatitude = parseFloat ('<?= $coordinates [0] ?>');
pandora.Map.defaultLongitude = parseFloat ('<?= $coordinates [1] ?>');

pandora.Map.edit = function (id, latitude, longitude)
{
	if (!latitude || !longitude)
	{
		if (pandora.Map.defaultLatitude && pandora.Map.defaultLongitude)
		{
			latitude = pandora.Map.defaultLatitude;
			longitude = pandora.Map.defaultLongitude;
		}
		else
		{
			latitude = -20.469703;
			longitude = -54.620086;
		}
	}
	
	var opt = {
		center: new google.maps.LatLng (latitude, longitude),
		zoom: 18,
		mapTypeId: google.maps.MapTypeId.SATELLITE,
		mapTypeControl: false,
		streetViewControl: false
	};
	
	var map = new google.maps.Map ($(id), opt);
	
	var info = new google.maps.InfoWindow ();
	
	var marker = new google.maps.Marker ({
		position: new google.maps.LatLng (latitude, longitude),
		map: map,
		animation: google.maps.Animation.DROP,
		draggable: true
	});
	
	google.maps.event.addListener (marker, 'dragend', function ()
	{
		$(id + '_LATITUDE_').value = marker.getPosition ().lat ().toFixed (6);
		$(id + '_LONGITUDE_').value = marker.getPosition ().lng ().toFixed (6);
	});
	
	var input = /** @type {HTMLInputElement} */($(id + '_SEARCH_'));
	
	map.controls [google.maps.ControlPosition.TOP_LEFT].push (input);
	
	var searchBox = new google.maps.places.SearchBox (/** @type {HTMLInputElement} */(input));
	
	google.maps.event.addListener (searchBox, 'places_changed', function ()
	{
		var places = searchBox.getPlaces ();
		
		if (places.length == 0)
			return;
		
		marker.setPosition (places [0].geometry.location);
		
		map.setCenter (places [0].geometry.location);
		
		$(id + '_LATITUDE_').value = places [0].geometry.location.lat ().toFixed (6);
		$(id + '_LONGITUDE_').value = places [0].geometry.location.lng ().toFixed (6);
	});
}

pandora.Map.view = function (id, latitude, longitude)
{
	var setted = true;
	
	if (!latitude || !longitude)
	{
		setted = false;
		
		if (pandora.Map.defaultLatitude && pandora.Map.defaultLongitude)
		{
			latitude = pandora.Map.defaultLatitude;
			longitude = pandora.Map.defaultLongitude;
		}
		else
		{
			latitude = -20.469703;
			longitude = -54.620086;
		}
	}
	
	var opt = {
		center: new google.maps.LatLng (latitude, longitude),
		zoom: 18,
		mapTypeId: google.maps.MapTypeId.SATELLITE,
		mapTypeControl: false,
		streetViewControl: false
	};
	
	var map = new google.maps.Map ($(id), opt);
	
	map.setTilt (45);
	
	if (setted)
	{
		var info = new google.maps.InfoWindow ();
		
		var marker = new google.maps.Marker ({
			position: new google.maps.LatLng (latitude, longitude),
			map: map,
			animation: google.maps.Animation.DROP,
			clickable: false
		});
	}
}
</script>