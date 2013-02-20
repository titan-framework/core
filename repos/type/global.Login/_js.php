<script language="javascript" type="text/javascript">
'global.Login'.namespace ();

global.Login.format = function (field, e)
{
	field.value = field.value.replace (new RegExp (/[^0-9a-z_\-\.]/g), '');
		
	return true;
}
</script>