<?
if (!Api::isActive ())
	throw new ApiException ('Application API is not active!');

$auth = Api::singleton ()->getActiveApp ();

$auth->authenticate ();

$user = $auth->getUser ();

if (!is_integer ($user) || !$user)
	throw new ApiException ('Invalid user! Do you are sure the application API is configured to client connect as user (CLIENT-AS-USER)?', ApiException::UNAUTHORIZED);

$sth = Database::singleton ()->prepare ("SELECT _name AS name, _email AS mail FROM _user WHERE _id = :id LIMIT 1");

$sth->bindParam (':id', $user, PDO::PARAM_INT);

$sth->execute ();

header ('Content-Type: application/json');

echo json_encode ($sth->fetch (PDO::FETCH_OBJ));
?>