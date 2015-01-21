<?
// if (!User::singleton ()->isLogged ())
//	throw new Exception (__ ('Attention! Probably attack detected. Access Denied!'));

if (!isset ($_GET ['note']) || !is_numeric ($_GET ['note']) || !(int) $_GET ['note'])
	throw new Exception (__ ('Error! Data losted.'));

$note = (int) $_GET ['note'];

// if (!User::singleton ()->isRegistered ('_note', '_id', $note))
//	throw new Exception (__ ('Attention! Probably attack detected. Access Denied!'));

$db = Database::singleton ();

$sql = "SELECT _title FROM _note WHERE _id = :id AND _deleted = B'0'";

$sth = $db->prepare ($sql);

$sth->bindParam (':id', $note, PDO::PARAM_INT);

$sth->execute ();

$title = $sth->fetch (PDO::FETCH_COLUMN);

if (is_null ($title))
	throw new Exception (__ ('Error! Invalid note.'));

$sql = "SELECT m.*, EXTRACT (EPOCH FROM m._date) AS _date 
		FROM _note_media m
		JOIN _note n ON n._id = m._note
		JOIN _cloud c ON c._id = m._file
		WHERE n._id = :id AND n._deleted = B'0' AND m._deleted = B'0' AND c._deleted = B'0'";

$sth = $db->prepare ($sql);

$sth->bindParam (':id', $note, PDO::PARAM_INT);

$sth->execute ();

$types = array ('IMAGE' => __ ('Image'), 'VIDEO' => __ ('Video'), 'AUDIO' => __ ('Audio'));

echo '<?xml version="1.0" encoding="utf-8"?>';
?>
<kml xmlns="http://www.opengis.net/kml/2.2">
	<Document>
		<name><?= $title ?></name>
		<?
		while ($obj = $sth->fetch (PDO::FETCH_OBJ))
		{
			?>
			<Placemark>
				<name><?= $types [$obj->_type] .' '. $obj->_code .' ('. strftime ('%x %X', $obj->_date) .')' ?></name>
				<description>
					<![CDATA[
					<img src="<?= Instance::singleton ()->getUrl () .'titan.php?target=tScript&type=CloudFile&file=open&fileId='. $obj->_file ?>" />
					]]>
				</description>
				<Point>
					<coordinates><?= $obj->_latitude ?>,<?= $obj->_longitude ?></coordinates>
				</Point>
			</Placemark>
			<?
		}
		?>
	</Document>
</kml>