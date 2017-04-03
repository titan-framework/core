var chatIsConnected = false;

function showChat ()
{
	var button = document.getElementById ('idChat');

	if (button.style.display == '')
	{
		parent.document.body.rows = '86px,*';

		pfc.swap_minimize_maximize ();

		button.style.display = 'none';
	}
	else
	{
		parent.document.body.rows = '373px,*';

		button.style.display = '';

		pfc.swap_minimize_maximize ();

		if (!chatIsConnected)
		{
			pfc.connect_disconnect ();

			chatIsConnected = true;
		}
	}
}

function showMenu ()
{
	var button = document.getElementById ('idChat');

	if (button.style.display == '')
	{
		parent.document.body.rows = '86px,*';

		pfc.swap_minimize_maximize ();

		button.style.display = 'none';
	}

	parent.body.showMenu ();
}

function enableMenu ()
{
	var menu = document.getElementById ('idMenu');

	menu.firstElementChild.className = 'fa fa-bars fa-2x';

	menu.onclick = function () { showMenu (); };
}

function disableMenu ()
{
	var menu = document.getElementById ('idMenu');

	menu.firstElementChild.className = 'fa fa-refresh fa-spin fa-2x';

	menu.onclick = function () {};
}

var menuDialogAlreadyShowed = false;

function showMenuDialog ()
{
	if (menuDialogAlreadyShowed)
		return;

	menuDialogAlreadyShowed = true;

	var div = document.getElementById ('idMenuDialog')

	div.style.visibility = 'visible';
	div.style.opacity = '1';

	setTimeout (function () {
		div.style.visibility = 'hidden';
		div.style.opacity = '0';
	}, 3000);
}

function urlencode (str)
{
	var histogram = {}, tmp_arr = [];
	var ret = (str+'').toString();

	var replacer = function(search, replace, str) {
		var tmp_arr = [];
		tmp_arr = str.split(search);
		return tmp_arr.join(replace);
	};

	// The histogram is identical to the one in urldecode.
	histogram["'"]   = '%27';
	histogram['(']   = '%28';
	histogram[')']   = '%29';
	histogram['*']   = '%2A';
	histogram['~']   = '%7E';
	histogram['!']   = '%21';
	histogram['%20'] = '+';
	histogram['\u20AC'] = '%80';
	histogram['\u0081'] = '%81';
	histogram['\u201A'] = '%82';
	histogram['\u0192'] = '%83';
	histogram['\u201E'] = '%84';
	histogram['\u2026'] = '%85';
	histogram['\u2020'] = '%86';
	histogram['\u2021'] = '%87';
	histogram['\u02C6'] = '%88';
	histogram['\u2030'] = '%89';
	histogram['\u0160'] = '%8A';
	histogram['\u2039'] = '%8B';
	histogram['\u0152'] = '%8C';
	histogram['\u008D'] = '%8D';
	histogram['\u017D'] = '%8E';
	histogram['\u008F'] = '%8F';
	histogram['\u0090'] = '%90';
	histogram['\u2018'] = '%91';
	histogram['\u2019'] = '%92';
	histogram['\u201C'] = '%93';
	histogram['\u201D'] = '%94';
	histogram['\u2022'] = '%95';
	histogram['\u2013'] = '%96';
	histogram['\u2014'] = '%97';
	histogram['\u02DC'] = '%98';
	histogram['\u2122'] = '%99';
	histogram['\u0161'] = '%9A';
	histogram['\u203A'] = '%9B';
	histogram['\u0153'] = '%9C';
	histogram['\u009D'] = '%9D';
	histogram['\u017E'] = '%9E';
	histogram['\u0178'] = '%9F';

	// Begin with encodeURIComponent, which most resembles PHP's encoding functions
	ret = encodeURIComponent(ret);

	for (search in histogram) {
		replace = histogram[search];
		ret = replacer(search, replace, ret) // Custom replace. No regexing
	}

	// Uppercase for full PHP compatibility
	return ret.replace(/(\%([a-z0-9]{2}))/g, function(full, m1, m2) {
		return "%"+m2.toUpperCase();
	});

	return ret;
}

function searchDefault (field, str)
{
	if (field.value == str)
		field.value = '';
	else if (field.value.length == 0)
		field.value = str;
}

function searchSend (field, e)
{
	e = e ? e : (window.event ? window.event : null);

	if (e)
	{
		var charCode = !isNaN(e.charCode) && e.charCode ? e.charCode : !isNaN(e.keyCode) && e.keyCode ? e.keyCode : e.which;

		if (charCode == 13)
			parent.body.location = 'titan.php?target=lucene&query=' + urlencode (field.value);
	}
}

