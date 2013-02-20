<script language="javascript" type="text/javascript">
'global.Phone'.namespace ();

global.Phone.format = function (field, e)
{
	var char = 48;
	
	var obj = new crossEvent (e);
	
	char = obj.charCode;
	
	if(char == undefined)
		return true;
		
	if (char == 8 || char == 0)
		return true;
	
	if (char < 48 || char > 57)
		return false;
	
	if (e)
		char -= 48;
	else
		char = '';
		
	if (field.value.length > 28)
		return false;
	if (field.value.length == 0)
		field.value = '(' + field.value;
	else if (field.value.length == 3)
		field.value = field.value + ') ';
	else if (field.value.length == 4)
		field.value = field.value + ' ';
	else if (field.value.length == 9)
		field.value = field.value + '-';
	
	return true;
}
</script>