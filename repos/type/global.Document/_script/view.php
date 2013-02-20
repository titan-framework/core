<?
if (!isset ($_GET['c']) || !is_numeric ($_GET['c']) || !((int) $_GET['c']) || !isset ($_GET['a']) || strlen (trim ($_GET['a'])) != 16)
	throw new Exception (__ ('Error! Data losted.'));

$_file = $_GET['c'];
$_auth = $_GET['a'];

$db = Database::singleton ();

$sql = "SELECT table_name AS name, table_schema AS schema FROM information_schema.columns WHERE column_name='_file' AND column_default = 'nextval((''". $db->getSchema () ."._document''::text)::regclass)'";

$stt = $db->prepare ($sql);

$stt->execute ();

$querys = array ();

while ($table = $stt->fetch (PDO::FETCH_OBJ))
{
	$query = $db->query ("SELECT COUNT(*) FROM ". $table->schema .".". $table->name ." WHERE _file = '". $_file ."' AND _auth = '". $_auth ."' AND _validate = B'1' AND _hash IS NOT NULL");
	
	if (!(int) $query->fetchColumn ())
		continue;
	
	$_path = Archive::singleton ()->getDataPath () . 'term_'. str_pad ($_file, 7, '0', STR_PAD_LEFT);
	
	$binary = fopen ($_path, 'rb');
	
	$buffer = fread ($binary, filesize ($_path));
	
	fclose ($binary);
	
	header ('Content-Type: application/pdf');
	header ('Content-Disposition: inline; filename='. date ('Ymdhis') .'.pdf');
	
	echo $buffer;
	
	exit ();
}

throw new Exception (__ ('This file do not exists!'));
?>