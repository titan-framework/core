<?php

$skin = Skin::singleton ();

$_CHAT ['HEADER'] = '';

$_CHAT ['BODY'] = '';

if (Instance::singleton ()->useChat ())
{
	include Instance::singleton ()->getCorePath () .'system/chat.php';

	ob_start ();

	$chat->printStyle ();

	$chat->printJavascript ();

	$_CHAT ['HEADER'] = ob_get_clean ();

	ob_start ();

	$chat->printChat ();

	$_CHAT ['BODY'] = ob_get_clean ();
}

$user = User::singleton ();

if ($user->getType ()->useLdap () && $user->getType ()->getLdap ()->updateOnLogon ())
{
	$cSection = Business::singleton ()->getSection (Section::TCURRENT);
	$cAction  = Business::singleton ()->getAction (Action::TCURRENT);

	try
	{
		Business::singleton ()->setCurrent ($user->getType ()->getName (), '_modify');

		$files = array ($user->getType ()->getLdapForm (), $user->getType ()->getModify (), 'edit.xml', 'all.xml');

		$form = new Form ($files);

		if (!$form->load ($user->getId ()))
			throw new Exception ('Unable to load your register data!');

		if (!$form->loadFromLdap ($user->getLogin (), $user->getType ()->getLdap ()))
			throw new Exception ('Unable to load your data from the server LDAP!');

		if (!$form->save ($user->getId (), FALSE))
			throw new Exception ('Unable to save in Database');

		User::singleton ()->update ();

		$_SESSION ['_TITAN_LDAP_USER_CONTROL_'][$user->getId ()] = TRUE;
	}
	catch (Exception $e)
	{
		Message::singleton ()->addWarning ($e->getMessage ());

		Message::singleton ()->save ();
	}

	Business::singleton ()->setCurrent ($cSection->getName (), $cAction->getName ());
}

if (!(bool) ini_get ('zlib.output_compression'))
	ob_start ('ob_gzhandler');

