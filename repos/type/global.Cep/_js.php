<script language="javascript" type="text/javascript">
'global.Cep'.namespace ();

global.Cep.format = function (field, e)
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
		
	if (field.value.length > 10)
		return false;
	
	if (field.value.length == 2)
		field.value = field.value + '.';
	else if (field.value.length == 6)
		field.value = field.value + '-';
	
	return true;
}
</script>