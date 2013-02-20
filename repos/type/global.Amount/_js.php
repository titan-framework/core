<script language="javascript" type="text/javascript">
'global.Amount'.namespace ();

global.Amount.format = function (field, e)
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
	
	number = String (parseInt (number, 10));
	
	if(number == 'NaN')
		number = String ('0');
	
	if(number.length > 16)
		return false;
	
	number = number + char;
	
	if(number.length == 0)
		number = String ('0');
	else
	{
		var size = number.length
		
		var prefix = number.substring (size - 3, size);
		
		size -= 3;
		
		while (size > 0)
		{
			prefix = number.substring (size - 3, size) + '.' + prefix;
			
			size -= 3;
		}
		
		number = prefix;
	}
	
	field.value = number;
	
	return false;
}
</script>