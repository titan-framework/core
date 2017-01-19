<?php
try
{
	$instance = Instance::singleton ();
}
catch (Exception $e)
{
	die ($e->getMessage ());
}

session_name ($instance->getSession ());

session_start ();

$_SESSION = array ();

session_destroy ();

session_name ($instance->getSession () .'_PUBLIC_');

session_start ();

define ('XOAD_AUTOHANDLE', true);

require_once Instance::singleton ()->getCorePath () .'class/AjaxPasswd.php';

require_once Instance::singleton ()->getCorePath () .'xoad/xoad.php';

XOAD_Server::allowClasses ('AjaxPasswd');

if (XOAD_Server::runServer ())
	exit ();

try
{
	if (!isset ($_GET['login']) || !isset ($_GET['hash']))
		throw new Exception ('Houve perda de variáveis!');

	$login = urldecode ($_GET['login']);
	$hash = $_GET['hash'];

	$validate = array ("'", '"', '\\', '--', '/*', '*/');
	$validLogin = str_replace ($validate, '', $login);

	if ($login !== $validLogin)
		throw new Exception ('Attention! Probably attack detected. Access Denied!');

	$db = Database::singleton ();

	$sth = $db->prepare ("SELECT _name, _email, _login, _active, _password, _type FROM _user WHERE _login = :login AND _deleted = '0'");

	$sth->bindValue (':login', $login, PDO::PARAM_STR);

	$sth->execute ();

	$obj = $sth->fetch (PDO::FETCH_OBJ);

	if (!$obj)
		throw new Exception ('Login from invalid user.');

	if (!((int) $obj->_active))
		throw new Exception (__ ('Your user is inactive into the system! If you registered recently, wait for one register avaliation.'));

	$name   = $obj->_name;
	$email  = $obj->_email;
	$passwd = $obj->_password;

	if (Security::singleton ()->getUserType ($obj->_type)->useLdap ())
	{
		$ldap = Security::singleton ()->getUserType ($obj->_type)->getLdap ();

		$fields = array ('userPassword', 'mail', 'cn');

		$ldap->connect (FALSE, FALSE, TRUE);

		$result = $ldap->load ($login, $fields);

		$name   = $result ['cn'];
		$email  = $result ['mail'];
		$passwd = $result ['userpassword'];

		$ldap->close ();
	}

	$systemHash = Security::singleton ()->getHash ();

	$vHash = sha1 ($systemHash . $name . $systemHash . $passwd . $systemHash . $email . $systemHash);

	if ((strlen ($hash) != 10 && $hash != $vHash) || (strlen ($hash) != 40 && $hash != shortlyHash ($vHash)))
		throw new Exception (__ ('Invalid link! Use the link \'Recovery Password\' at the logon page for receive a valid link.'));

	$skin = Skin::singleton ();
}
catch (PDOException $e)
{
	header ('Location: '. $instance->getLoginUrl () . '&error='. urlencode ($e->getMessage ()));

	exit ();
}
catch (Exception $e)
{
	header ('Location: '. $instance->getLoginUrl () . '&error='. urlencode ($e->getMessage ()));

	exit ();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title> <?= $instance->getName () ?> :: <?= __ ('Insert/Register Password') ?> </title>

		<link rel="icon" href="<?= $skin->getIcon () ?>" type="image/ico" />
		<link rel="shortcut icon" href="<?= $skin->getIcon () ?>" type="image/ico" />

		<link rel="stylesheet" type="text/css" href="<?= $skin->getCss (array ('main', 'top', 'message', 'password'), Skin::URL) ?>" />
		<!--[if IE]><link rel="stylesheet" type="text/css" href="<?= $skin->getCss ('ie', Skin::URL) ?>" /><![endif]-->

		<script language="javascript" type="text/javascript" src="titan.php?target=packer&amp;files=prototype,general,sha1,boxover,modal-message,modalbox&amp;v=<?= VersionHelper::singleton ()->getTitanBuild () ?>"></script>
		<?= XOAD_Utilities::header('titan.php?target=loadFile&amp;file=xoad') ."\n" ?>
		<script language="javascript" type="text/javascript">
		var tAjax = <?= XOAD_Client::register(new Xoad) ?>;

		var ajax = <?= XOAD_Client::register(new AjaxPasswd) ?>;

		showWait = function ()
		{
			document.getElementById('idWait').innerHTML = '<img src="titan.php?target=loadFile&amp;file=interface/icon/upload.gif" border="0" /> <label>Aguarde! Trabalhando em sua requisi&ccedil;&atilde;o...</label>';
		}

		hideWait = function ()
		{
			document.getElementById('idWait').innerHTML = '';
		}

		function remakePasswd ()
		{
			var fieldPasswd = document.getElementById ('fieldPasswd');
			var fieldRetype = document.getElementById ('fieldRetype');

			if (fieldPasswd.value.replace(/ /g,'') == '')
			{
				alert ('<?= __ ('The password cannot be empty and neither contain empty spaces!') ?>');

				return false;
			}

			if (fieldPasswd.value == '<?= $login ?>')
			{
				alert ('<?= __ ('The password cannot be equal with your login!') ?>');

				return false;
			}

			if (fieldPasswd.value != fieldRetype.value)
			{
				alert ('<?= __ ('The both field values ("New Password" and "Confirm Password") must be equal') ?>');

				return false;
			}

			showWait ();

			var passwd = fieldPasswd.value;

			<?php if (Security::singleton ()->encryptOnClient ()) { echo 'passwd = hex_sha1(passwd);'; } ?>

			if (!ajax.changePasswd ('<?= $hash ?>', passwd, '<?= $login ?>'))
			{
				ajax.showMessages ();

				ajax.delay (function () { hideWait (); });

				return false;
			}

			document.location = '<?= $instance->getLoginUrl () ?>&message=<?= urlencode (__ ('Password registered with success! Use the fields below to access the system.')) ?>&login=<?= $obj->_login ?>';
		}

		function strong (obj, e)
		{
			if (e) car = (window.Event) ? e.which : e.keyCode;

			$('rowStrong').style.display = '';

			var passwd = obj.value + String.fromCharCode (car);

			var ok = 0, str = '<label style="color: #900;"><?= __ ('Too Short') ?></label>', src = 'very_weak';

			if (passwd.length > 5)
			{
				if (passwd.match(/[A-Z]/)) ok++;

				if (passwd.match(/[a-z]/)) ok++;

				if (passwd.match(/[0-9]/)) ok++;

				if (passwd.match(/[@#$%&!?*\[\])(-+=^.\/\\]/)) ok++;

				switch (ok)
				{
					case 0:
						str = '<label style="color: #900;"><?= __ ('Very Weak') ?></label>';
						src = 'very_weak';
						break;

					case 1:
						str = '<label style="color: #FC3;"><?= __ ('Weak') ?></label>';
						src = 'very_fair';
						break;

					case 2:
						str = '<label style="color: #FC3;"><?= __ ('Regular') ?></label>';
						src = 'fair';
						break;

					case 3:
						str = '<label style="color: #69C;"><?= __ ('Strong') ?></label>';
						src = 'good';
						break;

					default:
						str = '<label style="color: #008000;"><?= __ ('Very Strong') ?></label>';
						src = 'strong';
						break;
				}
			}

			$('idStrong').innerHTML = 'Força da senha: ' + str;
			$('imgStrong').src = 'titan.php?target=loadFile&file=interface/image/passwd.' + src + '.gif';
		}
		</script>
	</head>
	<body marginheight="0" marginwidth="0" bottommargin="0" topmargin="0" leftmargin="0" rightmargin="0">
		<div id="idMainSpace"></div>
		<div id="idMain">
			<div class="cLogoApp">
				<?= trim ($skin->getLogo ()) == '' || !file_exists ($skin->getLogo ()) ? '<h1 style="color: #FFFFFF;">'. $instance->getName () .'</h1>' : '<img src="'. $skin->getLogo () .'" border="0" />' ?>
			</div>
			<div class="cName">
				<a href="http://www.titanframework.com/" target="_blank"><img src="titan.php?target=loadFile&amp;file=interface/image/logo.titan.png" border="0" alt="Titan Framework" title="Titan Framework" /></a>
			</div>
		</div>
		<div style="margin: 0 auto; width: 500px; text-align: center; margin-top: 50px;"><h1><?= __ ('Insert/Register Password') ?></h1></div>
		<div style="margin: 0 auto; width: 600px; text-align: center; vertical-align: top;" id="idWait"></div>
		<div id="idBodyReg" style="display:;">
			<label id="labelMessage"></label>
			<table width="500px" border="0" cellpadding="0" cellspacing="3" align="center">
				<tr height="20px"><td></td></tr>
				<tr height="20">
					<td style="text-align: right;" width="150px">
						<b><?= __ ('Name') ?>:</b>
					</td>
					<td width="5px">&nbsp;</td>
					<td>
						<?= $obj->_name ?>
					</td>
				</tr>
				<tr height="20">
					<td style="text-align: right;" width="150px">
						<b>Login:</b>
					</td>
					<td width="5px">&nbsp;</td>
					<td>
						<b><?= $obj->_login ?></b>
					</td>
				</tr>
				<tr height="20">
					<td style="text-align: right;" width="150px">
						<b>E-mail:</b>
					</td>
					<td width="5px">&nbsp;</td>
					<td>
						<?= $obj->_email ?>
					</td>
				</tr>
				<tr height="20">
					<td style="text-align: right;" width="150px" nowrap="nowrap">
						<b><?= __ ('New Password') ?>:</b>
					</td>
					<td width="5px">&nbsp;</td>
					<td>
						<input type="password" class="field" name="password" id="fieldPasswd" onkeypress="JavaScript: strong (this, event);"  />
					</td>
				</tr>
				<tr height="20" id="rowStrong" style="display: none;">
					<td colspan="2">&nbsp;</td>
					<td>
						<div id="idStrong" style="position: relative; font-weight: bold;"></div>
						<img id="imgStrong" style="margin: 3px 0px 3px 0px;" src="titan.php?target=loadFile&amp;file=interface/image/passwd.very_weak.gif" border="0" />
					</td>
				</tr>
				<tr height="20">
					<td style="text-align: right;" width="150px" nowrap="nowrap">
						<b><?= __ ('Confirm Password') ?>:</b>
					</td>
					<td width="5px">&nbsp;</td>
					<td>
						<input type="password" class="field" name="confirm" id="fieldRetype" />
					</td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;</td>
					<td>
						<input type="button" value="Salvar Senha" class="button" onclick="JavaScript: remakePasswd ();" />
					</td>
				</tr>
				<tr height="20px"><td></td></tr>
			</table>
		</div>
		<div id="idBase">
			<div class="cResources" id="_TITAN_INFO_">
				<?php
				$version = VersionHelper::singleton ();

				if (!$version->usingAutoDeploy ())
				{
					?>
					<label>Powered by <a href="http://www.titanframework.com" target="_blank" title="<?= $version->getTitanRelease () ?>">Titan Framework</a> (<?= $version->getTitanRelease () ?>)</label>
					<?php
				}
				else
				{
					?>
					<a href="http://www.titanframework.com" target="_blank" title="Titan Framework (<?= $version->getTitanRelease () ?>)"><img class="cTitanAssign" src="titan.php?target=loadFile&amp;file=interface/image/assign.titan.png" /></a>
					<img class="cIconInfo" id="_TITAN_INFO_ICON_" src="titan.php?target=loadFile&amp;file=interface/image/info.gif" alt="Release Info" />
					<div id="_TITAN_INFO_TEXT_" class="cReleaseInfo" style="display: none;">
						<div>
							<?= __ ('This web application, named "<b>[1]</b>", is in version <b>[2]</b> for <b>[3]</b> environment (released in <b>[4]</b> by <b>[5]</b>).', Instance::singleton ()->getName (), $version->getAppRelease (), $version->getAppEnvironment (), $version->getAppDate (), $version->getAppAuthor ()); ?>
							<br /><br />
							<?= __ ('It was developed using the <b>Titan Framework</b>, version <b>[1]</b>.', $version->getTitanRelease ()); ?>
						</div>
					</div>
					<script type="text/javascript">
					document.getElementById ('_TITAN_INFO_ICON_').onmouseover = function ()	{ document.getElementById ('_TITAN_INFO_TEXT_').style.display = 'block'; };
					document.getElementById ('_TITAN_INFO_ICON_').onmouseout = function () { document.getElementById ('_TITAN_INFO_TEXT_').style.display = 'none'; };
					</script>
					<?php
				}
				?>
			</div>
			<div class="cPowered">
				<?php
				if (trim (Instance::singleton ()->getAuthor ()) == '')
				{
					?>
					<a href="http://creativecommons.org/licenses/by-nd/4.0/" target="_blank" title="Creative Commons License"><img alt="Creative Commons License" style="border-width:0" src="titan.php?target=loadFile&amp;file=interface/image/cc.png" /></a>
					<label>&copy; 2005 - <?= date ('Y') ?> &curren; <a href="http://www.carromeu.com/" target="_blank">Camilo Carromeu</a></label>
					<?php
				}
				else
					echo Instance::singleton ()->getAuthor ();
				?>
			</div>
		</div>
	</body>
</html>
