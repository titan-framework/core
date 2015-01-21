<?
// if (!User::singleton ()->isLogged ())
//	throw new Exception (__ ('Attention! Probably attack detected. Access Denied!'));

if (!isset ($_GET ['note']) || !is_numeric ($_GET ['note']) || !(int) $_GET ['note'])
	throw new Exception (__ ('Error! Data losted.'));

$note = (int) $_GET ['note'];

// if (!User::singleton ()->isRegistered ('_note', '_id', $note))
//	throw new Exception (__ ('Attention! Probably attack detected. Access Denied!'));

$db = Database::singleton ();

$sql = "SELECT n.*, u._name, EXTRACT (EPOCH FROM n._change) AS _change 
		FROM _note n
		JOIN _user u ON u._id = n._user
		WHERE n._id = :id AND n._deleted = B'0'";

$sth = $db->prepare ($sql);

$sth->bindParam (':id', $note, PDO::PARAM_INT);

$sth->execute ();

$obj = $sth->fetch (PDO::FETCH_OBJ);

if (!$obj)
	throw new Exception (__ ('Error! Invalid note.'));

$title = $obj->_title;
$text = $obj->_note;
$user = $obj->_name;
$date = $obj->_change;

$noteCoordinate = $obj->_longitude .','. $obj->_latitude .','. $obj->_altitude;

$sql = "SELECT m.*, c._mimetype, EXTRACT (EPOCH FROM m._date) AS _date 
		FROM _note_media m
		JOIN _note n ON n._id = m._note
		JOIN _cloud c ON c._id = m._file
		WHERE n._id = :id AND n._deleted = B'0' AND m._deleted = B'0' AND c._deleted = B'0' AND c._ready = B'1'";

$sth = $db->prepare ($sql);

$sth->bindParam (':id', $note, PDO::PARAM_INT);

$sth->execute ();

$types = array ('IMAGE' => __ ('Photo taken on'), 'VIDEO' => __ ('Video recorded on'), 'AUDIO' => __ ('Audio recorded on'));

$colors = array ('7dff0000', '7f00ffff', 'ff5b00ab', 'ff0000ff', 'ff00ffff', 'c917ab22', 'AA0000ff', '801e4cb5');

header ('Content-Type: application/vnd.google-earth.kml+xml');
header ('Content-Disposition: inline; filename=tracking_'. randomHash (10) .'.kml;');

echo '<?xml version="1.0" encoding="utf-8"?>';
?>
<kml xmlns="http://www.opengis.net/kml/2.2">
	<Document>
		<name><?= $title ?></name>
		<description><?= $text ?></description>
		<Style id="line">
			<LineStyle>
				<color><?= $colors [array_rand ($colors)] ?></color>
				<colorMode>normal</colorMode>
				<width>3</width>
			</LineStyle>
		</Style>
		<Placemark>
			<name><?= $title ?></name>
			<description>
				<![CDATA[
				<?= __ ('Noted by <b>[1]</b> on <b>[2]</b>.', $user, strftime ('%x %X', $date)) ?>
				<br /><br />
				<?= $text ?>
				]]>
			</description>
			<Point>
				<coordinates><?= $noteCoordinate ?></coordinates>
			</Point>
		</Placemark>
		<?
		while ($obj = $sth->fetch (PDO::FETCH_OBJ))
		{
			$coordinate = $obj->_longitude .','. $obj->_latitude .','. $obj->_altitude;
			
			$coordinates [] = $coordinate;
			?>
			<Placemark>
				<name><?= $types [$obj->_type] .' '. strftime ('%x %X', $obj->_date) ?></name>
				<description>
					<![CDATA[
						<?
						switch ($obj->_type)
						{
							case 'IMAGE':
								?>
								<a href="<?= Instance::singleton ()->getUrl () .'titan.php?target=tScript&type=CloudFile&file=open&fileId='. $obj->_file ?>" target="_blank">
									<img src="<?= Instance::singleton ()->getUrl () .'titan.php?target=tScript&type=CloudFile&file=thumbnail&fileId='. $obj->_file .'&width=300' ?>" />
								</a>
								<?
								break;
							
							case 'VIDEO':
								?>
								<video width="320" height="240" controls="controls">
									<source src="<?= Instance::singleton ()->getUrl () .'titan.php?target=tScript&type=CloudFile&file=play&fileId='. $obj->_file ?>"  type="<?= $obj->_mimetype ?>" />
									<a href="<?= Instance::singleton ()->getUrl () .'titan.php?target=tScript&type=CloudFile&file=play&fileId='. $obj->_file ?>" target="_blank" title="<?= __ ('Play') ?>">
										<img src="<?= Instance::singleton ()->getUrl () ?>titan.php?target=tResource&type=Note&file=play.png" border="0" alt="<?= __ ('Play') ?>" />
									</a>
								</video>
								<?
								break;
							
							case 'AUDIO':
								?>
								<audio controls="controls">
									<source src="<?= Instance::singleton ()->getUrl () .'titan.php?target=tScript&type=CloudFile&file=play&fileId='. $obj->_file ?>"  type="<?= $obj->_mimetype ?>" />
									<a href="<?= Instance::singleton ()->getUrl () .'titan.php?target=tScript&type=CloudFile&file=open&fileId='. $obj->_file ?>" target="_blank" title="<?= __ ('Play') ?>">
										<img src="<?= Instance::singleton ()->getUrl () ?>titan.php?target=tResource&type=Note&file=play.png" border="0" alt="<?= __ ('Play') ?>" />
									</a>
								</audio>
								<?
								break;
						}
						?>
					]]>
				</description>
				<Point>
					<coordinates> <?= $coordinate ?> </coordinates>
				</Point>
			</Placemark>
			<?
		}
		
		foreach ($coordinates as $trash => $coordinate)
		{
			?>
			<Placemark>
				<styleUrl>#line</styleUrl>
				<LineString>
					<coordinates>
						<?= $noteCoordinate ?>
						<?= $coordinate ?>
					</coordinates>
				</LineString>
			</Placemark>
			<?
		}
		?>
	</Document>
</kml>