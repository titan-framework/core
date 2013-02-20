<script language="javascript" type="text/javascript">
'global.Rga'.namespace ();

global.Rga.format = function (field, e)
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
	
	if (field.value.length > 14)
		return false;
	
	if (field.value.length == 4)
		field.value = field.value + '.';
	else if (field.value.length == 9)
		field.value = field.value + '.';
	else if (field.value.length == 13)
		field.value = field.value + '-';
	
	return true;
}
</script>