<?
$skin = Skin::singleton ();

if (!(bool) ini_get ('zlib.output_compression'))
	ob_start ('ob_gzhandler');

header ('Content-Encoding: gzip');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title> <?= $instance->getName () ?> </title>
		<meta name="description" content="<?= $instance->getDescription () ?>" />
		
		<link rel="stylesheet" type="text/css" href="<?= $skin->getCss (array ('main', 'message', 'gallery', 'boxes', 'menu', 'bug', 'backup'), Skin::URL) ?>" />
		<!--[if IE]><link rel="stylesheet" type="text/css" href="<?= $skin->getCss ('ie', Skin::URL) ?>" /><![endif]-->
		
		<link rel="icon" href="<?= $skin->getIcon () ?>" type="image/ico" />
		<link rel="shortcut icon" href="<?= $skin->getIcon () ?>" type="image/ico" />
		
		<style type="text/css">
		#menuBox
		{
			position: absolute;
			overflow: hidden;
			width: 200px;
			height: <?= $menuHeight < 7 ? 158 : $menuHeight * 25 + 4  ?>px;
			z-index: 3;
			border: #D4D4D4 2px solid;
			padding: 0px;
		}
		.menuMain
		{
			position: absolute;
			display: block;
			top: 0px;
			width: 200px;
			height: <?= $menuHeight < 7 ? 155 : $menuHeight * 25 + 1 ?>px;
			background-color: #575556;
			border-top: #36817C 3px solid;
			overflow: hidden;
		}
		</style>
		<?
		$types = Instance::singleton ()->getTypes ();
		
		foreach ($types as $type => $path)
			if (file_exists ($path .'_css.php'))
				include $path .'_css.php';
		
		if (file_exists ($section->getCompPath () .'_css.php'))
			include $section->getCompPath () .'_css.php';
		?>
		<script language="javascript" type="text/javascript" src="titan.php?target=packer&amp;files=prototype,builder,effects,dragdrop,controls,slider,sound,protolimit,tooltip"></script>
		<script language="javascript" type="text/javascript">
		String.prototype.namespace = function (separator)
		{
			this.split (separator || '.').inject (window, function (parent, child) {
				return parent [child] = parent [child] || { };
			})
		}
		</script>
		<script language="javascript" type="text/javascript" src="titan.php?target=packer&amp;files=general,menu,type,boxover,common,actb,ajax,ajax-dynamic-content,modal-message,modalbox"></script>
		<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?sensor=false&libraries=places"></script>
		<?= XOAD_Utilities::header('titan.php?target=loadFile&amp;file=xoad') ."\n" ?>
		<script language="javascript" type="text/javascript">
		var tAjax = <?= XOAD_Client::register(new Xoad) ?>;
		var menuHeight = <?= $menuHeight < 7 ? 154 : $menuHeight * 22  ?>;
		
		var _formErrorFields = new Array ();
		var _formErrorColors = new Array ();
		
		function saveForm (file, formId, itemId, goTo)
		{
			showWait ();
			
			var formData = xoad.html.exportForm (formId);
			
			var fields = new Array ();
			
			eval ("fields = new Array (" + tAjax.validate (file, formData, itemId) + ");");
			
			if (fields.length)
			{
				tAjax.showMessages ();
				
				$('idBody').scrollTop = 0;
				
				for (var i = 0; i < _formErrorFields.length; i++)
				{
					$('row_' + _formErrorFields [i]).style.backgroundColor = _formErrorColors [i];
					$$('#row_' + _formErrorFields [i] + ' td').first ().style.background = 'none';
				}
				
				_formErrorFields = new Array ();
				_formErrorColors = new Array ();
				
				for (var i = 0; i < fields.length; i++)
				{
					_formErrorFields [i] = fields [i];
					_formErrorColors [i] = $('row_' + fields [i]).style.backgroundColor;
					
					$('row_' + fields [i]).style.backgroundColor = '#FADFDD';
					$$('#row_' + fields [i] + ' td').first ().style.background = 'url(titan.php?target=loadFile&file=interface/image/exclamation.png) 5px no-repeat';
				}
				
				hideWait ();
				
				return false;
			}
			
			var form = document.getElementById (formId);
			
			if (goTo)
				form.action = 'titan.php?target=commit&toSection=<?= $section->getName () ?>&toAction=<?= $action->getName () ?>&goTo=' + goTo;
			else
				form.action = 'titan.php?target=commit&toSection=<?= $section->getName () ?>&toAction=<?= $action->getName () ?>';
			
			form.submit ();
		}
		
		function deleteForm (file, form, itemId)
		{
			document.getElementById (form).action = 'titan.php?target=commit&toSection=<?= $section->getName () ?>&toAction=<?= $action->getName () ?>';
			
			document.getElementById (form).submit ();
		}
		
		function saveFormAjax (file, form, itemId, goToAction)
		{
			showWait ();
			
			var formData = xoad.html.exportForm (form);
			
			var fields = new Array ();
			
			eval ("fields = new Array (" + tAjax.validate (file, formData, itemId) + ");");
			
			if (fields.length)
			{
				tAjax.showMessages ();
				
				$('idBody').scrollTop = 0;
				
				for (var i = 0; i < _formErrorFields.length; i++)
				{
					$('row_' + _formErrorFields [i]).style.backgroundColor = _formErrorColors [i];
					$$('#row_' + _formErrorFields [i] + ' td').first ().style.background = 'none';
				}
				
				_formErrorFields = new Array ();
				_formErrorColors = new Array ();
				
				for (var i = 0; i < fields.length; i++)
				{
					_formErrorFields [i] = fields [i];
					_formErrorColors [i] = $('row_' + fields [i]).style.backgroundColor;
					
					$('row_' + fields [i]).style.backgroundColor = '#FADFDD';
					$$('#row_' + fields [i] + ' td').first ().style.background = 'url(titan.php?target=loadFile&file=interface/image/exclamation.png) 5px no-repeat';
				}
				
				hideWait ();
				
				return false;
			}
			
			if (!tAjax.save (file, formData, itemId, '<?= $section->getName () ?>'))
			{
				tAjax.delay (function () { hideWait (); });
				
				return false;
			}
			
			if (goToAction != '<?= $action->getName () ?>')
			{
				document.location = 'titan.php?target=body&toSection=<?= $section->getName () ?>&amp;toAction=' + goToAction;
				
				return true;
			}
			
			tAjax.delay (function () { hideWait (); });
			
			return true;
		}
		
		function bugReport (error, type)
		{
			if (!error)
				error = '';
			
			var source = '<table border="0" class="bugReport" style="margin: 10px 20px;">\
				<tr>\
					<td colspan="2" class="warning"><?= __ ('Use bellow fields to report application erros for developer team. You can send report as anonimous user, but this is not recomended if you want feedback.') ?></td>\
				</tr>\
				<tr>\
					<td colspan="2">\
						<form id="bugReport" action="#">\
						<p><label for="name"><?= __ ('Your Name') ?></label> <input type="text" id="name" name="name" value="<?= User::singleton ()->getName () ?>" /></p>\
						<p><label for="mail"><?= __ ('Your E-mail') ?></label> <input type="text" id="mail" name="mail" value="<?= User::singleton ()->getEmail () ?>" /></p>\
						<p><label for="browser"><?= __ ('Browser') ?></label> <input type="text" id="browser" name="browser" value="<?= getBrowser () ?>" /></p>\
						<p><label for="bread"><?= __ ('Breadcrumb') ?></label> <input type="text" id="bread" name="bread" value="<?= getBreadPath ($section, FALSE, FALSE) . $action->getLabel () ?>" /></p>\
						<p><label for="description"><?= __ ('Description') ?></label> <textarea id="description" name="description">' + error + '</textarea></p>\
						</form>\
					</td>\
				</tr>\
				<tr>\
					<td colspan="2">\
						<input type="button" class="button" value="<?= __ ('Submit') ?>" onclick="JavaScript: sendBugReport ();" style="margin-left: 107px;" />\
						<a href="#" onclick="JavaScript: Modalbox.hide ();" style="color: #900; font-size: 12px; margin-left: 20px;"><?= __ ('Cancel') ?></a>\
					</td>\
				</tr>\
			</table>';
			
			Modalbox.show (source, {width: 430, height: 480, title: '<?= __ ('Bug Report') ?>'});
		}
		
		<?
		if (PHP_OS == 'Linux' && User::singleton ()->isAdmin () && Backup::singleton ()->isActive ())
		{
			$bkpFree  = (int) @$_SESSION['_TITAN_BACKUP_FREE_'];
			$bkpDB    = (int) @$_SESSION['_TITAN_BACKUP_DB_'];
			$bkpFile  = (int) @$_SESSION['_TITAN_BACKUP_FILE_'];
			$bkpCache = (int) @$_SESSION['_TITAN_BACKUP_DB_'];
			?>
			function instanceBackup ()
			{
				var source = '<table border="0" class="instanceBackup" style="margin: 5px 10px;">\
					<tr><td colspan="2" class="warning"><?= __ ('Select bellow the artifacts that you want make backup.') ?></td></tr>\
					<tr><td colspan="2"><?= __ ('The space available on server is <b>~[1] MB</b>.', number_format ($bkpFree, 0, ',', '.')) ?></td></tr>\
					<tr>\
						<td colspan="2">\
							<input type="checkbox" name="db" id="_INSTANCE_BACKUP_DB_" onclick="JavaScript: updateInstanceBackupSize ();" /> <?= __ ('Entire Database [~[1] MB]', number_format ($bkpDB, 0, ',', '.')) ?><br />\
							<input type="checkbox" name="file" id="_INSTANCE_BACKUP_FILE_" onclick="JavaScript: updateInstanceBackupSize ();" /> <?= __ ('All Uploaded Files [~[1] MB]', number_format ($bkpFile, 0, ',', '.')) ?><br />\
							<input type="checkbox" name="cache" id="_INSTANCE_BACKUP_CACHE_" onclick="JavaScript: updateInstanceBackupSize ();" /> <?= __ ('All Cached Files (includes auditing database) [~[1] MB]', number_format ($bkpCache, 0, ',', '.')) ?><br />\
						</td>\
					</tr>\
					<tr><td colspan="2"><?= __ ('The links for backup download will be sent to <b>[1]</b>.', User::singleton ()->getEmail ()) ?></td></tr>\
					<tr style="height: 115px;">\
						<td style="width: 180px;"><label id="_INSTANCE_BACKUP_TOTAL_"></label></td>\
						<td style="width: 300px;" id="_INSTANCE_BACKUP_BUTTON_"></td>\
					</tr>\
				</table>';
				
				Modalbox.show (source, {width: 500, height: 370, title: 'Backup'});
			}
			
			function updateInstanceBackupSize ()
			{
				var elements = new Array ('_INSTANCE_BACKUP_DB_', '_INSTANCE_BACKUP_FILE_', '_INSTANCE_BACKUP_CACHE_');
				
				var values = new Array (<?= $bkpDB ?>, <?= $bkpFile ?>, <?= $bkpCache ?>);
				
				var free = <?= $bkpFree ?>;
				
				var total = 0;
				
				for (var i = 0 ; i < elements.length ; i++)
					if ($(elements [i]).checked)
						total = total + values [i];
				
				if (!total || total > free)
				{
					$('_INSTANCE_BACKUP_TOTAL_').style.color = '#900';
					
					var button = $('_INSTANCE_BACKUP_BUTTON_').childElements ();
					
					for (var i = 0 ; i < button.length ; i++)
						$('_INSTANCE_BACKUP_BUTTON_').removeChild (button [i]);
				}
				else
				{
					$('_INSTANCE_BACKUP_TOTAL_').style.color = '#090';
					
					if (!$('_INSTANCE_BACKUP_BUTTON_').childElements ().length)
					{
						var button = document.createElement ('img');
						
						button.src = 'titan.php?target=loadFile&file=interface/image/backup.png';
						button.onclick = function () { generateInstanceBackup () };
						button.style.cursor = 'pointer';
						
						$('_INSTANCE_BACKUP_BUTTON_').appendChild (button);
					}
				}
				
				$('_INSTANCE_BACKUP_TOTAL_').innerHTML = total + ' MB';
			}
			
			function generateInstanceBackup ()
			{
				var elements = new Array ('DB', 'FILE', 'CACHE');
				
				var aux = '';
				
				for (var i = 0 ; i < elements.length ; i++)
					if ($('_INSTANCE_BACKUP_' + elements [i] + '_').checked)
						aux += elements [i] + ',';
				
				xmlHttp = new XMLHttpRequest ();
				xmlHttp.open ('GET', 'titan.php?target=backup&artifacts=' + aux, true);
				xmlHttp.send (null);
				
				message ('<?= __ ('The backup process has started in background! The system can still be used normally. Depending on the size, the process may take from few seconds to several hours. When finished you will receive a e-mail with download links.') ?>', 500, 120, true, '<?= __ ('Success') ?>', 'SUCCESS');
			}
			<?
		}
		?>
		
		function chooseLanguage ()
		{
			<?
			$languages = Localization::singleton ()->getAvaliableLanguages ();
			
			$size = sizeof ($languages) * 60;
			?>
			var source = '<div style="margin: 0 auto; width: <?= $size ?>px;"><?
					foreach ($languages as $language => $label)
						echo '<div class="flag" style="background-image: url(titan.php?target=loadFile&amp;file=interface/locale/'. $language .'.png);'. ($language == Localization::singleton ()->getLanguage () ? ' background-position: top;" onclick="JavaScript: Modalbox.hide ();"' : '" onclick="JavaScript: changeLanguage (\\\''. $language .'\\\');"') .' title="'. $label .'"></div>';
					?></div>';
			
			Modalbox.show (source, { title: '<?= __ ('Choose Your Language') ?>', width: <?= $size < 240 ? 260 : $size + 20 ?>, height: 90 });
		}
		
		function getHelp ()
		{
			oBody = document.body;
			
			h = oBody.scrollHeight + (oBody.offsetHeight - oBody.clientHeight) - 70;
			w  = oBody.scrollWidth  + (oBody.offsetWidth  - oBody.clientWidth) - 20;
			
			if (w > 1060)
				w = 1060;
			
			var source = '<iframe src="titan.php?target=manual&toSection=<?= Business::singleton ()->getSection (Section::TCURRENT)->getName () ?>" scrolling="auto" style="margin: 0px; border: #CCC 1px solid; width: ' + (w - 20) + 'px; height: ' + (h - 50) + 'px; background: url(titan.php?target=loadFile&file=interface/image/manual_generation.png) center no-repeat;"></iframe>';
			
			Modalbox.show (source, { title: '<?= __ ('User Manual') ?>', width: w, height: h });
		}
		
		function showAlerts ()
		{
			showWait ();
			
			eval (tAjax.getAlerts ());
			
			if (!alerts || !alerts.length)
			{
				Modalbox.show ('<ul class="alert"><li class="read last" style="background: url(titan.php?target=loadFile&file=interface/alert/confirm.gif) no-repeat left;"><div><?= __ ('No alerts!') ?></div></li></ul>', { title: '<?= __ ('Alerts') ?>', width: 600 });
				
				hideWait ();
				
				return false;
			}
			
			var buffer = '';
			
			for (var i = 0 ; i < alerts.length ; i++)
				buffer += '<li id="_TITAN_ALERT_' + alerts [i].id + '" class="' + (alerts [i].read ? 'read' : 'unread') + '" style="background: #' + (alerts [i].read ? 'EFEFEF' : 'FFF') + ' url(' + alerts [i].icon + ') no-repeat left;"><div title="' + alerts [i].message + '"' + (alerts [i].read ? '' : ' onmouseover="JavaScript: readAlert (' + alerts [i].id + ');"') + ' onclick="JavaScript: document.location=\'' + alerts [i].link + '\';">' + alerts [i].message + '</div><img src="titan.php?target=loadFile&file=interface/image/trash.gif" title="<?= __ ('Delete') ?>" alt="<?= __ ('Delete') ?>" onclick="JavaScript: deleteAlert (' + alerts [i].id + ');" /></li>';
			
			Modalbox.show ('<ul class="alert">' + buffer + '</ul>', { title: '<?= __ ('Alerts') ?>', width: 600 });
			
			hideWait ();
		}
		
		<?
		if (Shopping::isActive ())
		{
			?>
			function showShoppingCart ()
			{
				showWait ();
				
				eval (tAjax.getItemsInShoppingCart ());
				
				if (!items || !items.length)
				{
					Modalbox.show ('<ul class="shopCar"><li class="read last" style="background: url(titan.php?target=loadFile&file=interface/alert/info.gif) no-repeat left;"><div style="margin-left: 40px;"><?= __ ('Your shopping cart is empty!') ?></div></li></ul>', { title: '<?= __ ('Shopping Cart') ?>', width: 950 });
					
					hideWait ();
					
					return false;
				}
				
				var buffer = '<li id="_TITAN_SHOP_HEADER_" class="header"><div class="description"><?= __ ('Description') ?></div><div class="quantity"><?= __ ('Quantity') ?></div><div class="value"><?= __ ('Value') ?> (<?= Shopping::singleton ()->getCurrency () ?>)</div><div class="total"><?= __ ('Total') ?></div></li>';
				
				var total = 0;
				
				for (var i = 0 ; i < items.length ; i++)
				{
					buffer += '<li id="_TITAN_SHOP_' + items [i].id + '"><div class="description" title="' + items [i].description + '">' + items [i].description + '</div><div class="quantity" title="' + items [i].quantity + '">' + items [i].quantity + '</div><div class="value" title="<?= Shopping::singleton ()->getCurrencySymbol () ?> ' + formatMoney (items [i].value) + '"><?= Shopping::singleton ()->getCurrencySymbol () ?> ' + formatMoney (items [i].value) + '</div><div class="total" title="<?= Shopping::singleton ()->getCurrencySymbol () ?> ' + formatMoney (items [i].quantity * items [i].value) + '"><?= Shopping::singleton ()->getCurrencySymbol () ?> ' + formatMoney (items [i].quantity * items [i].value) + '</div><img src="titan.php?target=loadFile&file=interface/image/trash.gif" title="<?= __ ('Remove') ?>" alt="<?= __ ('Remove') ?>" onclick="JavaScript: deleteItemFromShoppingCart (' + items [i].id + ');" /></li>';
					
					total += items [i].quantity * items [i].value;
				}
				
				buffer += '<li class="lineOfButtons"><div class="clearShopCar" style="background: url(titan.php?target=loadFile&file=interface/button/ClearShopCar-<?= Localization::singleton ()->getLanguage () ?>.png) center top no-repeat;" onmouseover="JavaScript: this.style.backgroundPosition = \'bottom\';" onmouseout="JavaScript: this.style.backgroundPosition = \'top\';"></div><div class="checkout" style="background: url(titan.php?target=loadFile&file=interface/button/Checkout-<?= Localization::singleton ()->getLanguage () ?>.png) center top no-repeat;" onmouseover="JavaScript: this.style.backgroundPosition = \'bottom\';" onmouseout="JavaScript: this.style.backgroundPosition = \'top\';"></div><div class="final"><div id="_TITAN_SHOP_FINAL_VALUE_">' + '<?= ('You have <b># item(s)</b> with total value<br /><b>$0,00</b>') ?>'.replace ('#', items.length).replace ('$0,00', '<?= Shopping::singleton ()->getCurrencySymbol () ?> ' + formatMoney (total)) + '</div></div></li>';
				
				Modalbox.show ('<ul class="shopCar">' + buffer + '</ul>', { title: '<?= __ ('Shopping Cart') ?>', width: 950 });
				
				hideWait ();
			}
			
			function updateShoppingCart ()
			{
				eval (tAjax.getItemsInShoppingCart ());
				
				var total = 0;
				
				for (var i = 0 ; i < items.length ; i++)
					total += items [i].quantity * items [i].value;
				
				$('_TITAN_SHOP_FINAL_VALUE_').innerHTML = '<?= ('You have <b># item(s)</b> with total value<br /><b>$0,00</b>') ?>'.replace ('#', items.length).replace ('$0,00', '<?= Shopping::singleton ()->getCurrencySymbol () ?> ' + formatMoney (total));
			}
			<?
		}
		?>
		
		var ajax = <?= XOAD_Client::register(new Ajax) ?>;
		</script>
		<?
		$types = Instance::singleton ()->getTypes ();
		
		foreach ($types as $type => $path)
			if (file_exists ($path .'_js.php'))
				include $path .'_js.php';
		
		if (file_exists ($section->getCompPath () .'_js.php'))
			include $section->getCompPath () .'_js.php';
		?>
	</head>
	<body marginheight="0" marginwidth="0" bottommargin="0" topmargin="0" leftmargin="0" rightmargin="0" onload="JavaScript: start ();" onunload="JavaScript: end ();" onresize="JavaScript: resizeBody ();">
		<div id="idSection">
			<div class="cPath">
				<div class="cTitle"><?= $section->getLabel () ?>: <a href="<?= $_SERVER['PHP_SELF'] .'?target=body&amp;toSection='. $section->getName () .'&amp;toAction='. $action->getName () .'&itemId='. $itemId ?>"><?= $action->getLabel () ?></a></div>
				<div class="cBreadcrumb"><?= $_OUTPUT ['BREADCRUMB'] ?></div>
			</div>
			<div class="cMenu">
				<?= $_OUTPUT ['SECTION_MENU'] ?>
			</div>
		</div>
		<div id="idBody" style="height: 500px;">
			<label id="labelMessage">
				<?
				if ($message->has ())
				{
					?>
					<div id="idMessage" style="display:;">
						<? while ($msg = $message->get ()) echo $msg; ?>
					</div>
					<?
					
					$message->clear ();
				}
				?>
			</label>
			<div class="cBody">
				<?= $_OUTPUT ['SECTION'] ?>
			</div>
		</div>
		<div id="idBase">
			<div class="cResources" id="_TITAN_INFO_">
				<?
				$path = Instance::singleton ()->getCorePath () .'update'. DIRECTORY_SEPARATOR;
				
				$version = trim (file_get_contents ($path .'VERSION'));
				$release = trim (file_get_contents ($path .'STABLE'));
				
				$appReleasePath = Instance::singleton ()->getCachePath () .'RELEASE';
				
				$autoDeploy = FALSE;
				
				if (file_exists ($appReleasePath) && is_readable ($appReleasePath))
				{
					$file = parse_ini_file ($appReleasePath);
					
					if (is_array ($file)) 
					{
						$autoDeploy = TRUE;
						
						$requiredKeys = array ('version', 'environment', 'date', 'author');
						
						foreach ($requiredKeys as $trash => $key)
							if (!array_key_exists ($key, $file) || trim ((string) $file [$key]) == '')
								$autoDeploy = FALSE;
					}
				}
				
				if (!$autoDeploy)
				{
					?>
					<label>Powered by <a href="http://www.titanframework.com" target="_blank" title="<?= $version .'-'. $release ?>">Titan Framework</a> (<?= $version .'-'. $release ?>)</label>
					<?
				}
				else
				{
					$appRelease = $file ['version'];
					$appEnvironment = $file ['environment'];
					$appDate = strftime ('%x %X', $file ['date']);
					
					$fileOfVersion = 'update'. DIRECTORY_SEPARATOR .'VERSION';
					
					if (file_exists ($fileOfVersion) && is_readable ($fileOfVersion))
					{
						$appVersion = trim (file_get_contents ($fileOfVersion, 0, NULL, 0, 16));
						
						if (!empty ($appVersion))
							$appRelease = $appVersion .'-'. $appRelease;
					}
					?>
					<a href="http://www.titanframework.com" target="_blank" title="Titan Framework (<?= $version .'-'. $release ?>)"><img class="cTitanAssign" src="titan.php?target=loadFile&amp;file=interface/image/assign.titan.png" /></a>
					<img class="cIconInfo" id="_TITAN_INFO_ICON_" src="titan.php?target=loadFile&amp;file=interface/image/info.gif" alt="Release Info" />
					<div id="_TITAN_INFO_TEXT_" class="cReleaseInfo" style="display: none;">
						<div>
							<?= __ ('This web application, named "<b>[1]</b>", is in version <b>[2]</b> for <b>[3]</b> environment (released <b>[4]</b>).', Instance::singleton ()->getName (), $appRelease, $appEnvironment, $appDate); ?>
							<br /><br />
							<?= __ ('It was developed using the <b>Titan Framework</b>, version <b>[1]</b>.', $version .'-'. $release); ?>
						</div>
					</div>
					<script type="text/javascript">
					document.getElementById ('_TITAN_INFO_ICON_').onmouseover = function ()	{ document.getElementById ('_TITAN_INFO_TEXT_').style.display = 'block'; };
					document.getElementById ('_TITAN_INFO_ICON_').onmouseout = function () { document.getElementById ('_TITAN_INFO_TEXT_').style.display = 'none'; };
					</script>
					<?
				}
				?>
			</div>
			<div class="cPowered">
				<?
				if (trim (Instance::singleton ()->getAuthor ()) == '')
				{
					?>
					<a href="http://creativecommons.org/licenses/by-nd/4.0/" target="_blank" title="Creative Commons License"><img alt="Creative Commons License" style="border-width:0" src="titan.php?target=loadFile&amp;file=interface/image/cc.png" /></a>
					<label>&copy; 2005 - <?= date ('Y') ?> &curren; <a href="http://www.carromeu.com/" target="_blank">Camilo Carromeu</a></label>
					<?
				}
				else
					echo Instance::singleton ()->getAuthor ();
				?>
			</div>
		</div>
		<div id="idBaseSpace"></div>
		<?= $_OUTPUT ['MENU'] ?>
		<script language="javascript" type="text/javascript">
			<?= $_OUTPUT ['MENU-POSITION'] ?>
		</script>
	</body>
</html>