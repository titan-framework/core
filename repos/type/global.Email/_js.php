<script language="javascript" type="text/javascript">
'global.Email'.namespace ();

global.Email.oBorder = '#AAA';
global.Email.oBack   = '#FFF url(titan.php?target=loadFile&file=interface/back/field.gif) top left no-repeat';
global.Email.oColor  = '#575556';

global.Email.saveOriginalColor = function (field)
{
	if (global.Email.oBorder == '#AAA' && field.style.borderColor != '')
		global.Email.oBorder = field.style.borderColor;
		
	if (global.Email.oBack == '#FFF url(titan.php?target=loadFile&file=interface/back/field.gif) top left no-repeat' && field.style.background != '')
		global.Email.oBack = field.style.background;
	
	if (global.Email.oColor == '#575556' && field.style.color != '')
		global.Email.oColor = field.style.color;
	
	return true;
}

global.Email.format = function (field, e)
{
	var regex = new RegExp (/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+[\.]{1}[a-zA-Z]{2,4}$/g);
	
	if (regex.test (field.value))
	{
		field.style.borderColor = '#090';
		field.style.color = '#090';
		field.style.background = '#CCEBCC';
	}
	else
	{
		field.style.borderColor = '#900';
		field.style.color = '#900';
		field.style.background = '#EBCCCC';
	}
	
	return true;
}

global.Email.setOriginalColor = function (field)
{
	field.style.borderColor = global.Email.oBorder;
	field.style.background = global.Email.oBack;
	field.style.color = global.Email.oColor;
	
	global.Email.oBorder = '#AAA';
	global.Email.oBack = '#FFF url(titan.php?target=loadFile&file=interface/back/field.gif) top left no-repeat';
	global.Email.oColor = '#575556';
	
	return true;
}
</script>