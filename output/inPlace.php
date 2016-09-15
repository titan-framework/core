<?php
$skin = Skin::singleton ();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title> <?= $instance->getName () ?> </title>

		<link rel="stylesheet" type="text/css" href="<?= $skin->getCss (array ('main', 'message'), Skin::URL) ?>" />
		<!--[if IE]><link rel="stylesheet" type="text/css" href="<?= $skin->getCss ('ie', Skin::URL) ?>" /><![endif]-->
		
		<style type="text/css">
		body
		{
			background: #FFF none;
			margin: 0px;
			padding: 0px;
		}
		#idBody
		{
			position: relative;
			top: auto;
			left: auto;
		}
		</style>
		<?php
		$types = Instance::singleton ()->getTypes ();

		foreach ($types as $type => $path)
			if (file_exists ($path .'_css.php'))
				include $path .'_css.php';

		if (file_exists ($section->getCompPath () .'_css.php'))
			include $section->getCompPath () .'_css.php';
		?>
		<script language="javascript" type="text/javascript" src="titan.php?target=packer&files=prototype,builder,effects,dragdrop,controls,slider,sound,protolimit,tooltip"></script>
		<script language="javascript" type="text/javascript">
		String.prototype.namespace = function (separator)
		{
			this.split (separator || '.').inject (window, function (parent, child) {
				return parent[child] = parent[child] || { };
			})
		}
		</script>
		<script language="javascript" type="text/javascript" src="titan.php?target=packer&amp;files=general,type,boxover,common,modal-message,modalbox"></script>
		<?= XOAD_Utilities::header('titan.php?target=loadFile&amp;file=xoad') ."\n" ?>
		<script language="javascript" type="text/javascript">
		var tAjax = <?= XOAD_Client::register(new Xoad) ?>;
		
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

			var form = document.getElementById(formId);

			if (goTo)
				form.action = 'titan.php?target=commitInPlace&toSection=<?= $section->getName () ?>&toAction=<?= $action->getName () ?>&goTo=' + goTo;
			else
				form.action = 'titan.php?target=commitInPlace&toSection=<?= $section->getName () ?>&toAction=<?= $action->getName () ?>';

			form.submit();
		}

		function deleteForm (file, form, itemId)
		{
			document.getElementById (form).action = 'titan.php?target=commitInPlace&toSection=<?= $section->getName () ?>&toAction=<?= $action->getName () ?>';

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
				document.location = 'titan.php?target=inPlace&toSection=<?= $section->getName () ?>&amp;toAction=' + goToAction;

				return true;
			}

			tAjax.delay (function () { hideWait (); });

			return true;
		}

		var ajax = <?= XOAD_Client::register(new Ajax) ?>;

		function showWait ()
		{
			document.getElementById('idWait').innerHTML = '<img src="titan.php?target=loadFile&amp;file=interface/icon/upload.gif" border="0" /> <label><?= __ ('Wait! working on your request...') ?></label>';
		}

		function hideWait ()
		{
			document.getElementById('idWait').innerHTML = '';
		}
		
		function callParent ()
		{
			if (self == parent)
				return false;
			
			parent.document.getElementById ('<?= @$_GET['assign'] ?>').style.height = (document.body.scrollHeight + 5).toString () + 'px';
			
			parent.hideWait ();
		}
		</script>
		<?php
		$types = Instance::singleton ()->getTypes ();

		foreach ($types as $type => $path)
			if (file_exists ($path .'_js.php'))
				include $path .'_js.php';

		if (file_exists ($section->getCompPath () .'_js.php'))
			include $section->getCompPath () .'_js.php';
		?>
	</head>
	<body marginheight="0" marginwidth="0" bottommargin="0" topmargin="0" leftmargin="0" rightmargin="0" onload="JavaScript: callParent ();">
		<div id="idWait" style="display: none;"></div>
		<div id="idBody" style="margin: 0px; border-width: 0px;">
			<label id="labelMessage">
				<?php
				if ($message->has ())
				{
					?>
					<div id="idMessage" style="display:;">
						<?php while ($msg = $message->get ()) echo $msg; ?>
					</div>
					<?php

					$message->clear ();
				}
				?>
			</label>
			<div class="cBody" style="width: 100%; padding-top: 10px;">
				<?= $_OUTPUT ['SECTION'] ?>
			</div>
			<div class="cMenuInPlace">
				<span><?= $_OUTPUT ['SECTION_MENU'] ?></span>
			</div>
		</div>
		<div id="idBody" style="display: none;"></div>
	</body>
</html>