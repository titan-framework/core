<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title><?= __ ('Document Validation') ?></title>
		<style type="text/css">
			html, body { height: 100%; overflow: hidden; background-color: #F4F4F4; text-align: center; }
			body { margin: 0px; padding: 50px 10px; font-family: Verdana, Geneva, sans-serif; font-size: 12px; font-weight: bold; }
			.field
			{
				border: #AAA 1px solid;
				font-family: Verdana, Arial, Helvetica, sans-serif;
				font-size: 12px;
				width: 100px;
				padding: 2px;
				color: #575556;
				background: #FFF url(titan.php?target=loadFile&file=interface/back/field.gif) top left no-repeat;
				outline: none;
				transition: all 0.25s ease-in-out;
				-webkit-transition: all 0.25s ease-in-out;
				-moz-transition: all 0.25s ease-in-out;
				margin-right: 14px;
			}
			.field:focus
			{
				box-shadow: 0 0 5px #000;
				-webkit-box-shadow: 0 0 5px #000; 
				-moz-box-shadow: 0 0 5px #000; 
			}
			.button
			{
				font-family: Verdana, Geneva, sans-serif;
				font-size: 13px;
				font-weight: bold;
				border: #AAA 1px solid;
				color: #575556;
				background: #FFF url(titan.php?target=loadFile&file=interface/back/field.gif) top left no-repeat;
				cursor: pointer;
			}
			.button:hover
			{
				background: #AAA;
				color: #FFF;
				box-shadow: 0 0 5px #AAA;
				-webkit-box-shadow: 0 0 5px #AAA; 
				-moz-box-shadow: 0 0 5px #AAA; 
			}
		</style>
		<script language="javascript" type="text/javascript">
			function fControl (field, e)
			{
				var char = 48;
				
				if (e)
				{
					var obj = new crossEvent (e);
					
					char = obj.charCode;
				}
				
				if (char == 8 || char == 0)
					return true;
				
				if (char < 48 || char > 57)
					return false;
				
				if (e)
					char -= 48;
				else
					char = '';
							
				var number = String (field.value);
				
				number = number.replace (/\./g,'');
				
				number = String (parseInt (number,10));
				
				if(number == 'NaN')
					number = String ('0');
				
				if(number.length > 16)
					return false;
				
				number = number + char;
				
				if(number.length == 0)
					number = String('0');
				
				field.value = number;
				
				return false;
			}
			function fAuth (field, e)
			{
				var char = e.charCode ? e.charCode : (e.keyCode ? e.keyCode : (e.which ? e.which : 0));
				
				if (char == 8 || char == 0)
					return true;
				
				if (char > 31 && (char < 65 || char > 90) && (char < 97 || char > 122) && (char < 48 || char > 57))
					return false;
				
				if (field.value.length > 16)
					return false;
				
				field.value = field.value + (String.fromCharCode (char)).toUpperCase ();
				
				return false;
			}
			function send ()
			{
				var control = parseInt (document.getElementById ('_CONTROL_').value);
				var auth = document.getElementById ('_AUTH_').value;
				
				if (!control || auth.length != 16 || /[^A-Z0-9]/.test (auth))
					alert ('<?= __ ('The values entered are invalid. Please check!') ?>')
				else
					document.location = '?target=tScript&type=Document&file=v&c=' + control + '&a=' + auth;
				
				return false;
			}
		</script>
	</head>
	<body>
		<label><?= __ ('Control Number') ?>:</label>
		<input type="text" class="field" name="control" id="_CONTROL_" value="0" onkeypress="JavaScript: return fControl (this, event);" onkeyup="JavaScript: fControl (this, false);" />
		<label><?= __ ('Authentication') ?>:</label>
		<input type="text" class="field" style="width: 150px" name="auth" id="_AUTH_" value="" maxlength="16" onkeypress="JavaScript: return fAuth (this, event);" onkeyup="JavaScript: fAuth (this, false);" />
		<input type="button" class="button" value="&raquo;" onclick="JavaScript: send ();" />
	</body>
</html>