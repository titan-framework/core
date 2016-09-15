<?php
require_once Instance::singleton ()->getCorePath () .'extra/htmlPurifier/HTMLPurifier.standalone.php';

$itemId = isset ($_POST['itemId']) ? $_POST['itemId'] : $itemId;

try
{
	if (!sizeof ($_POST) && !sizeof ($_FILES))
		throw new Exception ('Não há dados a serem salvos na base de dados!');
	
	include $action->getFullPathTo (Action::COMMIT);
	
	$message->save ();
	
	// header ('Location: '. $_SERVER['PHP_SELF'] .'?target=inPlace&toSection='. $section->getName () .'&toAction='. $action->getName () .'&itemId='. $itemId);
	?>
	<html><body onload="JavaScript: parent.location.reload ();"></body></html>
	<?php
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

require_once Instance::singleton ()->getCorePath () .'extra/fckEditor/fckeditor.php';

define ('XOAD_AUTOHANDLE', true);

require_once Instance::singleton ()->getCorePath () .'class/Xoad.php';

if (file_exists (Instance::singleton ()->getReposPath () .'component/'. $section->getComponent () .'/_ajax.php'))
	include_once Instance::singleton ()->getReposPath () .'component/'. $section->getComponent () .'/_ajax.php';
else
	require_once Instance::singleton ()->getCorePath () .'class/Ajax.php';

require_once Instance::singleton ()->getCorePath () .'xoad/xoad.php';

XOAD_Server::allowClasses ('Xoad', 'Ajax');

if (XOAD_Server::runServer ())
	exit ();

require_once Instance::singleton ()->getCorePath () .'assembly/section.php';
	
include $instance->getCorePath () .'output/inPlace.php';
?>