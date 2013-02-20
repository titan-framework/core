<?
if (!isset ($_GET['fieldId']) || !isset ($_GET['search']) || !isset ($_GET['where']))
	throw new Exception (__ ('There was lost of variables!'));

$fieldId = $_GET['fieldId'];

$file = $_GET['search'];

$instance = Instance::singleton ();

foreach ($instance->getTypes () as $type => $path)
	require_once $path . $type .'.php';

$message = Message::singleton ();

$business = Business::singleton ();

$business->setCurrent ();

$section = $business->getSection (Section::TCURRENT);

$action = $business->getAction (Action::TCURRENT);

$itemId = isset ($_GET['itemId']) ? $_GET['itemId'] : 0;

require_once $instance->getCorePath () .'class/Xoad.php';

$allow = array ('Xoad', 'Ajax');

foreach ($instance->getTypes () as $type => $path)
{
	if (!file_exists ($path .'_ajax.php'))
		continue;
	
	require_once $path . '_ajax.php';
	
	$allow [] = 'x'. $type;
}

require_once $instance->getCorePath () .'xoad/xoad.php';

XOAD_Server::allowClasses ($allow);

if (XOAD_Server::runServer ())
	exit ();

require_once $instance->getCorePath () .'extra/fckEditor/fckeditor.php';
require_once $instance->getCorePath () .'extra/htmlPurifier/HTMLPurifier.standalone.php';

$search = new Search ($file);

$search->recovery ();

$view = new View ($file);

$in = $search->makeWhere ();

$out = urldecode ($_GET['where']);

if (trim ($out) != '')
	$out .= trim ($in) != '' ? " AND ". $in : "";
else
	$out = $in;

if (!$view->load ($out))
	throw new Exception (__ ('Unable to load data!'));

$skin = Skin::singleton ();

ob_start ('ob_gzhandler');

header ('Content-type: text/html; charset: UTF-8');
header ('Content-Encoding: gzip');
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title> <?= $instance->getName () ?> :: <?= __ ('User register') ?></title>

		<link rel="stylesheet" type="text/css" href="<?= $skin->getCss ('main', Skin::URL) ?>" />
		<style type="text/css">
		body
		{
			background: none #FFFFFF;
			padding: 0px;
			margin: 0px;
		}
		#idSearch,#idList
		{
			margin: 2px;
		}
		#idResult
		{
			margin: 5px 2px;
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
		<script language="javascript" type="text/javascript" src="titan.php?target=packer&amp;files=prototype"></script>
		<script language="javascript" type="text/javascript">
		String.prototype.namespace = function (separator)
		{
			this.split (separator || '.').inject (window, function (parent, child) {
				return parent[child] = parent[child] || { };
			})
		}
		</script>
		<script language="javascript" type="text/javascript" src="titan.php?target=packer&amp;files=general,type,boxover,common,modal-message"></script>
		<?= XOAD_Utilities::header('titan.php?target=loadFile&amp;file=xoad') ."\n" ?>
		<script language="javascript" type="text/javascript">
		var tAjax = <?= XOAD_Client::register(new Xoad) ?>;

		function showSelectSearch ()
		{
			var div = $('idSearch');
			var label = $('labelSearch');
			var image = $('imageSearch');

			if (div.style.display == '')
			{
				div.style.display = 'none';
				label.style.display = '';
				image.style.display = '';
			}
			else
			{
				label.style.display = 'none';
				image.style.display = 'none';
				div.style.display = '';
			}
		}
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
	<body>
		<? include Template::import ('global.search') ?>
	</body>
</html>