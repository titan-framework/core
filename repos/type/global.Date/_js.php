<script language="javascript" type="text/javascript" src="titan.php?target=loadFile&file=extra/DatePicker/js/lang/<?= Date::getLanguage () ?>.js"></script>
<script language="javascript" type="text/javascript" src="titan.php?target=loadFile&file=extra/DatePicker/js/datepicker.js"></script>
<script language="javascript" type="text/javascript">
'global.Date'.namespace ();

global.Date.bissext = function (year)
{
	return (year % 400 == 0) || (year % 4 == 0 && year % 100 != 0);
}

global.Date.validate = function (id, obj)
{
	var dayOfMonth = new Array (0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

	var day 	= $(id + '-dd');
	var month 	= $(id + '-mm');
	var year 	= $(id);

	if (obj && obj.value == 0)
	{
		day.value = 0;
		month.value = 0;
		year.value = 0;

		$('_HIDDEN_' + id).value = '0-0-0';

		return;
	}

	if (day.value == 0 || month.value == 0 || year.value == 0)
		return;

	if (global.Date.bissext (year.value))
		dayOfMonth [2]++;

	yearDate = dayOfMonth [Number (month.value)];

	if (yearDate < Number (day.value))
		day.value = yearDate;

	$('_HIDDEN_' + id).value = day.value + '-' + month.value + '-' + year.value;
}

global.Date.validateSearch = function (id, qualifier, obj)
{
	if (obj && obj.value == 0)
	{
		$(id + '-from-dd').value = 0;
		$(id + '-from-mm').value = 0;
		$(id = '-from').value = 0;

		$(id + '-to-dd').value = 0;
		$(id + '-to-mm').value = 0;
		$(id = '-to').value = 0;

		$('_HIDDEN_' + id + '-from').value = '0-0-0';
		$('_HIDDEN_' + id + '-to').value = '0-0-0';

		return;
	}

	var dayOfMonth = new Array (0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

	var day 	= $(id + qualifier + '-dd');
	var month 	= $(id + qualifier + '-mm');
	var year 	= $(id + qualifier);

	if (day.value == 0 || month.value == 0 || year.value == 0)
		return;

	if (year.value && global.Date.bissext (year.value))
		dayOfMonth [2]++;

	var yearDate = 0;

	if (month.value)
		yearDate = dayOfMonth [Number (month.value)];

	if (day.value && yearDate && yearDate < Number (day.value))
		day.value = yearDate;

	$('_HIDDEN_' + id + qualifier).value = day.value + '-' + month.value + '-' + year.value;
}

global.Date.clear = function (id)
{
	var day 	= $(id + '-dd');
	var month 	= $(id + '-mm');
	var year 	= $(id);

	day.value = 0;
	month.value = 0;
	year.value = 0;

	$('_HIDDEN_' + id).value = '0-0-0';
}

global.Date.clearSearch = function (id)
{
	$(id + '-from-dd').value = 0;
	$(id + '-from-mm').value = 0;
	$(id + '-from').value = 0;

	$(id + '-to-dd').value = 0;
	$(id + '-to-mm').value = 0;
	$(id + '-to').value = 0;

	$('_HIDDEN_' + id + '-from').value = '0-0-0';
	$('_HIDDEN_' + id + '-to').value = '0-0-0';
}
</script>
