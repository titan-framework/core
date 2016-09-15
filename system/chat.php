<?php
$instance = Instance::singleton ();

require_once $instance->getCorePath () .'extra/freeChat/src/phpfreechat.class.php';

$params ['serverid'] = md5 ('_TITAN_FREECHAT_'. Security::singleton ()->getHash () .'_');

$params ['nick'] = User::singleton ()->getName ();

$params ['title'] = $instance->getName ();

$params ['height'] = '150px';

$params ['language'] = 'pt_BR';

$params ['output_encoding'] = 'UTF-8';

$params ['data_public_url'] = 'titan.php?target=loadFile&amp;file=extra/freeChat/data/public';

$params ['theme_default_url'] = 'titan.php?target=loadFile&amp;file=extra/freeChat/themes';

// $params ['debug'] = TRUE;

if (!file_exists ($instance->getCachePath () .'freechat') && !mkdir ($instance->getCachePath () .'freechat'))
	throw new Exception ('Impossível criar pasta de cache ['. $instance->getCachePath () .'freechat]');

$privateData = $instance->getCachePath () .'freechat/private';

if (!file_exists ($privateData) && !mkdir ($privateData))
	throw new Exception ('Impossível criar pasta de cache ['. $privateData .']');

$params ['data_private_path'] = $privateData;

$groups = User::singleton ()->getChatRooms ();

if (!in_array ('Geral', $groups))
	array_unshift ($groups, 'Geral');

$params ['frozen_channels'] = $groups;

$params ['channels'] = $groups;

$params ['connect_at_startup'] = FALSE;

$params ['start_minimized'] = FALSE;

$params ['displaytabclosebutton'] = FALSE;

$chat = new phpFreeChat ($params);
?>