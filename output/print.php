<?php
$skin = Skin::singleton ();

if (!(bool) ini_get ('zlib.output_compression'))
 	ob_start ('ob_gzhandler');
			
header ('Content-type: text/html; charset: UTF-8');
header ('Content-Encoding: gzip');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title> <?= $instance->getName () ?> </title>
		
		<link rel="icon" href="<?= $skin->getIcon () ?>" type="image/ico" />
		<link rel="shortcut icon" href="<?= $skin->getIcon () ?>" type="image/ico" />

		<link rel="stylesheet" type="text/css" href="<?= $skin->getCss (array ('main', 'print'), Skin::URL) ?>" />
		<!--[if IE]><link rel="stylesheet" type="text/css" href="<?= $skin->getCss ('ie', Skin::URL) ?>" /><![endif]-->
	</head>
	<body marginheight="0" marginwidth="0" bottommargin="0" topmargin="0" leftmargin="0" rightmargin="0" onload="JavaScript: window.print();">
		<div id="idSection" style="padding: 0px; height: 24px;">
			<div class="cPath" style="margin: 6px;">
				<?= $_OUTPUT ['BREADCRUMB'] ?>
			</div>
			<div class="cMenu" style="margin: 2px; margin-right: 6px;">
				<a href="#" onclick="JavaScript: window.print(); return false;"><img src="titan.php?target=loadFile&amp;file=interface/icon/print.gif" border="0" title="Imprimir" /></a>
			</div>
		</div>
		<div id="idBody" style="margin-bottom: 10px;">
			<?= $_OUTPUT ['SECTION'] ?>
		</div>
	</body>
</html>