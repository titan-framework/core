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
		<div id="idBase">
			<div class="cResources" id="_TITAN_INFO_">
				<?
				$path = Instance::singleton ()->getCorePath () .'update'. DIRECTORY_SEPARATOR;
				
				$version = trim (file_get_contents ($path .'VERSION'));
				$release = trim (file_get_contents ($path .'STABLE'));
				
				$appReleasePath = Instance::singleton ()->getCachePath () .'RELEASE';
				
				$autoDeploy = FALSE;
				
				if (file_exists ($appReleasePath) && is_readable ($appReleasePath))
				{
					$file = parse_ini_file ($appReleasePath);
					
					if (is_array ($file)) 
					{
						$autoDeploy = TRUE;
						
						$requiredKeys = array ('version', 'environment', 'date', 'author');
						
						foreach ($requiredKeys as $trash => $key)
							if (!array_key_exists ($key, $file) || trim ((string) $file [$key]) == '')
								$autoDeploy = FALSE;
					}
				}
				
				if (!$autoDeploy)
				{
					?>
					<label>Powered by <a href="http://www.titanframework.com" target="_blank" title="<?= $version .'-'. $release ?>">Titan Framework</a> (<?= $version .'-'. $release ?>)</label>
					<?
				}
				else
				{
					$appRelease = $file ['version'];
					$appEnvironment = $file ['environment'];
					$appDate = strftime ('%x %X', $file ['date']);
					
					$fileOfVersion = 'update'. DIRECTORY_SEPARATOR .'VERSION';
					
					if (file_exists ($fileOfVersion) && is_readable ($fileOfVersion))
					{
						$appVersion = trim (file_get_contents ($fileOfVersion, 0, NULL, 0, 16));
						
						if (!empty ($appVersion))
							$appRelease = $appVersion .'-'. $appRelease;
					}
					?>
					<a href="http://www.titanframework.com" target="_blank" title="Titan Framework (<?= $version .'-'. $release ?>)"><img class="cTitanAssign" src="titan.php?target=loadFile&amp;file=interface/image/assign.titan.png" /></a>
					<img class="cIconInfo" id="_TITAN_INFO_ICON_" src="titan.php?target=loadFile&amp;file=interface/image/info.gif" alt="Release Info" />
					<div id="_TITAN_INFO_TEXT_" class="cReleaseInfo" style="display: none;">
						<div>
							<?= __ ('This web application, named "<b>[1]</b>", is in version <b>[2]</b> for <b>[3]</b> environment (released <b>[4]</b>).', Instance::singleton ()->getName (), $appRelease, $appEnvironment, $appDate); ?>
							<br /><br />
							<?= __ ('It was developed using the <b>Titan Framework</b>, version <b>[1]</b>.', $version .'-'. $release); ?>
						</div>
					</div>
					<script type="text/javascript">
					document.getElementById ('_TITAN_INFO_ICON_').onmouseover = function ()	{ document.getElementById ('_TITAN_INFO_TEXT_').style.display = 'block'; };
					document.getElementById ('_TITAN_INFO_ICON_').onmouseout = function () { document.getElementById ('_TITAN_INFO_TEXT_').style.display = 'none'; };
					</script>
					<?
				}
				?>
			</div>
			<div class="cPowered">
				<?
				if (trim (Instance::singleton ()->getAuthor ()) == '')
				{
					?>
					<a href="http://creativecommons.org/licenses/by-nd/4.0/" target="_blank" title="Creative Commons License"><img alt="Creative Commons License" style="border-width:0" src="titan.php?target=loadFile&amp;file=interface/image/cc.png" /></a>
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