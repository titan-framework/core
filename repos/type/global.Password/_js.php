<script language="javascript" type="text/javascript">
'global.Password'.namespace ();

global.Password.validate = function (assign)
{
	var passwd = $('passwd_' + assign);
	var reply  = $('reply_' + assign);
	
	if (passwd.value != '')
		if (passwd.value == reply.value)
			return true;
		else
			alert ('Atenção! A senha e a confirmação não conferem.');
	else
		alert ('Atenção! Você deve inserir uma senha.');

	return false;
}
</script>