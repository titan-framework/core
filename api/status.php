<?php

if (Api::getHttpRequestMethod () != Api::GET)
	throw new ApiException (__ ('Invalid URI request method!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::METHOD_NOT_ALLOWED);

$instance = Instance::singleton ();
$version = VersionHelper::singleton ();

$out = (object) [
	'name' => $instance->getName (),
	'url' => $instance->getUrl (),
	'description' => $instance->getDescription (),
	'author' => $instance->getAuthor (),
	'email' => $instance->getEmail (),
	'debug' => $instance->onDebugMode () ? 'yes' : 'no',
	'timezone' => $instance->getTimeZone (),
	'languages' => implode (',', $instance->getLanguages ()),
	'version' => (object) [
		'instance' => $version->usingAutoDeploy () ? $version->getAppRelease () .'/'. $version->getAppEnvironment () .' (released in '. $version->getAppDate () .' by '. $version->getAppAuthor () .')' : '',
		'titan' => $version->getTitanRelease ()
	]
];

echo json_encode ($out);
