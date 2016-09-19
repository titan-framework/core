<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title><?= __ ('Document Validation') ?></title>
		<style type="text/css">
			html, body { height:100%; overflow:hidden; background-color: #F4F4F4; text-align: center; }
			body { margin:0; }
			.left { float: left; width: 350px; }
			.right
			{
				float: right;
				font-family: Verdana, Geneva, sans-serif;
				font-weight: bold;
				font-size: 10px;
				width: 210px;
				margin: 50px 15px;
			}
			.right img { border: #CCC 2px solid; }
			.right span { color: #900; }
		</style>
	</head>
	<body>
		<div class="left">
			<object  id="iembedflash" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" width="350px" height="350px">
				<param name="movie" value="titan.php?target=tResource&type=Document&file=QRCode.swf" />
				<param name="quality" value="best" />
				<param name="allowScriptAccess" value="always" />
				<param name="allowFullScreen" value="true" />
				<param name="wmode" value="transparent" />
				<param name="bgcolor" value="#CCCCCC" />
				<embed  allowscriptaccess="always"  id="embedflash" src="titan.php?target=tResource&type=Document&file=QRCode.swf" allowfullscreen="true" quality="best" width="350px" height="350px" wmode="transparent" bgcolor="#CCCCCC" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" mayscript="true"  />
			</object>
		</div>
		<div class="right">
			<?= __ ('Place the document\'s <span>QR CODE</span> in front of the camera.') ?><br /><br />
			<?= __ ('The QR Code is an image like the shown in the figure below:') ?><br /><br />
			<img src="titan.php?target=loadFile&file=interface/image/qr.png" />
		</div>
	</body>
</html>
