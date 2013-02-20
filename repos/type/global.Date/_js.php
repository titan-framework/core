<script language="javascript" type="text/javascript" src="titan.php?target=loadFile&file=extra/DatePicker/js/lang/<?= Date::getLanguage () ?>.js"></script>
<script language="javascript" type="text/javascript" src="titan.php?target=loadFile&file=extra/DatePicker/js/datepicker.js"></script>
<script language="javascript" type="text/javascript">
'global.Date'.namespace ();

global.Date.bissext = function (year)
{
    return (year % 400 == 0) || (year % 4 == 0 && year % 100 != 0);
}

global.Date.validate = function (id)
{
	var dayOfMonth = new Array (0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	
	var day 	= $(id + '-dd');
	var month 	= $(id + '-mm');
	var year 	= $(id);
	
	if (day.value == 0 || month.value == 0 || year.value == 0)
		return;
		
	if (global.Date.bissext (year.value))
		dayOfMonth [2]++;
	
	yearDate = dayOfMonth [Number (month.value)];
	
	if (yearDate < Number (day.value))
		day.value = yearDate;
	
	$('_HIDDEN_' + id).value = day.value + '-' + month.value + '-' + year.value;
}

global.Date.validateSearch = function (id)
{
	var dayOfMonth = new Array (0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	
	var day 	= $(id + '-dd');
	var month 	= $(id + '-mm');
	var year 	= $(id);
	
	if (year.value && global.Date.bissext (year.value))
		dayOfMonth [2]++;
	
	var yearDate = 0;
	
	if (month.value)
		yearDate = dayOfMonth [Number (month.value)];
	
	if (day.value && yearDate && yearDate < Number (day.value))
		day.value = yearDate;
	
	$('_HIDDEN_' + id).value = day.value + '-' + month.value + '-' + year.value;
}
</script>