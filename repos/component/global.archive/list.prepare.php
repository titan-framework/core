<?
$sql = "SELECT *,
		to_char(_create_date, 'DD-MM-YYYY HH24:MI:SS') AS _create_date
		FROM _file
		ORDER BY _name";

$db = Database::singleton ();

$sth = $db->prepare ($sql);

$sth->execute ();

$menu =& Menu::singleton ();
$menu->addJavaScript (__ ('Delete Selected'), 'titan.php?target=loadFile&file=interface/menu/delete.png', "deleteMessage ();");
?>