function openPopup (url, name, width, height, scrolling, status, resizable)
{
	openPopUp (url, name, width, height, scrolling, status, resizable);
}

function openPopUp (url, name, width, height, scrolling, status, resizable)
{
	if (!width)
		width = screen.availWidth - 10;

	if (!height)
		height = screen.availHeight - 40;

	if (!scrolling)
		scrolling = 'auto';

	if (!status)
		status = 'no';

	if (!resizable)
		resizable = 'yes';

	PopUp = window.open (url, name, 'width=' + width + ',height=' + height + ',scrollbars=' + scrolling + ',toolbar=no,location=no,status=' + status + ',menubar=no,resizable=' + resizable + ',left=100,top=100');
}

function openPrintPopup (queryString)
{
	PopUp = window.open(queryString, 'Imprimir', 'width=500,height=300,toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,left=100,top=100');
}

function changeAction (newAction, formName)
{
	var form = document.getElementById (formName);

	form.action = newAction;

	form.submit ();
}

function start ()
{
	if (typeof window.event != 'undefined')
		document.onkeydown = function ()
		{
			if(event.keyCode == 8)
				window.event.keyCode = 127;
		}

	resizeBody ();

	parent.banner.enableMenu ();

	hideWait ();

	if (this.runOnLoad == null)
		return false;

	runOnLoad ();
}

function end ()
{
	parent.banner.disableMenu ();

	showWait ();

	if (this.runOnUnload == null)
		return false;

	runOnUnload ();
}

function resizeBody ()
{
	var height = 0;

	if(!window.innerWidth)
		if(!(document.documentElement.clientWidth == 0))
			height = document.documentElement.clientHeight;
		else
			height = document.body.clientHeight;
	else
		height = window.innerHeight;

	document.getElementById ('idBody').style.height = (height - 78) + 'px';
}

function sendLetter (counter, character)
{
	var hidden = document.getElementById ('keyboardHiddenValue' + counter);

	if (character)
		hidden.value = character;
}

if (typeof Spinner === 'function')
	var titanWaitSpinner = new Spinner ({
		lines: 13,
		length: 28,
		width: 14,
		radius: 42,
		scale: 1,
		corners: 1,
		color: '#FFF',
		opacity: 0.6,
		rotate: 0,
		direction: 1,
		speed: 1,
		trail: 60,
		fps: 20,
		zIndex: 2e9,
		className: '',
		top: '50%',
		left: '50%',
		shadow: true,
		hwaccel: false,
		position: 'absolute'
	});

var titanWaitBlockLayer = null;

function showWait ()
{
	parent.banner.document.getElementById('idWait').style.display = 'block';

	parent.banner.disableMenu ();

	if (titanWaitBlockLayer != null && titanWaitBlockLayer.style.display == 'block')
		return;

	if (titanWaitBlockLayer != null)
		titanWaitBlockLayer.style.display = 'block';
	else
	{
		titanWaitBlockLayer = document.createElement ('div');
		titanWaitBlockLayer.className = 'titanWaitBlockLayer';
		$(document.body).appendChild (titanWaitBlockLayer);
	}

	if (titanWaitSpinner)
		titanWaitSpinner.spin ($(document.body));
}

function hideWait ()
{
	parent.banner.document.getElementById('idWait').style.display = 'none';

	parent.banner.enableMenu ();

	if (titanWaitSpinner)
		titanWaitSpinner.stop ();

	if (titanWaitBlockLayer != null)
		titanWaitBlockLayer.style.display = 'none';
}

var searchParams = false;

function showSearch ()
{
	var div = document.getElementById ('idSearch');
	var params;

	if (div.style.display == '')
	{
		div.style.display = 'none';

		if (searchParams)
			if (params = document.getElementById ('idSearchParams'))
				params.style.display = '';
	}
	else
	{
		if (params = document.getElementById ('idSearchParams'))
			if (params.style.display == '')
			{
				params.style.display = 'none';
				searchParams = true;
			}

		div.style.display = '';
	}
}

function showGroup (id)
{
	var fieldset = document.getElementById ('group_' + id);

	if (fieldset.className == 'formGroup')
		fieldset.className = 'formGroupCollapse';
	else
		fieldset.className = 'formGroup';
}

