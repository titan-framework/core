<?
if (isset ($_POST['selectTypes']))
	$typeSelected = $_POST['selectTypes'];
else
	$typeSelected = isset ($_GET['itemId']) ? $_GET['itemId'] : '';

$arraySystem = array ();
$arrayType = array ();
$arrayRelation = array ();

$security = Security::singleton ();

while ($userType = $security->getUserType ())
	$arrayType [$userType->getName ()] = $userType->getLabel ();

$db = Database::singleton ();

$sql = "SELECT _id, _name FROM _group WHERE 1 = 1";

if ($typeSelected)
	$sql .= " AND _id NOT IN (SELECT _group FROM _type_group WHERE _type = '". $typeSelected ."')";

if (isset ($_POST['letter']) && trim ($_POST['letter']) != '')
	$sql .= " AND _name ILIKE '". $_POST['letter'] ."%'";

$sth = $db->prepare ($sql);

$sth->execute ();

while ($obj = $sth->fetch (PDO::FETCH_OBJ))
	$arraySystem [$obj->_id] = $obj->_name;


if ($typeSelected)
{
	$sth = $db->prepare ("	SELECT _group._id, _group._name 
							FROM _group 
							LEFT JOIN _type_group ON _type_group._group = _group._id
							WHERE _type_group._type = '". $typeSelected ."'");
	
	$sth->execute ();
	
	while ($obj = $sth->fetch (PDO::FETCH_OBJ))
		$arrayRelation [$obj->_id] = $obj->_name;
}

$form =& Form::singleton ('type.xml');

$menu =& Menu::singleton ();
$menu->addJavaScript (__ ('Save'), 'titan.php?target=loadFile&file=interface/menu/save.png', "saveRelation ();");
$menu->add ($form->goToAction ('cancel')->getName (), __ ('List Groups'), 0, $section->getName (), 'titan.php?target=loadFile&file=interface/menu/list.png');
?>