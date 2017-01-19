<?php
$skin = Skin::singleton ();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title> <?= $instance->getName () ?> :: <?= __ ('User register') ?></title>

		<link rel="stylesheet" type="text/css" href="<?= $skin->getCss (array ('main', 'message'), Skin::URL) ?>" />
		<!--[if IE]><link rel="stylesheet" type="text/css" href="<?= $skin->getCss ('ie', Skin::URL) ?>" /><![endif]-->

		<?php
		$types = Instance::singleton ()->getTypes ();

		foreach ($types as $type => $path)
			if (file_exists ($path .'_css.php'))
				include $path .'_css.php';

		if (file_exists ($section->getCompPath () .'_css.php'))
			include $section->getCompPath () .'_css.php';
		?>
		<script language="javascript" type="text/javascript" src="titan.php?target=packer&amp;files=prototype,effects,protolimit,tooltip,spin.min&amp;v=<?= VersionHelper::singleton ()->getTitanBuild () ?>"></script>
		<script language="javascript" type="text/javascript">
		String.prototype.namespace = function (separator)
		{
			this.split (separator || '.').inject (window, function (parent, child) {
				return parent[child] = parent[child] || { };
			})
		}
		</script>
		<script language="javascript" type="text/javascript" src="titan.php?target=packer&amp;files=general,type,boxover,common,modal-message,modalbox&amp;v=<?= VersionHelper::singleton ()->getTitanBuild () ?>"></script>
		<?= XOAD_Utilities::header('titan.php?target=loadFile&amp;file=xoad') ."\n" ?>
		<script language="javascript" type="text/javascript">
		var tAjax = <?= XOAD_Client::register(new Xoad) ?>;

		var ajax = <?= XOAD_Client::register(new Ajax) ?>;

		function showWait ()
		{
			document.getElementById('idWait').innerHTML = '<img src="titan.php?target=loadFile&amp;file=interface/icon/upload.gif" border="0" /> <label><?= __ ('Wait! working on your request...') ?></label>';
		}

		function hideWait ()
		{
			document.getElementById('idWait').innerHTML = '';
		}
		</script>
		<?php
		$types = Instance::singleton ()->getTypes ();

		foreach ($types as $type => $path)
			if (file_exists ($path .'_js.php'))
				include $path .'_js.php';

		if (file_exists ($section->getCompPath () .'_js.php'))
			include $section->getCompPath () .'_js.php';
		?>
	</head>
	<body>
		<style type="text/css">
		body
		{
			background: #FFF none;
			margin: 0px;
		}
		#idMessage .cError a.cReport
		{
			display: none;
		}
		</style>
		<noscript>
			<div>
				<b>Atenção!</b> Seu navegador não suporta ou o JavaScript não está habilitado.
				Você pode corrigir este problema habilitando nas configurações do seu navegador o suporte à JavaScript ou,
				caso ele realmente não suporte esta função, fazer download de outro navegador mais moderno
				(tal como o <a href="http://www.getfirefox.com/" target="_blank">Mozilla Firefox</a>).
			</div>
		</noscript>
		<div id="divCalendar" style="position: absolute; visibility: hidden; background-color: white; layer-background-color: white; z-index: 5;"></div>
		<div id="idWait" style="display: none;"></div>
		<div id="idRegister">
			<label id="labelMessage"></label>
			<?= $_OUTPUT ['SECTION'] ?>
		</div>
		<div id="idBody" style="display: none;"></div>
	</body>
</html>
