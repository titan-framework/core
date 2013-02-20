<script language="javascript" type="text/javascript">
function makeAlert (type, message)
{
	var color, img, str;
	
	switch (type)
	{
		case 'SUCCESS':
			color = '009900';
			img = 'ok';
			break;
		
		case 'FAIL':
			color = '990000';
			img = 'cancel';
			break;
		
		case 'WARNING':
			color = 'E4B01A';
			img = 'alert';
			break;
		
		default:
			return false;
	}
	
	str  = '<table width="100%" style="border: #' + color + ' 1px solid; margin-bottom: 3px; background-color: #FFFFFF;">';
	str += '	<tr height="30px">';
	str += '		<td style="text-align: center; width: 30px;"><img src="titan.php?target=loadFile&file=interface/icon/' + img + '.gif" border="0" /></td>';
	str += '		<td>' + message + '</td>';
	str += '	</tr>';
	str += '</table>';
	
	return str;
}

function showArchError (error)
{
	var str = '';
	
	str  = '<table width="100%" style="border: #AAAAAA 2px solid; margin-bottom: 3px; background-color: #FFFFFF;">';
	str += '	<tr height="10px">';
	str += '		<td style="font-size: 9px; font-weight: bold; color: #999999;">Mensagens</td>';
	str += '		<td style="text-align: right;"><a href="#" style="font-size: 9px; font-weight: bold; color: #990000;" onclick="JavaScript: document.getElementById(\'errorBanner\').style.display=\'none\';">Fechar</a></td>';
	str += '	</tr>';
	str += '</table>';
	
	document.getElementById ('errorBanner').innerHTML = str + error;
	
	document.getElementById ('errorBanner').style.display = '';
}

function goStep (step)
{
	document.location = 'titan.php?target=body&toSection=<?= $section->getName () ?>&toAction=step_' + step;
}

function preview (itemId, obj)
{
	openPopup ('instance/' + itemId + '/titan.php?target=login', 'popup_' + itemId);
}
</script>