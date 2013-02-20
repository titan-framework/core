<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<style type="text/css">
		td
		{
			font-family: Verdana, Arial, Helvetica, sans-serif;
			font-size: 12px;
			color: #666666;
		}
		</style>
	</head>
	<body>
		<table align="center" border="0" style="border: #666666 2px solid;" cellpadding="3">
			<tr>
				<td rowspan="2">
					<img src="titan.php?target=loadFile&amp;file=interface/image/warning.png" border="0" style="margin-right: 10px;" />
				</td>
				<td style="text-align: center; font-weight: bold;">
					<?= isset ($_GET['error']) ? urldecode ($_GET['error']) : __ ('Unknow error in system! Try again more later.') ?>
				</td>
			</tr>
		</table>
	</body>
</html>