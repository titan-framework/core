<?php
require_once Instance::singleton ()->getCorePath () .'extra/htmlPurifier/HTMLPurifier.standalone.php';

$itemId = isset ($_POST['itemId']) ? $_POST['itemId'] : $itemId;

try
{
	if (!sizeof ($_POST) && !sizeof ($_FILES))
		throw new Exception ();

	include $action->getFullPathTo (Action::COMMIT);

	$message->save ();

	header ('Location: '. $_SERVER['PHP_SELF'] .'?target=body&toSection='. $section->getName () .'&toAction='. $action->getName () .'&itemId='. $itemId);

	exit ();
}
catch (PDOException $e)
{
	$message->addWarning ($e->getMessage ());
}
catch (Exception $e)
{
	$message->addWarning ($e->getMessage ());
}

define ('XOAD_AUTOHANDLE', true);

require_once Instance::singleton ()->getCorePath () .'class/Xoad.php';

if (file_exists ($section->getCompPath () .'_ajax.php'))
	include_once $section->getCompPath () .'_ajax.php';
else
	require_once Instance::singleton ()->getCorePath () .'class/Ajax.php';

$allow = array ('Xoad', 'Ajax');

foreach (Instance::singleton ()->getTypes () as $type => $path)
{
	if (!file_exists ($path .'_ajax.php'))
		continue;

	require_once $path . '_ajax.php';

	$allow [] = 'x'. $type;
}

require_once Instance::singleton ()->getCorePath () .'xoad/xoad.php';

XOAD_Server::allowClasses ($allow);

if (XOAD_Server::runServer ())
	exit ();

require_once Instance::singleton ()->getCorePath () .'assembly/menu.php';

require_once Instance::singleton ()->getCorePath () .'assembly/breadcrumb.php';

require_once Instance::singleton ()->getCorePath () .'assembly/section.php';

include $instance->getCorePath () .'output/body.php';
?>
