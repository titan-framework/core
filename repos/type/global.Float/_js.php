<script language="javascript" type="text/javascript">
'global.Float'.namespace ();

global.Float.format = function (field, e, precision)
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
	number = number.replace (/,/g,'');
	number = String (parseInt (number,10));
	
	if (number == 'NaN')
		number = String ('0');
	
	if (number.length > 16)
		return false;
	
	number = number + char;
	
	while (number.length <= precision)
		number = String ('0') + number;
	
	var aux = number.substring (0, number.length - precision);
	
	var size = aux.length;
	
	var prefix = aux.substring (size - 3, size);
	
	size -= 3;
	
	while (size > 0)
	{
		prefix = aux.substring (size - 3, size) + '.' + prefix;
		
		size -= 3;
	}
	
	number = prefix + ',' + number.substring (number.length - precision, number.length)
	
	field.value = number;
	
	return false;
}
</script>