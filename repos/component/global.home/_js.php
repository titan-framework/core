<script language="javascript" type="text/javascript" src="titan.php?target=packer&files=dragable-ajax,dragable-boxes,sha1"></script>
<script language="javascript" type="text/javascript">
function createARSSBox(url,columnIndex,heightOfBox,maxRssItems,minutesBeforeReload,uniqueIdentifier,state)
{
	if(!heightOfBox)heightOfBox = '0';
	if(!minutesBeforeReload)minutesBeforeReload = '0';

	var tmpIndex = createABox(columnIndex,heightOfBox,true);

	if(useCookiesToRememberRSSSources && !cookieRSSSources[url])
	{
		cookieRSSSources[url] = cookieCounter;
		Set_Cookie(nameOfCookie + cookieCounter,url + '#;#' + columnIndex + '#;#' + maxRssItems + '#;#' + heightOfBox + '#;#' + minutesBeforeReload + '#;#' + uniqueIdentifier + '#;#' + state  ,60000);
		cookieCounter++;
	}

	dragableBoxesArray[tmpIndex]['rssUrl'] = url;
	dragableBoxesArray[tmpIndex]['maxRssItems'] = maxRssItems?maxRssItems:100;
	dragableBoxesArray[tmpIndex]['minutesBeforeReload'] = minutesBeforeReload;
	dragableBoxesArray[tmpIndex]['heightOfBox'] = heightOfBox;
	dragableBoxesArray[tmpIndex]['uniqueIdentifier'] = uniqueIdentifier;
	dragableBoxesArray[tmpIndex]['state'] = state;

	if(state==0){
		showHideBoxContent(false,document.getElementById('dragableBoxExpand' + tmpIndex));
	}

	staticObjectArray[uniqueIdentifier] = tmpIndex;

	var tmpInterval = false;
	if(minutesBeforeReload && minutesBeforeReload>0){
		var tmpInterval = setInterval("reloadRSSData(" + tmpIndex + ")",(minutesBeforeReload*1000*60));
	}

	dragableBoxesArray[tmpIndex]['intervalObj'] = tmpInterval;

	addRSSEditContent(document.getElementById('dragableBoxHeader' + tmpIndex))

	if(!document.getElementById('dragableBoxContent' + tmpIndex).innerHTML)document.getElementById('dragableBoxContent' + tmpIndex).innerHTML = 'loading RSS data';

	if(url.length>0 && url!='undefined')
	{
		var ajaxIndex = ajaxObjects.length;
		ajaxObjects[ajaxIndex] = new sack();
		if(!maxRssItems)maxRssItems = 100;
		ajaxObjects[ajaxIndex].requestFile = 'titan.php?target=readRss&rssURL=' + escape(url) + '&maxRssItems=' + maxRssItems;	// Specifying which file to get
		ajaxObjects[ajaxIndex].onCompletion = function(){ showRSSData(ajaxIndex,tmpIndex); };	// Specify function that will be executed after file has been found
		ajaxObjects[ajaxIndex].runAJAX();		// Execute AJAX function
	}
	else
		hideHeaderOptionsForStaticBoxes(tmpIndex);
}

function reloadRSSData(numericId)
{
	var ajaxIndex = ajaxObjects.length;
	ajaxObjects[ajaxIndex] = new sack();
	showStatusBarMessage(numericId,'Loading data...');
	ajaxObjects[ajaxIndex].requestFile = 'titan.php?target=readRss&rssURL=' + escape(dragableBoxesArray[numericId]['rssUrl']) + '&maxRssItems=' + dragableBoxesArray[numericId]['maxRssItems'];	// Specifying which file to get
	ajaxObjects[ajaxIndex].onCompletion = function(){ showRSSData(ajaxIndex,numericId); };	// Specify function that will be executed after file has been found
	ajaxObjects[ajaxIndex].runAJAX();		// Execute AJAX function
}

