<?
$instance = Instance::singleton ();

session_name ($instance->getSession ());

session_start ();

Log::singleton ()->add ('LOGOFF', '', Log::SECURITY, FALSE, TRUE);

$_SESSION = array ();

@session_destroy ();

header ('Location: '. $instance->getLoginUrl () . (isset ($_GET['error']) ? '&error='. $_GET['error'] : '') . (isset ($_GET['message']) ? '&message='. $_GET['message'] : '') . (isset ($_GET['url']) ? '&url='. urlencode ($_GET['url']) : ''));
?>