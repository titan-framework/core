<?
if (!User::singleton ()->isLogged ())
	throw new Exception (__ ('Attention! Probably attack detected. Access Denied!'));

if (!isset ($_GET['hash']) || trim ($_GET['hash']) == '' || !array_key_exists ('_TERM_REGISTER_', $_SESSION) || !is_array ($_SESSION ['_TERM_REGISTER_']) || !array_key_exists ($_GET ['hash'], $_SESSION ['_TERM_REGISTER_']) || !is_array ($_SESSION ['_TERM_REGISTER_'][$_GET ['hash']]))
	throw new Exception (__ ('Error! Data losted.'));

$_hash = $_GET ['hash'];

$control = array (	'_RELATION_' => '_relation',
					'_ID_' => '_id',
					'_FATHER_' => '_item',
					'_VERSION_' => '_version',
					'_TEMPLATE_' => '_template',
					'_LABEL_' => '_label');

$array  = $_SESSION ['_TERM_REGISTER_'][$_hash];

foreach ($control as $key => $var)
{
	if (!isset ($array [$key]))
		throw new Exception (__ ('Error! Data losted.'));
	
	$$var = $array [$key];
}

$_doc = new DocumentForm ($_template);

$_doc->load ($_relation, $_id, $_item, $_version);

$aux = array ();
while ($field = $_doc->getField ())
	if (is_object ($field) && get_class ($field) == 'File')
		$aux [] = intval ($field->getValue ());

$files = array ();

if (sizeof ($aux))
{
	$sql = "SELECT _id, _mimetype FROM _file WHERE _id IN (". implode (",", $aux) .")";
	
	$sth = Database::singleton ()->prepare ($sql);
	
	$sth->execute ();
	
	while ($file = $sth->fetch (PDO::FETCH_OBJ))
		$files [$file->_id] = $file->_mimetype;
}

$sth = Database::singleton ()->prepare ("SELECT to_char(_create, 'DD-MM-YYYY HH24:MI:SS') AS create, _file, _hash, _validate FROM ". $_relation ." WHERE _id = '". $_id ."' AND _relation = '". $_item ."' AND _version = '". $_version ."'");

$sth->execute ();

$obj = $sth->fetch (PDO::FETCH_OBJ);

$_file = $obj->_file;

$_validate = (int) $obj->_validate ? TRUE : FALSE;

$_path = Archive::singleton ()->getDataPath () . 'term_'. str_pad ($_file, 7, '0', STR_PAD_LEFT);

if (!is_null ($obj->_file) && !is_null ($obj->_hash) && file_exists ($_path))
{
	if (md5_file ($_path) != trim ($obj->_hash))
		throw new Exception (__ ('The file [[1]] was unduly altered or corrupted!', $_path, md5_file ($_path), $obj->_hash));
	
	$binary = fopen ($_path, 'rb');
	
	$buffer = fread ($binary, filesize ($_path));
	
	fclose ($binary);
	
	header ('Content-Type: application/pdf');
	header ('Content-Disposition: inline; filename='. fileName ($_label));
	
	echo $buffer;
	
	exit ();
}

require Instance::singleton ()->getCorePath () .'extra/QRCode/qrlib.php';

$_qr = QR_CACHE_DIR . $_hash .'.png';

QRcode::png (Instance::singleton ()->getUrl () .'titan.php?target=tScript&type=Document&file=v&c='. $_file .'&a='. Document::genAuth ($_hash), $_qr, 'Q', 4, 0);

define ('FPDF_FONTPATH', $instance->getCorePath () .'extra/fonts/');

require_once $instance->getCorePath () .'extra/fpdf.php';

$_create = $obj->create;

require $_template .'.php';

@unlink ($_qr);

$pdf->Output ($_path, 'F');

try
{
	Database::singleton ()->exec ("UPDATE ". $_relation ." SET _hash = '". md5_file ($_path) ."', _auth = '". Document::genAuth ($_hash) ."' WHERE _id = '". $_id ."' AND _relation = '". $_item ."' AND _version = '". $_version ."'");
}
catch (PDOException $e)
{
	toLog ($e->getMessage ());
	
	@unlink ($_path);
}

header ('Pragma: public');

if (isset ($_SERVER ['HTTP_USER_AGENT']) && strpos ($_SERVER ['HTTP_USER_AGENT'], 'MSIE'))
	$pdf->Output (fileName ($_label) .'.pdf', 'D');
else
	$pdf->Output (fileName ($_label) .'.pdf', 'I');

exit ();
?>