function createDefaultBoxes()
{
	eval (ajax.getBoxes ());

	<? ob_start () ?>
	<b><?= __ ('Name') ?>:</b> <?= $user->getName () ?><br />\
	<b>Login:</b> <?= $user->getLogin () ?><br />\
	<b>E-mail:</b> <?= $user->getEmail () ?><br />\
	<b><?= __ ('User since') ?>:</b> <?= $user->getCreateDate () ?><br />\
	<b><?= __ ('Last Logon') ?>:</b> <?= $user->getCreateDate () == $user->getLastLogon () ? __ ('Never had logged.') : $user->getLastLogon () ?><br />\
	<? $str = ob_get_clean () ?>

	var htmlContentOfNewBox = '<div style="line-height: 15px;"><?= $str ?></div>';

	var titleOfNewBox = '<?= __ ('Informations') ?>';
	var newIndex = createABox (1, 80, false, 'staticObject1');
	document.getElementById ('dragableBoxContent' + newIndex).innerHTML = htmlContentOfNewBox;
	document.getElementById ('dragableBoxHeader_txt' + newIndex).innerHTML = titleOfNewBox;
	hideHeaderOptionsForStaticBoxes(staticObjectArray['staticObject1']);
	disableBoxDrag (staticObjectArray['staticObject1']);
}

