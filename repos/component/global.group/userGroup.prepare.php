<?
if (isset ($_POST['selectSystems']))
	$systemSelected = $_POST['selectSystems'];
else
	if (isset ($_GET['itemId']))
		$systemSelected = $_GET['itemId'];
	else
		$systemSelected = 0;

$arrayUser = array ();
$arraySystem = array ();
$arrayRelation = array ();

$db = Database::singleton ();

$sth = $db->prepare ("SELECT _id, _name FROM _group ORDER BY _name");

$sth->execute ();

while ($obj = $sth->fetch (PDO::FETCH_OBJ))
	$arraySystem [$obj->_id] = $obj->_name;

$sql = "SELECT _id, _name, _login FROM _user WHERE _deleted = '0'";

if ($systemSelected)
	$sql .= " AND _id NOT IN (SELECT _user FROM _user_group WHERE _group = '". $systemSelected ."')";

if (isset ($_POST['letter']) && trim ($_POST['letter']) != '')
	$sql .= " AND name ILIKE '". $_POST['letter'] ."%'";

$sql .= " ORDER BY _name";

$sth = $db->prepare ($sql);

$sth->execute ();

while ($obj = $sth->fetch (PDO::FETCH_OBJ))
	$arrayUser [$obj->_id] = $obj->_name .' ('. $obj->_login .')';


if ($systemSelected)
{
	$sth = $db->prepare ("	SELECT _user._id, _user._name, _user._login 
							FROM _user 
							LEFT JOIN _user_group ON _user_group._user = _user._id
							WHERE _user_group._group = '". $systemSelected ."' AND _deleted = '0' ORDER BY _user._name");
	
	$sth->execute ();
	
	while ($obj = $sth->fetch (PDO::FETCH_OBJ))
		$arrayRelation [$obj->_id] = $obj->_name .' ('. $obj->_login .')';
}

$form =& Form::singleton ('user.xml');

$menu =& Menu::singleton ();
$menu->addJavaScript (__ ('Save'), 'titan.php?target=loadFile&file=interface/menu/save.png', "saveRelation ();");
$menu->add ($form->goToAction ('cancel')->getName (), __ ('List Groups'), 0, $section->getName (), 'titan.php?target=loadFile&file=interface/menu/list.png');
?>