function message (content, w, h, inText, title, type)
{
	if (title == null) title = '';

	if (w == null) w = 500;

	if (h == null) h = 120;

	if (inText == null || inText)
	{
		switch (type)
		{
			case 'SUCCESS':
				color = '090';
				image = 'success.png';
				break;

			case 'ERROR':
				color = '900';
				image = 'error.png';
				break;

			case 'WARNING':
			default:
				color = 'A85C00';
				image = 'warning.png';
		}

		var content = '<table border="0">\
			<tr>\
				<td rowspan="2">\
					<img src="titan.php?target=loadFile&amp;file=interface/image/' + image + '" border="0" style="margin-right: 10px;" />\
				</td>\
				<td style="color: #' + color + '; font-family: Helvetica, sans-serif, Arial; font-weight: bold; font-size: 12px; text-align: justify;">' + content + '</td>\
			</tr>\
		</table>';
	}

	Modalbox.show (content, { title: title, width: w, height: h });
}

function rssLink (url)
{
	var monitor = '';

	if (tAjax.tableExists ('_rss'))
		monitor = '<input type="button" class="button" value="Monitorar" style="color: #DA5E29; border-color: #DA5E29;" onclick="JavaScript: addFeed (\'' + url + '\');" />';

	var source = '<table border="0">\
		<tr>\
			<td rowspan="2">\
				<img src="titan.php?target=loadFile&amp;file=interface/image/rss.png" border="0" style="margin-right: 10px;" />\
			</td>\
			<td>\
				<div id="copy_clipboard" style="color: #900; font-weight: bold; text-align: left; width: 300px; height: 35px; line-height: 20px; white-space: nowrap; overflow: scroll; margin: 0 auto; border: #CCC 1px solid; padding: 5px;">' + url + '</div>\
			</td>\
		</tr>\
		<tr>\
			<td style="text-align: center;">\
				' + monitor + '\
				<input type="button" class="button" value="Copiar para o Clipboard" onclick="JavaScript: clipboard (\'copy_clipboard\');" />\
				<input type="button" class="button" value="Fechar" onclick="JavaScript: Modalbox.hide ();" />\
			</td>\
		</tr>\
	</table>';

	Modalbox.show (source, { title: 'Feed RSS', width: 460 });
}

function addFeed (url)
{
	 Modalbox.hide ();

	 showWait ();

	 tAjax.addFeed (url);

	 tAjax.delay (function () { hideWait (); });
}

function clipboard (div)
{
	if (navigator.appName == "Microsoft Internet Explorer" && navigator.appVersion >= "4.0")
	{
		for (i = 0 ; i < document.all.length ; i++)
			document.all(i).unselectable = "on";

		document.getElementById(div_id).unselectable = "off";
		document.getElementById(div_id).focus();

		document.execCommand('SelectAll');
		document.execCommand('Copy');

		for (i=0; i<document.all.length; i++)
			document.all(i).unselectable = "off";
	}
	else
		alert("Desculpe-nos, este recurso só está disponível para Internet Exploder.\nPara os demais browsers, selecione o link acima e pressione CTRL+C.");
}

var loadInPlaceIds = new Array ();
var loadInPlaceEls = new Array ();

function loadInPlace (id, element, button)
{
	if (typeof (element) == 'undefined')
		return false;

	var content = $('_CONTENT_' + id);

	var assign = element.id;

	for (var i = 0 ; i < loadInPlaceEls.length ; i++)
	{
		if (loadInPlaceEls [i] == assign)
		{
			button.onclick = function () { showInPlace (id, $(assign)); };

			hideWait ();

			showInPlace (id, $(assign));

			return false;
		}

		$(loadInPlaceEls [i]).style.display = 'none';
		$('_ROW_' + loadInPlaceIds [i]).style.display = 'none';
		$('_ITEM_' + loadInPlaceIds [i]).className = 'cTableItem';
	}

	loadInPlaceEls [i] = assign;
	loadInPlaceIds [i] = id;

	content.appendChild (element);

	button.onclick = function () { showInPlace (id, element); };

	$('_ITEM_' + id).className = 'cTableItemActive';
	$('_ROW_' + id).style.display = '';

	return false;
}