function getTimeZone ()
{
	tmSummer = new Date(Date.UTC(2005, 6, 30, 0, 0, 0, 0));
	so = -1 * tmSummer.getTimezoneOffset();
	tmWinter = new Date(Date.UTC(2005, 12, 30, 0, 0, 0, 0));
	wo = -1 * tmWinter.getTimezoneOffset();

	if (-660 == so && -660 == wo) return 'Pacific/Midway';
	if (-600 == so && -600 == wo) return 'Pacific/Tahiti';
	if (-570 == so && -570 == wo) return 'Pacific/Marquesas';
	if (-540 == so && -600 == wo) return 'America/Adak';
	if (-540 == so && -540 == wo) return 'Pacific/Gambier';
	if (-480 == so && -540 == wo) return 'US/Alaska';
	if (-480 == so && -480 == wo) return 'Pacific/Pitcairn';
	if (-420 == so && -480 == wo) return 'US/Pacific';
	if (-420 == so && -420 == wo) return 'US/Arizona';
	if (-360 == so && -420 == wo) return 'US/Mountain';
	if (-360 == so && -360 == wo) return 'America/Guatemala';
	if (-360 == so && -300 == wo) return 'Pacific/Easter';
	if (-300 == so && -360 == wo) return 'US/Central';
	if (-300 == so && -300 == wo) return 'America/Bogota';
	if (-240 == so && -300 == wo) return 'US/Eastern';
	if (-240 == so && -240 == wo) return 'America/Caracas';
	if (-240 == so && -180 == wo) return 'America/Santiago';
	if (-180 == so && -240 == wo) return 'Canada/Atlantic';
	if (-180 == so && -180 == wo) return 'America/Montevideo';
	if (-180 == so && -120 == wo) return 'America/Sao_Paulo';
	if (-150 == so && -210 == wo) return 'America/St_Johns';
	if (-120 == so && -180 == wo) return 'America/Godthab';
	if (-120 == so && -120 == wo) return 'America/Noronha';
	if (-60 == so && -60 == wo) return 'Atlantic/Cape_Verde';
	if (0 == so && -60 == wo) return 'Atlantic/Azores';
	if (0 == so && 0 == wo) return 'Africa/Casablanca';
	if (60 == so && 0 == wo) return 'Europe/London';
	if (60 == so && 60 == wo) return 'Africa/Algiers';
	if (60 == so && 120 == wo) return 'Africa/Windhoek';
	if (120 == so && 60 == wo) return 'Europe/Amsterdam';
	if (120 == so && 120 == wo) return 'Africa/Harare';
	if (180 == so && 120 == wo) return 'Europe/Athens';
	if (180 == so && 180 == wo) return 'Africa/Nairobi';
	if (240 == so && 180 == wo) return 'Europe/Moscow';
	if (240 == so && 240 == wo) return 'Asia/Dubai';
	if (270 == so && 210 == wo) return 'Asia/Tehran';
	if (270 == so && 270 == wo) return 'Asia/Kabul';
	if (300 == so && 240 == wo) return 'Asia/Baku';
	if (300 == so && 300 == wo) return 'Asia/Karachi';
	if (330 == so && 330 == wo) return 'Asia/Calcutta';
	if (345 == so && 345 == wo) return 'Asia/Katmandu';
	if (360 == so && 300 == wo) return 'Asia/Yekaterinburg';
	if (360 == so && 360 == wo) return 'Asia/Colombo';
	if (390 == so && 390 == wo) return 'Asia/Rangoon';
	if (420 == so && 360 == wo) return 'Asia/Almaty';
	if (420 == so && 420 == wo) return 'Asia/Bangkok';
	if (480 == so && 420 == wo) return 'Asia/Krasnoyarsk';
	if (480 == so && 480 == wo) return 'Australia/Perth';
	if (540 == so && 480 == wo) return 'Asia/Irkutsk';
	if (540 == so && 540 == wo) return 'Asia/Tokyo';
	if (570 == so && 570 == wo) return 'Australia/Darwin';
	if (570 == so && 630 == wo) return 'Australia/Adelaide';
	if (600 == so && 540 == wo) return 'Asia/Yakutsk';
	if (600 == so && 600 == wo) return 'Australia/Brisbane';
	if (600 == so && 660 == wo) return 'Australia/Sydney';
	if (630 == so && 660 == wo) return 'Australia/Lord_Howe';
	if (660 == so && 600 == wo) return 'Asia/Vladivostok';
	if (660 == so && 660 == wo) return 'Pacific/Guadalcanal';
	if (690 == so && 690 == wo) return 'Pacific/Norfolk';
	if (720 == so && 660 == wo) return 'Asia/Magadan';
	if (720 == so && 720 == wo) return 'Pacific/Fiji';
	if (720 == so && 780 == wo) return 'Pacific/Auckland';
	if (765 == so && 825 == wo) return 'Pacific/Chatham';
	if (780 == so && 780 == wo) return 'Pacific/Enderbury'
	if (840 == so && 840 == wo) return 'Pacific/Kiritimati';
	return 'US/Pacific';
}
