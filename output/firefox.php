<?
try
{
	$instance = Instance::singleton ();

	$skin = Skin::singleton ();
}
catch (Exception $e)
{
	die ($e->getMessage ());
}

Log::singleton ()->add ('BROWSER', '', Log::INFO, FALSE, FALSE);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title> <?= $instance->getName () ?> </title>

		<link rel="icon" href="<?= $skin->getIcon () ?>" type="image/ico" />
		<link rel="shortcut icon" href="<?= $skin->getIcon () ?>" type="image/ico" />

		<link rel="stylesheet" type="text/css" href="<?= $skin->getCss ('firefox', Skin::URL) ?>" />
	</head>
	<body>
		<div id="idMain">
			<div class="cLogoApp">
				<?= $instance->getName () ?>
			</div>
			<div class="cName">
				<a href="http://www.titanframework.com/" target="_blank"><img src="titan.php?target=loadFile&amp;file=interface/image/logo.titan.png" border="0" alt="Titan Framework" title="Titan Framework" /></a>
			</div>
		</div>
		<div id="idBody">
			<div class="alert">
				<label><?=__ ('Attention')?>!</label><br />
				<?= __ ('Navigator unfit for the system')?>
			</div>
			<div class="text">
				<?= __ ('The system <b>[1]</b> was create using <a href="http://www.ledes.net/titan/">Titan Framework</a> that, at moment only support free and open source browser <a href="http://www.getfirefox.com/">Mozilla Firefox</a>. For contine using this manager system you has the following options:', $instance->getName ())?>
			</div>
			<div class="item">
				<div class="number">1&ordm;</div>
					<?= __ ('If you have been installed the <a href="http://www.getfirefox.com/">Mozilla Firefox</a> in your computer, use it for access the system <b>[1]</b>', $instance->getName ())?>
				</b>.
			</div>
			<div class="item">
				<div class="number">2&ordm;</div>
				Caso não tenha o <a href="http://www.getfirefox.com/">Mozilla Firefox</a> instalado em seu sistema, você pode fazer download deste excelente navegador e instalá-lo em seu computador.
				Para isto basta clicar na imagem abaixo:<br />
				<a href="http://www.getfirefox.com/"><img style="margin: 15px 0px 0px 190px;" src="titan.php?target=loadFile&amp;file=interface/image/get.firefox.png" border="0" /></a>
			</div>
			<div class="item">
				<div class="number" style="height: 100px;">3&ordm;</div>
				Caso não queira ou não possa instalar o <a href="http://www.getfirefox.com/">Mozilla Firefox</a> neste computador, você pode fazer download da versão <i>standalone</i> deste navegador.
				Esta versão não precisa ser instalada, faça o download do arquivo, descompacte-o em qualquer pasta do computador (pode ser, inclusive, um <i>pendrive</i>), e execute o arquivo [<b>FirefoxPortable.exe</b>].
				Para efetuar o download, clique na imagem abaixo:<br />
				<a href="http://portableapps.com/apps/internet/firefox_portable" target="_blank"><img style="margin: 15px 0px 0px 190px;" src="titan.php?target=loadFile&amp;file=interface/image/firefox.portable.png" border="0" /></a>
			</div>
		</div>
		<div id="idBase" style="position: absolute; bottom: 0px;">
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