function showInPlace (id, element)
{
	var row = $('_ROW_' + id);
	var itm = $('_ITEM_' + id);

	if (element.style.display == '')
	{
		element.style.display = 'none';
		row.style.display = 'none';
		itm.className = 'cTableItem';
	}
	else
	{
		for (var i = 0 ; i < loadInPlaceEls.length ; i++)
		{
			$(loadInPlaceEls [i]).style.display = 'none';
			$('_ROW_' + loadInPlaceIds [i]).style.display = 'none';
			$('_ITEM_' + loadInPlaceIds [i]).className = 'cTableItem';
		}

		row.style.display = '';
		element.style.display = '';
		itm.className = 'cTableItemActive';
	}

	return false;
}

/* TODO: Send this function to [repos]/icon/InPlace/_js.php */

function inPlaceAction (id, action, section, button)
{
	var assign = '_IP_ACTION_' + id + '_' + action + '_';

	showWait ();

	var iframe = document.createElement ('iframe');
	iframe.id = assign;
	iframe.className = 'inPlaceAction';
	iframe.style.height = '50px;';
	iframe.style.display = '';
	iframe.src = 'titan.php?target=inPlace&toSection=' + section + '&toAction=' + action + '&itemId=' + id + '&assign=' + assign;

	loadInPlace (id, iframe, button);
}

/* TODO: Send this function to [repos]/icon/Status/_js.php */

function inPlaceStatus (icon, id, table, primary, column, msg, button, opts)
{
	var assign = '_IP_STATUS_' + icon + '_' + id + '_' + table + '_' + column + '_', row, col, b, aux;

	var element = document.createElement ('table');
	element.id = assign;
	element.className = 'inPlaceStatus';
	element.style.display = '';

	row = document.createElement ('tr');

	if (msg != '')
	{
		col = document.createElement ('td');

		col.innerHTML = msg;

		row.appendChild (col);
	}

	var actual = tAjax.inPlaceStatusValue (id, table, primary, column);

	col = document.createElement ('td');
	col.style.textAlign = 'right';

	for (var i = 0; i < opts.length; i++)
	{
		if (opts [i].value == actual)
			continue;

		b = document.createElement ('input');
		b.type = 'button';
		b.className = 'button';
		b.value = opts [i].label;
		b.style.color = opts [i].color != '' ? opts [i].color : '#575556';
		b.style.borderColor = opts [i].color != '' ? opts [i].color : '#575556';

		eval ("b.onclick = function () { inPlaceStatusChange ('" + id + "', '" + table + "', '" + primary + "', '" + column + "', '" + opts [i].value + "', '" + assign + "'); }");

		col.appendChild (b);
	}

	row.appendChild (col);

	element.appendChild (row);

	loadInPlace (id, element, button);
}

function inPlaceStatusChange (id, table, primary, column, value, assign)
{
	showWait ();

	$(assign).style.display = 'none';

	tAjax.inPlaceStatusChange (id, table, primary, column, value, function () {
		window.location.reload ();
	});

	return false;
}

function crossEvent (e)
{
	e = e ? e : (window.event ? window.event : null);

	if (e)
	{
		this.originalEvent = e;
		this.type = e.type;
		this.screenX = e.clientX;
		this.screenY = e.clientY;

		// IE: srcElement
		this.target = e.target ? e.target : e.srcElement;

		// N4: modificadores
		if (e.modifiers)
		{
			this.altKey   = e.modifiers & Event.ALT_MASK;
			this.ctrlKey  = e.modifiers & Event.CONTROL_MASK;
			this.shiftKey = e.modifiers & Event.SHIFT_MASK;
			this.metaKey  = e.modifiers & Event.META_MASK;
		}
		else
		{
			this.altKey   = e.altKey;
			this.ctrlKey  = e.ctrlKey;
			this.shiftKey = e.shiftKey;
			this.metaKey  = e.metaKey;
		}

		// N4: which // N6+: charCode
		this.charCode = !isNaN(e.charCode) ? e.charCode : !isNaN(e.keyCode) ? e.keyCode : e.which;
		this.keyCode = !isNaN(e.keyCode) ? e.keyCode : e.which;
		this.button = !isNaN(e.button) ? e.button: !isNaN(e.which) ? e.which-1 : null;
		this.debug = "c:" + e.charCode + " k:" + e.keyCode + " b:" + e.button + " w:" + e.which;
	}
}

function sendBugReport ()
{
	showWait ();

	var formData = xoad.html.exportForm ('bugReport');

	tAjax.sendBugReport (formData, function () {
		tAjax.showMessages ();

		Modalbox.hide ();

		hideWait ();
	});
}

