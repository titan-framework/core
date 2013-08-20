<?
if (!Api::isActive ())
	throw new ApiException (__ ('Application API is not active!'));

$auth = Api::singleton ()->getActiveApp ();

if (!is_object ($auth))
	throw new ApiException (__ ('Invalid credentials!'), ApiException::ERROR_APP_AUTH, ApiException::BAD_REQUEST, 'This application is not enable in system!');

if (!$auth->hasContext ('CLIENT-AS-USER'))
	throw new ApiException (__ ('Application cannot access this service!'), ApiException::ERROR_APP_AUTH, ApiException::BAD_REQUEST, 'To access this service ['. basename (__FILE__, '.php') .'] is necessary add CLIENT-AS-USER context to application configuration.');

$auth->authenticate ();

$user = $auth->getUser ();

if (!is_integer ($user) || !$user)
	throw new ApiException ('Invalid user!', ApiException::ERROR_APP_AUTH, ApiException::UNAUTHORIZED, 'The application API must be configured to client connect as user (add CLIENT-AS-USER context).');

$sth = Database::singleton ()->prepare ("SELECT _name AS name, _email AS mail FROM _user WHERE _id = :id LIMIT 1");

$sth->bindParam (':id', $user, PDO::PARAM_INT);

$sth->execute ();

header ('Content-Type: application/json');

echo json_encode ($sth->fetch (PDO::FETCH_OBJ));
?>