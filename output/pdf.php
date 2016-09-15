<?php
$skin = Skin::singleton ();

$section = Business::singleton ()->getSection (Section::TCURRENT);

$action = $section->getAction (Action::TCURRENT)->getLabel ();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title> <?= $instance->getName () ?> </title>
	</head>
	<body marginheight="0" marginwidth="0" bottommargin="0" topmargin="0" leftmargin="0" rightmargin="0">
		<div style="width: 560px; margin: 20px 20px 0px 20px; font-family: Helvetica; font-size: 12px;"><b><?= Instance::singleton ()->getName () ?></b></div>
		<div style="width: 560px; margin: 0px 20px 20px 20px; font-family: Helvetica; font-size: 10px;"><?= getBreadPath ($section, FALSE) . $action ?></div>
		<div style="width: 560px; margin: 20px; font-family: Helvetica; font-size: 12px;">
			<?= $_OUTPUT ['SECTION'] ?>
		</div>
	</body>
</html>