function changeLanguage (language)
{
	showWait ();

	tAjax.changeLanguage (language, function () {
		tAjax.showMessages ();

		parent.reloadFrames ();
	});
}

function readAlert (id)
{
	tAjax.readAlert (id, function () {
		$('_TITAN_ALERT_' + id).className = 'read';

		$('_TITAN_ALERT_' + id).children[0].onmouseover = function () { return false };

		parent.banner.verifyAlerts ();
	});

	return false;
}

function deleteAlert (id)
{
	showWait ();

	tAjax.deleteAlert (id, function () {
		var parent = $('_TITAN_ALERT_' + id).parentNode;

		parent.removeChild ($('_TITAN_ALERT_' + id));

		if (!parent.children.length)
			Modalbox.hide ();

		hideWait ();
	});
}

function copyItem (id, action, section, button)
{
	showWait ();

	var newId = tAjax.copyItem (id);

	if (!newId)
		tAjax.delay (function () {
			tAjax.showMessages ();

			hideWait ();
		});
	else
		document.location = 'titan.php?target=body&toSection=' + section + '&toAction=' + action + '&itemId=' + newId;
}

function formatMoney (value)
{
	var numbers = (value.toString ()).split ('.');

	var buffer = '', aux = '';

	while (true)
	{
		aux = (numbers [0] % 1000).toString ();

		buffer = aux + buffer;

		numbers [0] = parseInt (numbers [0] / 1000);

		if (!numbers [0])
			break;

		switch (aux.length)
		{
			case 1:
				buffer = '.00' + buffer;
				break;

			case 2:
				buffer = '.0' + buffer;
				break;

			default:
				buffer = '.' + buffer;
		}
	}

	if (typeof (numbers [1]) == 'undefined')
		numbers [1] = '00';
	else if (numbers [1].length < 2)
		numbers [1] += '0';

	return buffer + ',' + numbers [1];
}

function deleteItemFromShoppingCart (id)
{
	showWait ();

	tAjax.deleteItemFromShoppingCart (id, function () {
		var parent = $('_TITAN_SHOP_' + id).parentNode;

		parent.removeChild ($('_TITAN_SHOP_' + id));

		if (parent.children.length <= 2)
			Modalbox.hide ();
		else
			updateShoppingCart ();

		hideWait ();
	});
}

function clone (obj)
{
	if (null == obj || 'object' != typeof obj)
		return obj;

	if (obj instanceof Date)
	{
		var copy = new Date ();

		copy.setTime (obj.getTime ());

		return copy;
	}

	if (obj instanceof Array)
	{
		var copy = [];

		for (var i = 0, len = obj.length; i < len; i++)
			copy [i] = clone (obj [i]);

		return copy;
	}

	if (obj instanceof Object)
	{
		var copy = {};

		for (var attr in obj)
			if (obj.hasOwnProperty (attr)) copy [attr] = clone (obj [attr]);

		return copy;
	}

	return null;
}

function getBrowserInfo ()
{
	var ua = navigator.userAgent, tem, M = ua.match (/(opera|chrome|safari|firefox|msie|trident(?=\/))\/?\s*(\d+)/i) || [];

	if(/trident/i.test (M [1]))
	{
		tem = /\brv[ :]+(\d+)/g.exec(ua) || [];

		return { name: 'IE', version: (tem[1] || '') };
	}

	if(M[1]==='Chrome')
	{
		tem = ua.match(/\bOPR\/(\d+)/)

		if (tem!=null)
			return { name: 'Opera', version: tem[1] };
	}

	M = M [2] ? [M [1], M [2]] : [navigator.appName, navigator.appVersion, '-?'];

	if ((tem = ua.match(/version\/(\d+)/i)) != null) { M.splice (1, 1, tem[1]); }

	return { name: M [0], version: M[1] };
}

function getWindowSize ()
{
	oBody = document.body;

	h = oBody.scrollHeight + (oBody.offsetHeight - oBody.clientHeight);
	w  = oBody.scrollWidth  + (oBody.offsetWidth  - oBody.clientWidth);

	h = h > 0 ? h : window.innerHeight;
	w = w > 0 ? w : window.innerWidth;

	return { width: w, height: h };
}
