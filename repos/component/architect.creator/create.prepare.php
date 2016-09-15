<?php
$section = Business::singleton ()->getSection (Section::TCURRENT);

$dbUser = trim ($section->getDirective ('_CREATE_DB_USER_'));
$dbComm = trim ($section->getDirective ('_CREATE_DB_COMMAND_'));

if ($dbUser != '' && $dbComm != '')
	$dbFlag = TRUE;
else
	$dbFlag = FALSE;
?>