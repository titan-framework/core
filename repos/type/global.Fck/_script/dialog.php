<?
if (!isset ($_GET['dialog']))
	throw new Exception ('Link inválido!');

$dialog = $_GET['dialog'];

define ('XOAD_AUTOHANDLE', true);

$instance = Instance::singleton ();

require_once $instance->getCorePath () .'class/Xoad.php';

$allow = array ('Xoad', 'Ajax');

if (file_exists ($path .'_ajax.php'))
{
	require_once $path . '_ajax.php';
	
	$allow [] = 'x'. $type;
}

require_once $instance->getCorePath () .'xoad/xoad.php';

XOAD_Server::allowClasses ($allow);

if (XOAD_Server::runServer ())
	exit ();

include Instance::singleton ()->getCorePath () .'extra/fckEditor/scripts/'. $dialog .'.php';
?>