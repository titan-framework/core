<?
$instance = Instance::singleton ();
$skin = Skin::singleton ();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title> <?= $instance->getName () ?> :: <?= __ ('Disable Alerts') ?> </title>
		
		<link rel="icon" href="<?= $skin->getIcon () ?>" type="image/ico" />
		<link rel="shortcut icon" href="<?= $skin->getIcon () ?>" type="image/ico" />
		
		<link rel="stylesheet" type="text/css" href="<?= $skin->getCss (array ('main', 'top', 'message', 'password'), Skin::URL) ?>" />
		<!--[if IE]><link rel="stylesheet" type="text/css" href="<?= $skin->getCss ('ie', Skin::URL) ?>" /><![endif]-->
		
		<style media="screen" type="text/css">
		.alertMessage,
		.alertMessage a
		{
			width: 779px;
			margin: 50px auto;
			font-family: Georgia, "Times New Roman", Times, serif;
			font-size: 24px;
			color: #000;
			text-align: center;
			line-height: 36px;
		}
		.alertMessage a
		{
			color: #900;
		}
		.alertMessage a:hover
		{
			text-decoration: underline;
		}
		.alertSuccess
		{
			color: #090;
		}
		.alertChange,
		.alertChange a
		{
			font-size: 18px;
		}
		</style>
	</head>
	<body marginheight="0" marginwidth="0" bottommargin="0" topmargin="0" leftmargin="0" rightmargin="0">
		<div id="idMainSpace"></div>
		<div id="idMain">
			<div class="cLogoApp">
				<a href="<?= $instance->getUrl () ?>"><?= trim ($skin->getLogo ()) == '' || !file_exists ($skin->getLogo ()) ? '<h1 style="color: #FFFFFF;">'. $instance->getName () .'</h1>' : '<img src="'. $skin->getLogo () .'" border="0" />' ?></a>
			</div>
			<div class="cName">
				<a href="http://www.titanframework.com/" target="_blank"><img src="titan.php?target=loadFile&amp;file=interface/image/logo.titan.png" border="0" alt="Titan Framework" title="Titan Framework" /></a>
			</div>
		</div>
		<div class="alertMessage alertSuccess">
			<?= __ ('Success to disable e-mail alert messages!') ?>
		</div>
		<div class="alertMessage">
			<?= __ ('You no longer receive e-mail alert messages from<br /><a href="[1]">[2]</a>!', $instance->getUrl (), $instance->getName ()) ?>
		</div>
		<div class="alertMessage alertChange">
			<?= __ ('To change this configuration, <a href="[1]">log in</a> to system and modify your profile.', $instance->getLoginUrl () .'&login='. @$_GET['login']) ?>
		</div>
	</body>
</html>