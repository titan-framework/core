<?php

$instance = Instance::singleton ();

header ('Content-Type: text/html; charset=UTF-8', TRUE);

if ($instance->onDebugMode ())
{
	header ('Cache-Control: no-cache');
	header ('Pragma: no-cache');
}

session_name ($instance->getSession ());

session_start ();

require $instance->getCorePath () .'system/control.php';

foreach ($instance->getTypes () as $type => $path)
	require_once $path . $type .'.php';

$message = Message::singleton ();

Business::singleton ()->setSectionDefault (User::singleton ()->getType ()->getHome ());

$business = Business::singleton ();

$business->setCurrent ();

$section = $business->getSection (Section::TCURRENT);

$action = $business->getAction (Action::TCURRENT);

$itemId = isset ($_GET['itemId']) ? $_GET['itemId'] : 0;

$fatherId = isset ($_GET['fatherId']) ? $_GET['fatherId'] : 0;

if (file_exists ($section->getCompPath () .'_class.php'))
	include $section->getCompPath () .'_class.php';

if (file_exists ($section->getCompPath () .'_function.php'))
	include $section->getCompPath () .'_function.php';

$_OUTPUT = array ();

switch (@$_GET['target'])
{
	default:
		include $instance->getCorePath () .'output/main.php';

		break;

	case 'body':
		$_TITAN ['TIME_START'] = microtime (TRUE);

		define ('XOAD_AUTOHANDLE', true);

		require_once Instance::singleton ()->getCorePath () .'class/Xoad.php';

		if (file_exists ($section->getCompPath () .'_ajax.php'))
			include $section->getCompPath () .'_ajax.php';
		else
			require_once Instance::singleton ()->getCorePath () .'class/Ajax.php';

		$allow = array ('Xoad', 'Ajax');

		foreach (Instance::singleton ()->getTypes () as $type => $path)
		{
			if (!file_exists ($path .'_ajax.php'))
				continue;

			require_once $path . '_ajax.php';

			$allow [] = 'x'. $type;
		}

		require_once Instance::singleton ()->getCorePath () .'xoad/xoad.php';

		XOAD_Server::allowClasses ($allow);

		if (XOAD_Server::runServer ())
			exit ();

		require_once Instance::singleton ()->getCorePath () .'extra/htmlPurifier/HTMLPurifier.standalone.php';

		require Instance::singleton ()->getCorePath () .'assembly/menu.php';

		require Instance::singleton ()->getCorePath () .'assembly/breadcrumb.php';

		require Instance::singleton ()->getCorePath () .'assembly/section.php';

		include Instance::singleton ()->getCorePath () .'output/body.php';

		$_TITAN ['TIME_END'] = microtime (TRUE);

		if (Instance::singleton ()->onDebugMode ())
			toLog ('['. $_SERVER['QUERY_STRING'] .'] proccessed in '. number_format ($_TITAN ['TIME_END'] - $_TITAN ['TIME_START'], 4) .' seconds.');

		break;

	case 'inPlace':
		define ('XOAD_AUTOHANDLE', true);

		require_once Instance::singleton ()->getCorePath () .'class/Xoad.php';

		if (file_exists ($section->getCompPath () .'_ajax.php'))
			include $section->getCompPath () .'_ajax.php';
		else
			require_once Instance::singleton ()->getCorePath () .'class/Ajax.php';

		$allow = array ('Xoad', 'Ajax');

		foreach (Instance::singleton ()->getTypes () as $type => $path)
		{
			if (!file_exists ($path .'_ajax.php'))
				continue;

			require_once $path . '_ajax.php';

			$allow [] = 'x'. $type;
		}

		require_once Instance::singleton ()->getCorePath () .'xoad/xoad.php';

		XOAD_Server::allowClasses ($allow);

		if (XOAD_Server::runServer ())
			exit ();

		require_once Instance::singleton ()->getCorePath () .'extra/htmlPurifier/HTMLPurifier.standalone.php';

		require Instance::singleton ()->getCorePath () .'assembly/section.php';

		include Instance::singleton ()->getCorePath () .'output/inPlace.php';

		break;

	case 'top':
		include $instance->getCorePath () .'output/top.php';

		break;

	case 'print':
		require Instance::singleton ()->getCorePath () .'assembly/breadcrumb.php';

		require Instance::singleton ()->getCorePath () .'assembly/section.php';

		include Instance::singleton ()->getCorePath () .'output/print.php';

		break;

	case 'pdf':
		require Instance::singleton ()->getCorePath () .'assembly/breadcrumb.php';

		require Instance::singleton ()->getCorePath () .'assembly/section.php';

		require Instance::singleton ()->getCorePath () .'extra/domPdf/dompdf_config.inc.php';

		require Instance::singleton ()->getCorePath () .'extra/htmlPurifier/HTMLPurifier.standalone.php';

		$config = HTMLPurifier_Config::createDefault ();

		$config->set ('Core', 'Encoding', 'UTF-8');

		$config->set ('HTML', 'Doctype', 'HTML 4.01 Strict');

		if (!Instance::singleton ()->onDebugMode ())
		{
			$path = Instance::singleton ()->getCachePath () .'purifier';

			if (!file_exists ($path) && !@mkdir ($path, 0777))
				throw new Exception ('Impossível criar diretório ['. $path .'].');

			$config->set ('Cache', 'SerializerPath', $path);
		}
		else
			$config->set ('Cache', 'DefinitionImpl', NULL);

		$purifier = new HTMLPurifier ($config);

		$dompdf = new DOMPDF ();

		ob_start ();

		include Instance::singleton ()->getCorePath () .'output/pdf.php';

		$dompdf->load_html ($purifier->purify (ob_get_clean ()));

		$dompdf->render ();

		$dompdf->stream ('titan.pdf');

		break;

	case 'csv':
		include $instance->getCorePath () .'output/csv.php';

		break;

	case 'commit':
		include $instance->getCorePath () .'system/commit.php';

		break;

	case 'commitInPlace':
		include $instance->getCorePath () .'system/commitInPlace.php';

		break;

	case 'upload':
		include $corePath .'output/upload.php';

		break;

	case 'blank':
		echo '<html><body bgcolor="white"><label style="color: #990000;">Carregando...</label></body></html>';

		break;

	case 'lucene':
		$action = $section->getAction (Action::TLUCENE);

		Business::singleton ()->setCurrent ($section, $action);

		define ('XOAD_AUTOHANDLE', true);

		require_once Instance::singleton ()->getCorePath () .'class/Xoad.php';

		if (file_exists ($section->getCompPath () .'_ajax.php'))
			include $section->getCompPath () .'_ajax.php';
		else
			require_once Instance::singleton ()->getCorePath () .'class/Ajax.php';

		$allow = array ('Xoad', 'Ajax');

		foreach (Instance::singleton ()->getTypes () as $type => $path)
		{
			if (!file_exists ($path .'_ajax.php'))
				continue;

			require_once $path . '_ajax.php';

			$allow [] = 'x'. $type;
		}

		require_once Instance::singleton ()->getCorePath () .'xoad/xoad.php';

		XOAD_Server::allowClasses ($allow);

		if (XOAD_Server::runServer ())
			exit ();

		if (!isset ($_GET['query']))
			throw new Exception ('Invalid link!');

		$query = urldecode ($_GET['query']);

		require Instance::singleton ()->getCorePath () .'assembly/menu.php';

		require Instance::singleton ()->getCorePath () .'assembly/breadcrumb.php';

		$_OUTPUT ['SECTION_MENU'] = '';
		$_OUTPUT ['SECTION'] = '';

		try
		{
			ob_start ();

			if (!isset ($section) || !isset ($action))
				throw new Exception ('Seção ou Ação inválida!');

			$action->generateMenu ();

			while ($item = Menu::singleton ()->get ())
				echo $item;

			$_OUTPUT ['SECTION_MENU'] = '<ul>'. ob_get_clean () .'</ul>';

			ob_start ();

			require $instance->getCorePath () .'output/lucene.php';

			$_OUTPUT ['SECTION'] = ob_get_clean ();
		}
		catch (PDOException $e)
		{
			ob_end_clean ();

			$message->addWarning ($e->getMessage ());
		}
		catch (Exception $e)
		{
			ob_end_clean ();

			$message->addWarning ($e->getMessage ());
		}

		include Instance::singleton ()->getCorePath () .'output/body.php';

		break;

	case 'luceneContent':
		try
		{
			if (!isset ($_GET['id']) || !is_numeric ($_GET['id']) || empty ($_GET['id']) || !(int) $_GET['id'])
				throw new Exception (__ ('Invalid link! [[1]]', @$_GET['id']));

			if (!Lucene::singleton ()->isActive ())
				throw new Exception (__ ('Global search is not active!'));

			$out = Lucene::singleton ()->getIndex ()->getDocument ($_GET['id'])->content;
		}
		catch (Exception $e)
		{
			$out = $e->getMessage ();
		}

		echo '<pre>'. $out .'</pre>';

		break;

	case 'manual':
		if (!file_exists (Instance::singleton ()->getCachePath () .'doc/output/'. Localization::singleton ()->getLanguage () .'/index.html'))
			Manual::generate ();

		$father = trim (@$_GET ['toSection']);

		$business = Business::singleton ();

		$anchor = array ();

		while ($father != '' && $business->sectionExists ($father))
		{
			$anchor [] = str_replace ('.', '_', $father);

			$father = $business->getSection ($father)->getFather ();
		}

		header ('Location: '. str_replace ('titan.php', '', $_SERVER ['PHP_SELF']) . Instance::singleton ()->getCachePath () .'doc/output/'. Localization::singleton ()->getLanguage () .'/index.html#toc:'. implode ('_', array_reverse ($anchor)));

		exit ();

	case 'backup':

		require Instance::singleton ()->getCorePath () .'system/backup.php';

		break;

	case 'social':

		if (!User::singleton ()->isLogged ())
			die ('Access denied!');

		if (!Social::isActive ())
			die ('No one social network is enabled!');

		if (!isset ($_GET['driver']) || trim ($_GET['driver']) == '' ||
			!isset ($_GET['section']) || trim ($_GET['section']) == '' ||
			!isset ($_GET['action']) || trim ($_GET['action']) == '')
			die ('Invalid parameters!');

		$driver = Social::singleton ()->getSocialNetwork ($_GET['driver']);

		if (is_null ($driver))
			die ('Invalid social network!');

		if (!$driver->authenticate ())
			die ('Impossible to authenticate in '. $driver->getName () .'!');

		$id = $driver->getId ();

		if (is_null ($id) || trim ($id) == '')
			die ('Impossible to recovery user ID to '. $driver->getName () .'!');

		try
		{
			$sth = Database::singleton ()->prepare ("UPDATE _user SET ". $driver->getIdColumn () ." = :id WHERE _id = :user");

			$sth->bindParam (':id', $id);
			$sth->bindParam (':user', User::singleton ()->getId (), PDO::PARAM_INT);

			$sth->execute ();
		}
		catch (PDOException $e)
		{
			toLog (print_r ($e, TRUE));
		}

		header ('Location: titan.php?toSection='. $_GET['section'] .'&toAction='. $_GET['action'] .'&social=1');

		exit ();
}