function showChangePasswd ()
{
	var div = document.getElementById ('idSearch');

	if (div.style.display == '')
		div.style.display = 'none';
	else
		div.style.display = '';
}
function changePassword (form)
{
	var objForm = document.getElementById (form);

	if (objForm.newPassword.value.replace(/ /g,'') == '')
	{
		alert ('<?= __ ('The password cannot be empty and neither contain empty spaces!') ?>');

		return false;
	}

	if (objForm.newPassword.value == '<?= $user->getLogin () ?>')
	{
		alert ('<?= __ ('The password cannot be equal with your login!') ?>');

		return false;
	}

	if (objForm.newPassword.value != objForm.repeat.value)
	{
		objForm.password.value = '';
		objForm.newPassword.value = '';
		objForm.repeat.value = '';

		alert ('<?= __ ('The both field values ("New Password" and "Confirm Password") must be equal') ?>');

		return false;
	}

	showWait ();
	
	var passwd = objForm.password.value;
	var newPas = objForm.newPassword.value;
	
	<? if (Security::singleton ()->encryptOnClient ()) { echo 'passwd = hex_sha1 (passwd); newPas = hex_sha1 (newPas);'; } ?>
	
	if (ajax.changePasswd (passwd, newPas))
		showChangePasswd ();

	ajax.delay (function () {
		objForm.password.value = '';
		objForm.newPassword.value = '';
		objForm.repeat.value = '';

		ajax.showMessages ();

		hideWait ();
	});
}
function strong (obj, e)
{
	if (e) car = (window.Event) ? e.which : e.keyCode;
	
	$('rowStrong').style.display = '';
	
	var passwd = obj.value + String.fromCharCode (car);
	
	var ok = 0, str = '<label style="color: #900;"><?= __ ('Too Short') ?></label>', src = 'very_weak';
	
	if (passwd.length > 5)
	{
		if (passwd.match(/[A-Z]/)) ok++;
		
		if (passwd.match(/[a-z]/)) ok++;
		
		if (passwd.match(/[0-9]/)) ok++;
		
		if (passwd.match(/[@#$%&!?*\[\])(-+=^.\/\\]/)) ok++;
		
		switch (ok)
		{
			case 0:
				str = '<label style="color: #900;"><?= __ ('Very Weak') ?></label>';
				src = 'very_weak';
				break;
			
			case 1:
				str = '<label style="color: #FC3;"><?= __ ('Weak') ?></label>';
				src = 'very_fair';
				break;
			
			case 2:
				str = '<label style="color: #FC3;"><?= __ ('Regular') ?></label>';
				src = 'fair';
				break;
			
			case 3:
				str = '<label style="color: #69C;"><?= __ ('Strong') ?></label>';
				src = 'good';
				break;
			
			default:
				str = '<label style="color: #008000;"><?= __ ('Very Strong') ?></label>';
				src = 'strong';
				break;
		}
	}
	
	$('idStrong').innerHTML = 'Força da senha: ' + str;
	$('imgStrong').src = 'titan.php?target=loadFile&file=interface/image/passwd.' + src + '.gif';
}
function showSocialNetworks ()
{
	showWait ();
	
	<?
	$drivers = array ();
	
	if (Social::isActive ())
		while ($driver = Social::singleton ()->getSocialNetwork ())
		{
			$enabled = $driver->isEnabled ();
			
			if ($enabled)
				$link = '<a class="disconnect" href="#" onclick="JavaScript: disconnectFromSocialNetwork (\\\''. $driver->getName () .'\\\', \\\''. $driver->getPath () .'\\\', \\\''. $driver->getConnectUrl () .'\\\'); return false;">'. __ ('Revoke Access') .'</a>';
			else
				$link = '<a class="connect" href="#" onclick="JavaScript: showWait (); parent.document.location = \\\''. $driver->getConnectUrl () .'\\\';">'. __ ('Connect') .'</a>';
			
			$drivers [] = '<li id="_SOCIAL_'. $driver->getName () .'" style="background: url('. $driver->getPath () .'_resource/menu'. ($enabled ? '' : '-grey') .'.gif) no-repeat left;"><div class="link">'. $link .'</div><div class="url">'. ($enabled ? __ ('Connected to [1]', $driver->getUserUrl ()) : '') .'</div></li>';
		}
	
	if (!sizeof ($drivers))
	{
		?>
		Modalbox.show ('<ul class="socialNetwork"><li class="last" style="background: url(titan.php?target=loadFile&file=interface/alert/warning.gif) no-repeat left;"><div style="margin-left: 40px;"><?= __ ('No one social network is enable for this application!') ?></div></li></ul>', { title: '<?= __ ('Social Networks') ?>', width: 500 });
		
		hideWait ();
		
		return false;
		<?
	}
	?>
	
	Modalbox.show ('<ul class="socialNetwork"><?= implode ('', $drivers) ?></ul>', { title: '<?= __ ('Social Networks') ?>', width: 800 });
	
	hideWait ();
	
	return false;
}
function disconnectFromSocialNetwork (driver, path, url)
{
	showWait ();
	
	ajax.disconnectFromSocialNetwork (driver, function () {
		$('_SOCIAL_' + driver).style.background = 'url(' + path + '_resource/menu-grey.gif) no-repeat left';
	
		var array = $('_SOCIAL_' + driver).select('div');
		
		var a = document.createElement ('a');
		
		a.className = 'connect';
		a.href = '#';
		a.onclick = function () { showWait (); parent.document.location = url; };
		a.innerHTML = '<?= __ ('Connect') ?>';
		
		array [0].update ('');
		
		array [0].appendChild (a);
		
		array [1].update ('');
		
		hideWait ();
	});
	
	return false;
}
<?
if (isset ($_GET['social']) && (int) $_GET['social'])
{
	?>
	function runOnLoad ()
	{
		showSocialNetworks ();
	}
	<?
}
?>

function showMobileDevices ()
{
	showWait ();
	
	<?
	$list = array ('<li id="_MOBILE_LOADING_" style="background: url(titan.php?target=loadFile&file=interface/alert/info.gif) no-repeat left; display: block;"><div style="margin-left: 50px; font-weight: bold;">'. __ ('Please, wait! Loading...') .'</div></li>',
				   '<li id="_MOBILE_NONE_" style="background: url(titan.php?target=loadFile&file=interface/alert/warning.gif) no-repeat left; display: none;"><div style="margin-left: 50px; font-weight: bold;">'. __ ('No one mobile device is enable to access your data!') .'</div></li>');
	$button = '';
	
	if (MobileDevice::isActive ())
	{
		array_unshift ($list, '<li id="_MOBILE_HEADER_" class="header" style="display: none;"><div class="name">'. __ ('Name') .'</div><div class="id">'. __ ('Identifier') .'</div><div class="pk">'. __ ('Private Key') .'</div><div class="sync"></div><div class="icons">'. __ ('Actions') .'</div></li>');
		
		$button = '<div style="width: 750px; text-align: center;"><input type="button" class="buttonToRegisterMobileDevice" value="'. __ ('Register New Device') .'" onclick="JavaScript: registerDevice ();" /></div>';			
	}
	?>
	
	Modalbox.show ('<ul id="_MOBILE_DEVICES_" class="mobileDevices"><?= implode ('', $list) ?></ul><?= $button ?>', { title: '<?= __ ('Mobile Devices') ?>', width: 800 });
	
	ajax.delay (function ()
	{
		var buffer = ajax.getMobileDevices ();
		
		eval (buffer);
		
		if (devices.length)
		{
			$('_MOBILE_LOADING_').style.display = 'none';
			$('_MOBILE_HEADER_').style.display = 'block';
			
			for (var i = 0; i < devices.length; i++)
				addDeviceLine (devices [i]);
		
			Modalbox.resizeToContent ();
		}
		else
		{
			$('_MOBILE_LOADING_').style.display = 'none';
			$('_MOBILE_NONE_').style.display = 'block';
		}
		
		hideWait ();
	});
	
	return false;
}

function generateQrCode (id, pk)
{
	var source = '<table border="0" style="margin: 10px 20px;">\
			<tr>\
				<td style="font-size: 14px; font-weight: bold; padding-bottom: 10px; text-align: justify;"><?= __ ('<span style="color: #900;">Attention!</span> To register your device, please, pointing the camera to barcode bellow:') ?></td>\
			</tr>\
			<tr>\
				<td><iframe width="444px" height="444px" style="border: 2px solid #CCC;" src="titan.php?target=script&toSection=<?= Business::singleton ()->getSection (Section::TCURRENT)->getName () ?>&file=qr&id=' + id + '&pk=' + pk + '"></iframe></td>\
			</tr>\
		</table>';
	
	Modalbox.show (source, {width: 510, height: 580, title: '<?= __ ('Register Device by QR Code') ?>'});
}

function unregisterDevice (id)
{
	if (!confirm ("<?= __ ('Attention! The device will no longer synchronize data with application. Are you sure to continue?') ?>"))
		return false;
	
	showWait ();
	
	ajax.unregisterDevice (id, function ()
	{
		$('_MOBILE_' + id).style.display = 'none';
		
		Modalbox.resizeToContent ();
		
		hideWait ();
	});
}

function registerDevice ()
{
	var name = prompt ("<?= __ ('Please, insert a name for new device:') ?>", "<?= __ ('e.g.: My Personal Smartphone') ?>");
	
	if (name == null)
		return false;
	
	showWait ();
	
	var buffer = ajax.registerDevice (name);
	
	var device = null;
	
	eval (buffer);
	
	if (device == null)
	{
		ajax.showMessages ();
		
		hideWait ();
		
		Modalbox.hide ();
		
		return false;
	}
	
	$('_MOBILE_NONE_').style.display = 'none';
	$('_MOBILE_HEADER_').style.display = 'block';
	
	addDeviceLine (device);
	
	Modalbox.resizeToContent ();
	
	hideWait ();
}

function addDeviceLine (device)
{
	var div, li, img;
	
	li = document.createElement ('li');
	
	li.id = '_MOBILE_' + device.id;
	
	div = document.createElement ('div');
	div.className = 'name';
	div.title = device.name;
	div.innerHTML = device.name;
	
	li.appendChild (div);
	
	div = document.createElement ('div');
	div.className = 'id';
	div.innerHTML = device.id;
	
	li.appendChild (div);
	
	div = document.createElement ('div');
	div.className = 'pk';
	div.innerHTML = device.pk;
	
	li.appendChild (div);
	
	div = document.createElement ('div');
	div.className = 'sync';
	
	img = document.createElement ('img');
	img.src = 'titan.php?target=loadFile&file=interface/icon/' + (device.sync ? '' : 'grey/') + 'confirmed.gif';
	img.border = '0';
	img.title = device.sync ? '<?= __ ('Paired device') ?>' : '<?= __ ('Device is not synchronized') ?>';
	
	div.appendChild (img);
	
	li.appendChild (div);
	
	div = document.createElement ('div');
	div.className = 'icons';
	
	img = document.createElement ('img');
	img.src = 'titan.php?target=loadFile&file=interface/icon/qr.gif';
	img.border = '0';
	img.onclick = function () { generateQrCode (device.id, device.pk.replace(/[^0-9A-Z]/g, '')); };
	img.title = '<?= __ ('Register by QR Code') ?>';
	
	div.appendChild (img);
	
	img = document.createElement ('img');
	img.src = 'titan.php?target=loadFile&file=interface/icon/delete.gif';
	img.border = '0';
	img.onclick = function () { unregisterDevice (device.id); };
	img.title = '<?= __ ('Unregister Device') ?>';
	
	div.appendChild (img);
	
	li.appendChild (div);
	
	$('_MOBILE_DEVICES_').appendChild (li);
}
</script>