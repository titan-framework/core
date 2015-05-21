<script type="text/javascript" src="titan.php?target=tResource&type=Note&file=oms.min.js"></script>
<script language="javascript" type="text/javascript">
'global.Note'.namespace ();

global.Note.ajax = <?= class_exists ('xNote', FALSE) ? XOAD_Client::register (new xNote) : 'null' ?>;

global.Note.mapViewControl = new Array ();

global.Note.divForMapIsCreated = false;

global.Note.view = function (id, icon)
{
    alert (id);
}

global.Note.earth = function (id, field, latitude, longitude, title, date, author, description)
{
	var size = getWindowSize ();
	
	var h = size.height - 70;
	var w = size.width - 60;
	
	var source = '<div id="_TITAN_NOTE_MAP_" style="margin: 0px; border: #CCC 2px solid; width: ' + (w - 20) + 'px; height: ' + (h - 50) + 'px;"></div>';
	
	Modalbox.show (source, { title: title + ' (' + date + ') ~ ' + author, width: w, height: h, afterLoad: function () {
		var opt = {
			center: new google.maps.LatLng (parseFloat (latitude), parseFloat (longitude)),
			zoom: 18, 
			mapTypeId: google.maps.MapTypeId.HYBRID
		};
		
		var map = new google.maps.Map ($('_TITAN_NOTE_MAP_'), opt);
		
		var oms = new OverlappingMarkerSpiderfier (map);
		
		var info = new google.maps.InfoWindow ();
		
		oms.addListener ('click', function (marker, event) {
			info.setContent (marker.info);
			info.open (map, marker);
		});
		
		oms.addListener ('spiderfy', function (markers) {
			info.close ();
		});
		
		var icon = {
			url: 'titan.php?target=tResource&type=Note&file=information.png'
		};
		
		var main = new google.maps.Marker ({
			position: new google.maps.LatLng (parseFloat (latitude), parseFloat (longitude)),
			map: map,
			animation: google.maps.Animation.DROP,
			icon: icon
		});
		
		main.info = '<b>' + title + '</b><br />' + date + ' ~ ' + author + '<br /><br />' + description;
		
		oms.addMarker (main);
		
		eval ('var locations = ' + global.Note.ajax.locations (id) + ';');
		
		for (i = 0; i < locations.length; i++)
		{
			var content = '';
			
			switch (locations[i].type)
			{
				case 'IMAGE':
					content = ' <a href="titan.php?target=tScript&type=CloudFile&file=open&fileId=' + locations [i].file + '" target="_blank">\
									<img src="titan.php?target=tScript&type=CloudFile&file=thumbnail&fileId=' + locations [i].file + '&width=200" />\
								</a>';
					
					icon = {
						url: 'titan.php?target=tResource&type=Note&file=photo.png'
					};
					
					break;
				
				case 'VIDEO':
					content = ' <video width="320" height="240" controls="controls" autoplay="autoplay" preload="metadata">\
									<source src="titan.php?target=tScript&type=CloudFile&file=play&fileId=' + locations [i].file + '" type="' + locations [i].mimetype + '" />\
									<a href="titan.php?target=tScript&type=CloudFile&file=play&fileId=' + locations [i].file + '" target="_blank" title="<?= __ ('Play') ?>">\
										<img src="titan.php?target=tResource&type=Note&file=play.png" border="0" alt="<?= __ ('Play') ?>" />\
									</a>\
								</video>';
					
					icon = {
						url: 'titan.php?target=tResource&type=Note&file=video.png'
					};
					
					break;
				
				case 'AUDIO':
					content = ' <audio controls="controls" autoplay="autoplay" preload="metadata">\
									<source src="titan.php?target=tScript&type=CloudFile&file=play&fileId=' + locations [i].file + '" />\
									<a href="titan.php?target=tScript&type=CloudFile&file=open&fileId=' + locations [i].file + '" target="_blank" title="<?= __ ('Play') ?>">\
										<img src="titan.php?target=tResource&type=Note&file=play.png" border="0" alt="<?= __ ('Play') ?>" />\
									</a>\
								</audio>';
					
					icon = {
						url: 'titan.php?target=tResource&type=Note&file=audio.png'
					};
					
					break;
			}
			
			var marker = new google.maps.Marker ({				
				position: new google.maps.LatLng (locations [i].latitude, locations [i].longitude),				
				map: map,
				animation: google.maps.Animation.DROP,
				title: locations [i].date,
				icon: icon
			});
			
			marker.info = content;
			
			oms.addMarker (marker);
			
			var coordinates = [
				new google.maps.LatLng(parseFloat (latitude), parseFloat (longitude)),
				new google.maps.LatLng(locations [i].latitude, locations [i].longitude)
			];
			
			var line = new google.maps.Polyline ({
				path: coordinates,
				geodesic: true,
				strokeColor: '#FFFFFF',
				strokeOpacity: 0.5,
				strokeWeight: 2
			});
			
			line.setMap (map);
		}
		
		map.panBy (1, 1);
	} });
}

global.Note.delete = function (id, icon)
{
    alert (id);
}
</script>