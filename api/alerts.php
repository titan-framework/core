<?
if (!Api::isActive ())
	throw new ApiException (__ ('Application API is not active!'));

$auth = Api::singleton ()->getActiveApp ();

if (!is_object ($auth))
	throw new ApiException (__ ('Invalid credentials!'), ApiException::ERROR_APP_AUTH, ApiException::BAD_REQUEST, 'This application is not enable in system!');

$auth->authenticate ();


?>