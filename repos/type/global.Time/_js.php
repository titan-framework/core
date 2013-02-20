<script language="javascript" type="text/javascript">
'global.Time'.namespace ();

global.Time.alter = function (id)
{
	var hidden = $(id);
	
	var hour = $('hour_' + id);
	var minute = $('minute_' + id);
	var second = $('second_' + id);
	
	hidden.value = hour.value + ':' + minute.value + ':' + second.value;
}
</script>