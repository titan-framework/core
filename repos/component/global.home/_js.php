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
</script>