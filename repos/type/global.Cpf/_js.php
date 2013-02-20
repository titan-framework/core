<script language="javascript" type="text/javascript">
'global.Cpf'.namespace ();

global.Cpf.format = function (field, e)
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
	
	if (field.value.length > 14)
		return false;
	
	if (field.value.length == 3)
		field.value = field.value + '.';
	else if (field.value.length == 7)
		field.value = field.value + '.';
	else if (field.value.length == 11)
		field.value = field.value + '-';
		
	return true;
}
</script>