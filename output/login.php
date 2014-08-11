<?
$instance = Instance::singleton ();

require_once $instance->getCorePath () .'class/AjaxLogon.php';

session_name ($instance->getSession ());

session_start ();

define ('XOAD_AUTOHANDLE', true);

require_once $instance->getCorePath () .'xoad/xoad.php';

XOAD_Server::allowClasses ('AjaxLogon');

if (XOAD_Server::runServer ())
	exit ();

if (!isFirefox ())
{
	header ('Location: titan.php?target=noFirefox');

	exit ();
}

if (isset ($_POST['login']) && isset ($_POST['password']))
{
	$logon = new AjaxLogon;

	if ($logon->logon ($_POST))
	{
		Message::singleton ()->clear ();

		header ('Location: titan.php');

		die ();
	}

	$message = Message::singleton ();

	$_GET['error'] = '';
	while ($error = $message->get (Message::TEXT))
		$_GET['error'] .= $error;

	$message->clear ();

	sleep (3);
}

$skin = Skin::singleton ();

if (isset ($_POST['login']))
	$login = $_POST['login'];
elseif (isset ($_GET['login']))
	$login = $_GET['login'];
else
	$login = '';

$publicUserTypes = array ();

while ($userType = Security::singleton ()->getUserType ())
	if ($userType->showRegister () && ($userType->getType () == UserType::TPROTECTED || $userType->getType () == UserType::TPUBLIC))
		$publicUserTypes [] = $userType;

$validateTerm = FALSE;
			
try
{
	$db = Database::singleton ();
	
	$query = $db->query ("SELECT currval ('". $db->getSchema () ."._document')");
	
	if (!is_null ($query->fetchColumn ()))
		$validateTerm = TRUE;
}
catch (PDOException $e)
{
	if ($e->getCode () == '55000')
		$validateTerm = TRUE;
}

