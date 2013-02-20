<?
if (!isset ($_SESSION['UNIX_NAME']) || !isset ($_SESSION['DBH_'. $_SESSION['UNIX_NAME']]))
	throw new Exception ('Houve perda de variáveis.');

$itemId = $_SESSION['UNIX_NAME'];

$file = 'instance/'. $itemId .'/configure/security.xml';

if (!file_exists ($file))
	throw new Exception ('Arquivos da instância não encontrados! ['. $file .']');

$xml = new Xml ($file);

$userArray = $xml->getArray ();

if (!isset ($userArray ['security-mapping'][0]['user-type']))
	throw new Exception ('A tag &lt;security-mapping&gt;&lt;/security-mapping&gt; não existe no arquivo ['. $file .'].');

$aux = $userArray ['security-mapping'][0]['user-type'];

$userArray = array ();

foreach ($aux as $trash => $value)
	$userArray [$value ['name']] = $value;

$_SESSION['USERS_'. $itemId] = $userArray;

$file = 'instance/'. $itemId .'/configure/business.xml';

if (!file_exists ($file))
	throw new Exception ('Arquivos da instância não encontrados! ['. $file .']');

$xml = new Xml ($file);

$array = $xml->getArray ();

if (!isset ($array ['section-mapping'][0]['section']))
	throw new Exception ('A tag &lt;section-mapping&gt;&lt;/section-mapping&gt; não existe no arquivo ['. $file .'].');

$aux = $array ['section-mapping'][0]['section'];

$array = array ();

foreach ($aux as $trash => $value)
	$array [$value ['name']] = $value;

$_SESSION['SECTIONS_'. $itemId] = $array;

$file = 'instance/'. $itemId .'/configure/ldap.xml';

if (!file_exists ($file))
	throw new Exception ('Arquivos da instância não encontrados! ['. $file .']');

$xml = new Xml ($file);

$ldapArray = $xml->getArray ();

if (!isset ($ldapArray ['ldap-mapping'][0]['ldap']))
	throw new Exception ('A tag &lt;ldap-mapping&gt;&lt;/ldap-mapping&gt; não existe no arquivo ['. $file .'].');

$aux = $ldapArray ['ldap-mapping'][0]['ldap'];

$ldapArray = array ();

foreach ($aux as $trash => $value)
	$ldapArray [$value ['id']] = $value;

$_SESSION['LDAP_'. $itemId] = $ldapArray;

$file = Instance::singleton ()->getReposPath () .'package/br.ufms.cpcx.user/default.xml';

if (!file_exists ($file))
	throw new Exception ('Arquivos do package [br.ufms.cpcx.user] não encontrados! ['. $file .']');

$xml = new Xml ($file);

$fieldArray = $xml->getArray ();

if (!isset ($fieldArray ['form'][0]['field']))
	throw new Exception ('Arquivos do package [br.ufms.cpcx.user/'. $file .'] contêm erro de sintaxe!');

$fieldArray = $fieldArray ['form'][0]['field'];

$file = Instance::singleton ()->getReposPath () .'package/br.ufms.cpcx.user/default.ini';

if (!file_exists ($file))
	throw new Exception ('Arquivos do package [br.ufms.cpcx.user] não encontrados! ['. $file .']');

$defaultArray = parse_ini_file ($file, TRUE);

$menu =& Menu::singleton ();
$menu->addJavaScript ('Adicionar usuário', 'create.png', "showCreateUser ()");
$menu->addJavaScript ('Preview do Sistema', 'view.png', 'openPopup (\'instance/'. $itemId .'/titan.php?target=login\', \'popup_'. $itemId .'\')');
?>
