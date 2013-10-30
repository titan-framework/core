<?php

if (Api::getHttpRequestMethod () != Api::POST && Api::getHttpRequestMethod () != Api::PUT)
	throw new ApiException (__ ('Invalid URI request method!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::METHOD_NOT_ALLOWED);

if (!Social::singleton ()->socialNetworkExists ('Google'))
	throw new ApiException (__ ('Web application is not capable to register device user! Please, alert support team.'), ApiException::ERROR_SYSTEM, ApiException::SERVICE_UNAVAILABLE, 'Must enable Google Plus at Social Network configuration!');

if (!isset ($_POST ['email']) || trim ($_POST ['email']) == '' || !isset ($_POST ['token']) || trim ($_POST ['token']) == '')
	throw new ApiException (__ ('Invalid parameters (e-mail or token)!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

$email = trim ($_POST ['email']);
$token = Api::decrypt ($_POST ['token'], $_auth->getToken ());

$token = preg_replace ('/[^\x20-\x7f]/i', '', $token);

$url = 'https://www.googleapis.com/oauth2/v1/userinfo?access_token='. $token;

$json = file_get_contents ($url);

if (trim ($json) == '')
	throw new ApiException (__ ('Invalid token!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

$profile = (array) json_decode ($json);

if (!is_array ($profile) || !sizeof ($profile))
	throw new ApiException (__ ('Invalid user attributes!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

$social = Social::singleton ()->getSocialNetwork ('Google');

$social->setProfile ($profile);

print_r ($social);

exit ();

$db = Database::singleton ();

$sql = "SELECT _id, _google FROM _user WHERE _email = :email";

$sth = $db->prepare ($sql);

$sth->bindParam (':email', $email, PDO::PARAM_STR);

$sth->execute ();

$obj = $sth->fetch (PDO::FETCH_OBJ);

// if ()
// if ($obj->_google != )

// https://www.googleapis.com/oauth2/v1/userinfo?access_token=

// 1/aHJhvTK-3pXSHU5v5rq2uVKd7AenWpYuXKvflZFA4Aw



die (print_r ($test));

$array = (array) json_decode (file_get_contents ('php://input'));

if (!is_array ($array) || !sizeof ($array))
	throw new ApiException (__ ('Invalid user attributes!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

$type = $_auth->getRegisterType ();

if (!is_object ($type))
	throw new ApiException (__ ('Invalid user attributes!'), ApiException::ERROR_SYSTEM, ApiException::SERVICE_UNAVAILABLE);



if ($obj)
{
}

/*
[id] =&gt; 100458151633630195862
    [name] =&gt; Camilo Carromeu
    [given_name] =&gt; Camilo
    [family_name] =&gt; Carromeu
    [link] =&gt; https://plus.google.com/100458151633630195862
    [picture] =&gt; https://lh4.googleusercontent.com/-mVJei_yva-s/AAAAAAAAAAI/AAAAAAABV4A/T3yiycEN3lg/photo.jpg
    [gender] =&gt; male
    [locale] =&gt; en

$user = $_auth->getUser ();

if (!is_integer ($user) || !$user)
	throw new ApiException ('Invalid user!', ApiException::ERROR_APP_AUTH, ApiException::UNAUTHORIZED, 'The application API must be configured to client connect as user (add CLIENT-AS-USER context).');

$sth = Database::singleton ()->prepare ("SELECT
											_id AS id, 
											_login AS login, 
											_name AS name, 
											_email AS mail,
											_type AS type,
											_language AS language,
											_timezone AS timezone
										FROM _user WHERE _id = :id LIMIT 1");

$sth->bindParam (':id', $user, PDO::PARAM_INT);

$sth->execute ();

header ('Content-Type: application/json');

echo json_encode ($sth->fetch (PDO::FETCH_OBJ));
*/