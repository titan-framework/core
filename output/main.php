<?
if (!isFirefox ())
{
	header ('Location: titan.php?target=noFirefox');

	exit ();
}

include Instance::singleton ()->getCorePath () .'system/chat.php';

$skin = Skin::singleton ();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title> <?= $instance->getName () ?> </title>
		<link rel="icon" href="<?= $skin->getIcon () ?>" type="image/ico" />
		<link rel="shortcut icon" href="<?= $skin->getIcon () ?>" type="image/ico" />
		<script language="javascript" type="text/javascript">
		function loadFrame ()
		{
			frames['body'].window.location.href = 'titan.php?target=body&<?= $_SERVER['QUERY_STRING'] ?>';
		}
		
		function reloadFrames ()
		{
			frames['banner'].window.location.href = frames['banner'].window.location.href;
			frames['body'].window.location.href = frames['body'].window.location.href;
		}
		</script>
	</head>
	<frameset id="main" rows="86px,*" frameborder="no" framespacing="0" onload="JavaScript: loadFrame ();">
		<frame src="titan.php?target=top" name="banner" noresize="noresize" scrolling="no" />
		<frame src="titan.php?target=blank" name="body" noresize="noresize" scrolling="no" />
	</frameset>
	<noframes><body onload="JavaScript: document.location='titan.php?target=noFirefox';">You browser does not support frames!</body></noframes>
</html>