header ('Content-type: text/html; charset: UTF-8');
header ('Content-Encoding: gzip');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title> <?= $instance->getName () ?> </title>

		<link rel="stylesheet" type="text/css" href="<?= $skin->getCss (array ('top', 'menu'), Skin::URL) ?>" />
		<link rel="stylesheet" type="text/css" href="<?= $skin->getCss ('instance-top', Skin::PATH) ?>" />

		<link rel="icon" href="<?= $skin->getIcon () ?>" type="image/ico" />
		<link rel="shortcut icon" href="<?= $skin->getIcon () ?>" type="image/ico" />

		<script src="https://use.fontawesome.com/a98ba7fcbf.js"></script>
		<script language="javascript" type="text/javascript" src="titan.php?target=packer&amp;files=top"></script>
		<script language="javascript" type="text/javascript">
		function verifyAlerts ()
		{
			xmlHttp = new XMLHttpRequest ();
			xmlHttp.open ('GET', 'titan.php?target=unreadAlerts&id=<?= User::singleton ()->getId () ?>', false);
			xmlHttp.send (null);

			var alerts = parseInt (xmlHttp.responseText);

			var icon = document.getElementById ('alerts');

			if (alerts)
			{
				if (icon.className == 'alertsShiny')
					return false;

				icon.src = 'titan.php?target=loadFile&file=interface/icon/alerts.gif';
				icon.className = 'alertsShiny';
				icon.alt = alerts + ' <?= __ ('alert(s)!') ?>';
				icon.title = alerts + ' <?= __ ('alert(s)!') ?>';
			}
			else
			{
				if (icon.className == '')
					return false;

				icon.src = 'titan.php?target=loadFile&file=interface/icon/grey/alerts.gif';
				icon.className = '';
				icon.alt = '<?= __ ('No alerts!') ?>';
				icon.title = '<?= __ ('No alerts!') ?>';
			}

			return false;
		}

		function verifyShoppingCart ()
		{
			xmlHttp = new XMLHttpRequest ();
			xmlHttp.open ('GET', 'titan.php?target=itemsInShoppingCart&id=<?= User::singleton ()->getId () ?>', false);
			xmlHttp.send (null);

			var hasItens = parseInt (xmlHttp.responseText);

			var icon = document.getElementById ('shopping');

			if (hasItens)
			{
				if (icon.className == 'shoppingShiny')
					return false;

				icon.src = 'titan.php?target=loadFile&file=interface/icon/shop.gif';
				icon.className = 'shoppingShiny';
				icon.alt = hasItens + ' <?= __ ('item(s)!') ?>';
				icon.title = hasItens + ' <?= __ ('item(s)!') ?>';
			}
			else
			{
				if (icon.className == '')
					return false;

				icon.src = 'titan.php?target=loadFile&file=interface/icon/grey/shop.gif';
				icon.className = '';
				icon.alt = '<?= __ ('Your shopping cart is empty!') ?>';
				icon.title = '<?= __ ('Your shopping cart is empty!') ?>';
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

		<?= $_CHAT ['HEADER'] ?>
	</head>
	<body onload="JavaScript: setClientTimeZone ();">
		<div id="idMain" style="z-index: 3;">
			<div class="cLogoApp">
				<?= trim ($skin->getLogo ()) == '' || !file_exists ($skin->getLogo ()) ? '<h1 style="color: #FFF; cursor: pointer;" onclick="JavaScript: parent.document.location=\'titan.php\';">'. $instance->getName () .'</h1>' : '<img src="'. $skin->getLogo () .'" border="0" alt="'. $instance->getName () .'" title="'. $instance->getName () .'" style="cursor: pointer;" onclick="JavaScript: parent.document.location=\'titan.php\';" />' ?>
			</div>
			<div class="cIcons">
				<label id="idChatNotify"></label>
				<img src="titan.php?target=loadFile&amp;file=interface/icon/home.gif" border="0" onclick="JavaScript: parent.document.location='titan.php';" alt="<?= __ ('Start Page') ?>" title="<?= __ ('Start Page') ?>" />
				&nbsp;
				<?php
				if (Instance::singleton ()->useChat ())
				{
					?>
					<img src="titan.php?target=loadFile&amp;file=interface/icon/chat.gif" border="0" onclick="JavaScript: showChat ();" alt="Chat" title="Chat" />
					&nbsp;
					<?php
				}

				$profile = User::singleton ()->getType ()->getProfile ();

				if ($profile)
				{
					?>
					<img src="titan.php?target=loadFile&amp;file=interface/icon/profile.gif" border="0" onclick="JavaScript: parent.body.location='<?= $profile ?>';" alt="<?=__ ('Profile') ?>" title="<?= __ ('Profile') ?>" />
					&nbsp;
					<?php
				}

				if (Shopping::isActive ())
				{
					?>
					<script language="javascript" type="text/javascript">
						if (window.addEventListener)
							window.addEventListener ('load', function () { verifyShoppingCart (); }, false);
						else if (window.attachEvent)
							window.attachEvent ('onload', function () { verifyShoppingCart (); });

						window.setInterval ('verifyShoppingCart ()', 15000);
					</script>
					<img id="shopping" src="titan.php?target=loadFile&amp;file=interface/icon/grey/shop.gif" border="0" onclick="JavaScript: parent.body.showShoppingCart ();" alt="<?= __ ('Your shopping cart is empty!') ?>" title="<?= __ ('Your shopping cart is empty!') ?>" />
					&nbsp;
					<?php
				}

				if (Database::tableExists ('_alert'))
				{
					?>
					<script language="javascript" type="text/javascript">
						if (window.addEventListener)
							window.addEventListener ('load', function () { verifyAlerts (); }, false);
						else if (window.attachEvent)
							window.attachEvent ('onload', function () { verifyAlerts (); });

						window.setInterval ('verifyAlerts ()', 15000);
					</script>
					<img id="alerts" src="titan.php?target=loadFile&amp;file=interface/icon/grey/alerts.gif" border="0" onclick="JavaScript: parent.body.showAlerts ();" alt="<?= __ ('No alerts!') ?>" title="<?= __ ('No alerts!') ?>" />
					&nbsp;
					<?php
				}
				?>
				<img src="titan.php?target=loadFile&amp;file=interface/locale/<?= Localization::singleton ()->getLanguage () ?>.gif" border="0" onclick="JavaScript: parent.body.chooseLanguage ();" alt="<?= __ ('Language') ?>" title="<?= __ ('Language') ?>" />
				&nbsp;
				<img src="titan.php?target=loadFile&amp;file=interface/icon/bug.gif" border="0" onclick="JavaScript: parent.body.bugReport ();" alt="<?= __ ('Bug Report') ?>" title="<?= __ ('Bug Report') ?>" />
				&nbsp;
				<?php
				if (PHP_OS == 'Linux' && User::singleton ()->isAdmin () && Backup::singleton ()->isActive ())
				{
					if (!isset ($_SESSION['_TITAN_BACKUP_FREE_']))
						$_SESSION['_TITAN_BACKUP_FREE_'] = floor (disk_free_space (Backup::singleton ()->getRealPath ()) / (1024 * 1024));

					if (!isset ($_SESSION['_TITAN_BACKUP_DB_']))
						$_SESSION['_TITAN_BACKUP_DB_'] = ceil (Database::size () / (1024 * 1024));

					if (!isset ($_SESSION['_TITAN_BACKUP_FILE_']))
						$_SESSION['_TITAN_BACKUP_FILE_'] = ceil (dirSize (realpath (Archive::singleton ()->getDataPath ())) / (1024 * 1024));

					if (!isset ($_SESSION['_TITAN_BACKUP_CACHE_']))
						$_SESSION['_TITAN_BACKUP_CACHE_'] = ceil (dirSize (realpath (Instance::singleton ()->getCachePath ())) / (1024 * 1024));
					?>
					<img src="titan.php?target=loadFile&amp;file=interface/icon/backup.gif" border="0" onclick="JavaScript: parent.body.instanceBackup ();" alt="<?= __ ('Backup') ?>" title="<?= __ ('Backup') ?>" />
					&nbsp;
					<?php
				}

				if (Manual::isActive ())
				{
					?>
					<img src="titan.php?target=loadFile&amp;file=interface/icon/manual.gif" border="0" onclick="JavaScript: parent.body.getHelp ();" alt="<?= __ ('Get Help') ?>" title="<?= __ ('Get Help') ?>" />
					&nbsp;
					<?php
				}
				?>
				<img src="titan.php?target=loadFile&amp;file=interface/icon/logout.gif" border="0" onclick="JavaScript: parent.document.location='titan.php?target=logoff&amp;message=<?= urlencode (__ ('Logoff successfully executed!')) ?>';" alt="<?= __ ('Logoff') ?>" title="<?= __ ('Logoff') ?>" />
				<br />
				<label style="margin-right: 5px;"><?= __ ('Welcome <b>[1]</b>', $user->getName ()) ?></label>
			</div>
		</div>
		<div id="idMainSpace"></div>
		<div id="idHeader">
			<div class="cGroups">
				<b><?= $user->getType ()->getLabel () ?></b>
				<label style="color: #900; font-weight: bold; margin-left: 5px;">&loz;</label>
				<label style="color: #555;"><?= __ ('Group(s):') ?>
				<?php
				$groups = '<b>'. implode ('</b>, <b>', $user->getGroups ()) .'</b>';

				$position = strrpos ($groups, ',');

				if ($position)
				{
					echo substr ($groups, 0, $position);
					echo ' '. __ ('and') . substr ($groups, $position + 1);
				}
				else
					echo $groups;
				?></label>
				<label style="color: #900; font-weight: bold;"></label>
			</div>
			<div id="idMenu"><i class="fa fa-refresh fa-spin fa-2x"></i></div>
			<div id="idMenuDialog" class="cMenuDialog" style="visibility: hidden; opacity: 0;"><div><?= __ ('Menu... start here!') ?></div></div>
			<?php
			if (Lucene::singleton ()->isActive ())
			{
				?>
				<div id="idSearch" class="cSearch"><input type="text" placeholder=" &#xF002; <?= __ ('Search...') ?>" onkeypress="JavaScript: searchSend (this, event);" onkeyup="JavaScript: searchSend (this, false);" /></div>
				<?php
			}
			?>
			<div id="idWait" class="cWait" style="visibility: hidden; opacity: 0;">
				<label><?= __ ('Wait! Working on your request...') ?></label>
			</div>
		</div>
		<div id="idChat" style="display: none;">
			<?= $_CHAT ['BODY'] ?>
		</div>
	</body>
</html>