if (Social::isActive ())
{
	if (isset ($_GET['error']) && $_GET['error'] == 'access_denied')
		$_GET['error'] = __ ('Apparently you deny this application to access your profile data. Without granting this permission is not possible to authenticate!');
	
	$socialButtons = array ();
	
	while ($driver = Social::singleton ()->getSocialNetwork ())
	{
		if ($driver->authenticate ())
			try
			{
				if ($driver->login ())
				{
					?>
					<html><body onLoad="document.location='titan.php';"></body></html>
					<?
					exit ();
				}
			}
			catch (Exception $e)
			{
				$_GET ['error'] .= $e->getMessage ();
			}
			catch (PDOException $e)
			{
				$_GET ['error'] .= $e->getMessage ();
			}
		
		$socialButtons [$driver->getName ()] = array ($driver->getLoginUrl (), 'titan.php?target=loadFile&amp;file=repos/social/'. $driver->getName () .'/_resource/button.png');
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title> <?= $instance->getName () ?> </title>
		<meta name="description" content="<?= $instance->getDescription () ?>" />

		<link rel="stylesheet" type="text/css" href="<?= $skin->getCss ('login', Skin::URL) ?>" />

		<link rel="icon" href="<?= $skin->getIcon () ?>" type="image/ico" />
		<link rel="shortcut icon" href="<?= $skin->getIcon () ?>" type="image/ico" />

		<script language="javascript" type="text/javascript" src="titan.php?target=packer&amp;files=sha1,logon"></script>
		<?= XOAD_Utilities::header('titan.php?target=loadFile&amp;file=xoad') ."\n" ?>
		<script language="javascript" type="text/javascript">
		var tAjax = <?= XOAD_Client::register (new AjaxLogon) ?>;

		function logon (form)
		{
			showWait ();

			clearMessage ();

			var formData = xoad.html.exportForm (form);
			
			<?= Security::singleton ()->encryptOnClient () ? "formData ['password'] = hex_sha1(formData ['password']);" : "" ?>

			if (tAjax.logon (formData))
				parent.document.location = '<?= isset ($_GET['url']) ? 'titan.php?'. urldecode ($_GET['url']) : 'titan.php' ?>';
			else
			{
				tAjax.delay (function () {
					tAjax.showMessages ();

					document.getElementById ('formLogin').password.value = '';

					hideWait ();
				});
			}

			return false;
		}

		function setClientTimeZone ()
		{
			if ('<?= @$_COOKIE['_TITAN_TIMEZONE_'] ?>'.length)
				return false;
			
			xmlHttp = new XMLHttpRequest ();
			xmlHttp.open ('GET', 'titan.php?target=setClientTimeZone&z=' + getTimeZone (), true);
			xmlHttp.send (null);
		}
		</script>
	</head>
	<body onLoad="JavaScript: document.getElementById ('formLogin').login.focus (); setClientTimeZone ();">
		<div id="idMain" style="background-image: url('titan.php?target=loadFile&amp;file=interface/image/bar.png');">
			<div class="cLogoApp">
				<?= trim ($skin->getLogo ()) == '' || !file_exists ($skin->getLogo ()) ? '<h1>'. $instance->getName () .'</h1>' : '<img src="'. $skin->getLogo () .'" border="0" alt="'. $instance->getName () .'" title="'. $instance->getName () .'" />' ?>
			</div>
			<div class="cName">
				<a href="http://www.titanframework.com/" target="_blank" title="Titan Framework"><img src="titan.php?target=loadFile&amp;file=interface/image/logo.titan.png" border="0" alt="Titan Framework" alt="Titan Framework" /></a>
			</div>
		</div>
		<div id="idBody" style="width: 1000px; margin: 0 auto;">
			<?
			if (Social::isActive () && sizeof ($socialButtons))
			{
				?>
				<div class="cSocial">
					<?
					foreach ($socialButtons as $name => $array)
						echo '<img src="'. $array [1] .'" onclick="JavaScript: document.location=\''. $array [0] .'\';" border="0" style="float: right;" />'
					?>
					<div><?= __ ('Use your favorite social network to access:') ?></div>
				</div>
				<?
			}
			?>
			<div class="cLogin" style="<?= sizeof ($publicUserTypes) || $validateTerm ? 'float: right; margin-right: 5px; vertical-align: top;' : 'margin: 50px auto;' ?>">
				<?
				if (sizeof ($publicUserTypes) || $validateTerm)
				{
					?>
					<div class="cTitle" style="width: 370px;"><?= __ ('I\'m already registered') ?></div>
					<?
				}
				?>
				<div id="idOldMessage" style="display: ;">
					<div class="cWarning" id="idCaps" style="display: none;">
						<?= __ ('Important! CapsLock is active.') ?>
					</div>
					<div class="cError" <?= !isset ($_GET['error']) || trim ($_GET['error']) == '' ? 'style="display: none;"' : '' ?>>
						<?= isset ($_GET['error']) ? urldecode ($_GET['error']) : '' ?>
					</div>
					<div class="cMessage" <?= !isset ($_GET['message']) || trim ($_GET['message']) == '' ? 'style="display: none;"' : '' ?>>
						<?= isset ($_GET['message']) ? urldecode ($_GET['message']) : '' ?>
					</div>
				</div>
				<label id="labelMessage"></label>
				<div class="cMain">
					<img src="titan.php?target=loadFile&amp;file=interface/image/lock_hot.png" border="0" style="float: left;" alt="Lock" />
					<div style="width: 250px; float: right;">
                    	<br />
						<?= __ ('Welcome to <b>[1]</b> manager!', $instance->getName ()) ?> <br /><br />
						<?= __ ('You must <abbr title="Insert your login and password and press Access.">logon</abbr> to continue.') ?>
					</div>
					<img src="titan.php?target=loadFile&amp;file=interface/image/login.png" border="0" style="position: absolute; top: 179px; left: 80px;" alt="Login" />
					<div id="idLogon" style="display: ;">
						<form id="formLogin" action="" method="post" onSubmit="JavaScript: logon ('formLogin'); return false;">
						<div class="row">
							<label class="labelForm"><?= __ ('Login') ?>:</label>
							<input type="text" name="login" maxlength="32" value="<?= $login ?>" />
						</div>
						<div class="row">
							<label class="labelForm"><?= __ ('Password') ?>:</label>
							<input type="password" name="password" onKeyPress="JavaScript: capsCheck (event);" />
						</div>
						<div class="row">
							<input type="submit" class="button" value="<?= __ ('Access') ?> &raquo;" name="logar" onClick="JavaScript: logon ('formLogin'); return false;" />
						</div>
						<div class="row" style="margin-left: 108px;">
							[<a href="#" class="link" onClick="JavaScript: showLostPassword (); return false;"><?= __ ('Forget your password?') ?></a>]
						</div>
						</form>
					</div>
					<div id="idWait" style="display: none;">
						<img src="titan.php?target=loadFile&amp;file=interface/icon/upload.gif" border="0" alt="Loading" title="Carregando..." />
						<label><?= __ ('Loading...') ?></label>
					</div>
				</div>
				<div id="idLostPassword" class="cMain" style="display: none; margin-top: 10px; height: 200px;">
					<img src="titan.php?target=loadFile&amp;file=interface/image/info_alert.png" border="0" style="float: left;" alt="Alert" />
					<div style="width: 250px; float: right;">
						<?= __ ('<b style="color: #990000;">Important!</b> For recovery password insert your login and click in "Recovery password".') ?><br /><br />
						<?= __ ('You will receive a new message with a link into your e-mail. You can register the new password with this.') ?>
					</div>
					<div id="idLost" style="display: ; top: 140px; height: 70px;">
						<form id="formLost" action="" method="post">
						<div class="row">
							<label class="labelForm"><?= Database::isUnique ('_user', '_email') ? __ ('Login or e-Mail') :  __ ('Login') ?>:</label>
							<input type="text" name="login" maxlength="32" />
						</div>
						<div class="row">
							<input type="button" class="button" value="<?= __ ('Recovery Password') ?> &raquo;" name="logar" onClick="JavaScript: lostPassword (); return false;" />
						</div>
						</form>
					</div>
					<div id="idWaitLost" style="display: none;">
						<img src="titan.php?target=loadFile&amp;file=interface/icon/upload.gif" border="0" alt="Loading" title="<?= __ ('Loading...') ?>" />
						<label><?= __ ('Wait...') ?></label>
					</div>
				</div>
			</div>
			<?
			if (sizeof ($publicUserTypes))
			{
				?>
				<div class="cRegister">
					<div class="cTitle"><?= __ ('I want to register...') ?></div>
					<?
					foreach ($publicUserTypes as $trash => $type)
					{
						?>
						<div id="_DIV_<?= $type->getName () ?>" class="cUserType <?= sizeof ($publicUserTypes) == 1 && !$validateTerm ? 'selected' : 'unselected' ?>">
							<div onClick="JavaScript: showFormRegister ('_DIV_<?= $type->getName () ?>');"><?= __ ('Register as "[1]"', $type->getLabel ()) ?></div>
							<iframe src="?target=register&type=<?= $type->getName () ?>"></iframe>
						</div>
						<?
					}
					?>
				</div>
				<?
			}
			
			if ($validateTerm)
			{
				?>
				<div class="cTerm">
					<div class="cTitle"><?= __ ('Authenticate documents and certificates...') ?></div>
					<div id="_DIV_QR_" class="cValidateMethod unselected">
						<div onClick="JavaScript: showValidate ('_DIV_QR_');"><?= __ ('Using the document\'s QR Code') ?></div>
						<iframe src="?target=tScript&type=Document&file=qr" id="_IFRAME_QR_"></iframe>
					</div>
					<div id="_DIV_INFO_" class="cValidateMethod unselected">
						<div onClick="JavaScript: showValidate ('_DIV_INFO_');"><?= __ ('Using document information') ?></div>
						<iframe src="?target=tScript&type=Document&file=info" id="_IFRAME_INFO_" style="height: 120px;"></iframe>
					</div>
				</div>
				<?
			}
			
			$mobileButtons = Instance::singleton ()->getMobile ();
			
			if (sizeof ($mobileButtons))
			{
				?>
				<div class="cMobile">
					<?
					if (isset ($mobileButtons ['android']))
						echo '<img src="titan.php?target=loadFile&amp;file=interface/image/google-play.png" onclick="JavaScript: document.location=\''. $mobileButtons ['android'] .'\';" border="0" style="float: right;" />'
					?>
					<div><?= __ ('Get our mobile app:') ?></div>
				</div>
				<?
			}
			?>
		</div>
		<div id="idBase">
			<div class="cResources">
				<?
				$path = Instance::singleton ()->getCorePath () .'update'. DIRECTORY_SEPARATOR;
				
				$version = trim (file_get_contents ($path .'VERSION'));
				$release = trim (file_get_contents ($path .'STABLE'));
				?>
				Powered by <a href="http://www.titanframework.com" target="_blank">Titan Framework</a> (<?= $version ?>-<?= $release ?>)
			</div>
			<div class="cPowered">
				<?
				if (trim (Instance::singleton ()->getAuthor ()) == '')
				{
					?>
					<label>&copy; 2005 - <?= date ('Y') ?> &curren; <a href="http://www.carromeu.com/" target="_blank">Camilo Carromeu</a></label>
					<?
				}
				else
					echo Instance::singleton ()->getAuthor ();
				?>
			</div>
		</div>
	</body>
</html>