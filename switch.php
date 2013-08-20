<?
try
{
	if (!isset ($corePath) || !file_exists ($corePath))
		throw new Exception ('Titan Core path on [titan.xml] is invalid!');
	
	require_once $corePath .'function/general.php';
	
	require_once $corePath .'function/legacy.php';
	
	require_once $corePath .'class/Xml.php';
	
	require_once $corePath .'class/Instance.php';
	
	require_once $corePath .'extra/Browscap.php';
	
	if (!@set_include_path (get_include_path () . PATH_SEPARATOR . dirname (__FILE__) .'/extra/'))
		toLog ('Impossible to set include path. This cause Zend Framework load fail!');
	
	include_once 'Zend/Search/Lucene.php';
	
	include_once 'Zend/Search/Lucene/Exception.php';
	
	include_once 'Zend/Uri/Http.php';
	
	include_once 'Zend/Http/CookieJar.php';
	
	include_once 'Zend/Http/Client/Adapter/Socket.php';
	
	include_once 'Zend/Service/Twitter.php';
	
	$blockAccess = array (Instance::singleton ()->getDocPath (), Archive::singleton ()->getDataPath ());
	foreach ($blockAccess as $trash => $path)
		if (!file_exists ($path .'.htaccess') && is_writable ($path))
			if (!@copy (Instance::singleton ()->getCorePath () .'extra/access/.htaccess', $path .'.htaccess'))
				toLog ('Impossible to copy ['. Instance::singleton ()->getCorePath () .'extra/.htaccess] to ['. $path .']');
	
	switch (@$_GET['target'])
	{
		default:
			include $corePath .'titan.php';
			break;
		
		case 'redirect':
			
			if (!isset ($_GET['url']) || trim ($_GET['url']) == '')
				throw new Exception ('Invalid link!');
			
			header ('Location: '. Instance::singleton ()->getUrl () . urldecode ($_GET['url']));
			
			exit ();
		
		case 'viewThumb':
			include $corePath .'system/viewThumb.php';
			break;
		
		case 'openFile':
			include $corePath .'system/openFile.php';
			break;
		
		case 'packer':
			if (!isset ($_GET['files']))
				throw new Exception ('Invalid link!');
			
			$files = explode (',', @$_GET ['files']);
			
			$instance = Instance::singleton ();
			
			$array = array ();
			
			foreach ($files as $key => $file)
			{
				$file = $instance->getCorePath () .'js/'. str_replace ('..', '', $file) .'.js';
				
				if (!file_exists ($file))
					continue;
				
				$array [$file] = file_get_contents ($file);
			}
			
			$path = $instance->getCachePath () .'packed/js/';
			
			if (!file_exists ($path) && !@mkdir ($path, 0777, TRUE))
				throw new Exception (__ ('Impossible to create [[1]].', $path));
			
			$script = implode ("\n", $array);
			
			$assign = md5 ($script);
			
			if (!file_exists ($path . $assign .'.pck') || !(int) filesize ($path . $assign .'.pck'))
			{
				if (!file_exists ($path . $assign .'.js'))
					file_put_contents ($path . $assign .'.js', $script);
				
				// For debug
				// system ('java -jar '. $instance->getCorePath () .'extra/custom_rhino.jar -c '. $path . $assign .'.js > '. $path . $assign .'.pck 2>&1', $trash);
				
				$commands = array ('java', '/usr/bin/java', '/usr/local/bin/java');
				
				set_error_handler ('logPhpError');
				
				foreach ($commands as $trash => $cmd)
				{
					$line = exec ($cmd .' -jar '. $instance->getCorePath () .'extra/custom_rhino.jar -c '. $path . $assign .'.js > '. $path . $assign .'.pck', $output, $return);
				
					if (file_exists ($path . $assign .'.pck') && (int) filesize ($path . $assign .'.pck'))
						break;
					
					// For debug
					// toLog ('Error on shell command. Last line: ['. $line .']. Output: ['. print_r ($output, TRUE) .']. Return: ['. $return .']. Command:  ['. $cmd .' -jar '. $instance->getCorePath () .'extra/custom_rhino.jar -c '. $path . $assign .'.js > '. $path . $assign .'.pck].');
				}
				
				restore_error_handler ();
			}
			
			if (file_exists ($path . $assign .'.pck') && (int) filesize ($path . $assign .'.pck'))
				$packed = file_get_contents ($path . $assign .'.pck');
			else
				$packed = $script;
			
			if (!(bool) ini_get ('zlib.output_compression'))
				ob_start ('ob_gzhandler');
			
			header ('Content-type: text/javascript; charset: UTF-8'); 
			header ('Content-Encoding: gzip');
			
			if (!Instance::singleton ()->onDebugMode ())
			{
				header ('Date: '. date ('D, j M Y G:i:s', filemtime ($file)) .' GMT');
				header ('Expires: '. gmdate('D, d M Y H:i:s', time() + 15552000) .' GMT');
				header ('Cache-Control: must-revalidate');
				header ('Pragma: cache');
			}
			
			echo $packed;
			
			break;

		case 'packerCss':
			if (!isset ($_GET['contexts']))
				throw new Exception ('Invalid link!');

			$contexts = explode (',', @$_GET ['contexts']);

			$instance = Instance::singleton ();

			$skin = Skin::singleton ();

			$array = array ();

			foreach ($contexts as $key => $context)
			{
				$file = $skin->getCss ($context, Skin::PATH);

				if (!file_exists ($file))
					continue;
				
				$array [$file] = file_get_contents ($file);
			}

			$path = $instance->getCachePath () .'packed/css/';

			if (!file_exists ($path) && !@mkdir ($path, 0777, TRUE))
				throw new Exception (__ ('Impossible to create [[1]].', $path));

			$script = implode ("\n", $array);

			$assign = md5 ($script);

			if (!file_exists ($path . $assign .'.pck') || !(int) filesize ($path . $assign .'.pck'))
			{
				require_once $instance->getCorePath () .'extra/cssTidy/class.csstidy.php';

				$css = new csstidy ();

				//$css->set_cfg ('remove_last_;', TRUE);

				$css->load_template ('high_compression');

				$css->parse ($script);

				$packed = $css->print->plain ();

				file_put_contents ($path . $assign .'.pck', $packed);
			}
			else
				$packed = file_get_contents ($path . $assign .'.pck');

			if (!(bool) ini_get ('zlib.output_compression'))
				ob_start ('ob_gzhandler');

			header ('Content-type: text/css; charset: UTF-8');
			header ('Content-Encoding: gzip');

			if (!Instance::singleton ()->onDebugMode ())
			{
				header ('Date: '. date ('D, j M Y G:i:s', filemtime ($file)) .' GMT');
				header ('Expires: '. gmdate('D, d M Y H:i:s', time() + 15552000) .' GMT');
				header ('Cache-Control: must-revalidate');
				header ('Pragma: cache');
			}

			echo $packed;

			break;
		
		case 'js':
			if (!isset ($_GET['files']))
				throw new Exception ('Invalid link!');
			
			$files = explode (',', @$_GET ['files']);

			$array = array ();
			
			foreach ($files as $key => $file)
			{
				$file = Instance::singleton ()->getCorePath () .'js/'. str_replace ('..', '', $file) .'.js';
				
				if (!file_exists ($file))
					continue;
				
				$array [$file] = file_get_contents ($file);
			}
			
			if (!(bool) ini_get ('zlib.output_compression'))
				ob_start ('ob_gzhandler');
			
			header ('Content-type: text/javascript; charset: UTF-8'); 
			header ('Content-Encoding: gzip');
			
			if (!Instance::singleton ()->onDebugMode ())
			{
				header ('Expires: '. gmdate('D, d M Y H:i:s', time() + 15552000) .' GMT');
				header ('Cache-Control: must-revalidate');
				header ('Pragma: cache');
			}
			
			echo implode ("\n\n", $array) ."\n\n// ". implode ("\n// ", array_keys ($array));
			
			break;
		
		case 'loadFile':
			include $corePath .'system/loadFile.php';
			break;
		
		case 'logon':
		case 'login':
			$_TITAN ['TIME_START'] = microtime (TRUE);
			
			include $corePath .'output/login.php';
			
			$_TITAN ['TIME_END'] = microtime (TRUE);
			
			if (Instance::singleton ()->onDebugMode ())
				toLog ('['. $_SERVER['QUERY_STRING'] .'] proccessed in '. number_format ($_TITAN ['TIME_END'] - $_TITAN ['TIME_START'], 4) .' seconds.');
			
			break;
		
		case 'mLogon':
		case 'mLogin':
			include $corePath .'mobile/login.php';
			break;
		
		case 'logoff':
			include $corePath .'system/logoff.php';
			break;
		
		case 'remakePasswd':
			include $corePath .'output/password.php';
			break;
		
		case 'noFirefox':
			include $corePath .'output/firefox.php';
			break;
		
		case 'readRss':
			include $corePath .'extra/dragable-boxes/readRSS.php';
			break;
		
		case 'rss':
			if (!isset ($_GET['toSection']))
				throw new Exception ('Invalid RSS link!');
			
			$instance = Instance::singleton ();
			
			session_name ($instance->getSession () .'_PUBLIC_');

			session_start ();
			
			foreach ($instance->getTypes () as $type => $path)
				require_once $path . $type .'.php';
			
			$business = Business::singleton ();
			
			if (!$business->sectionExists ($_GET ['toSection']))
				throw new Exception ('Invalid RSS link! Unknown section.');
			
			$section = $business->getSection ($_GET ['toSection']);
			
			$action = $section->getAction (Action::TRSS);
			
			Business::singleton ()->setCurrent ($section, $action);
			
			$itemId = 0;
			
			include $action->getFullPathTo (Action::PREPARE);
			
			ob_start ();
			
			include $action->getFullPathTo (Action::VIEW);
			
			$_OUTPUT ['SECTION'] = ob_get_clean ();
					
			include $instance->getCorePath () .'output/rss.php';
			
			break;
		
		case 'register':
			if (!isset ($_GET['type']))
				throw new Exception ('Invalid link!');
			
			if (!Security::singleton ()->getUserType ($_GET['type']))
				throw new Exception ('Invalid link!');
			
			$instance = Instance::singleton ();
			
			session_name ($instance->getSession () .'_PUBLIC_');

			session_start ();
			
			foreach ($instance->getTypes () as $type => $path)
				require_once $path . $type .'.php';
			
			$business = Business::singleton ();
			
			$section = $business->getSection ($_GET ['type']);
			
			$action = $section->getAction (Action::TREGISTER);
			
			$business->setCurrent ($section, $action);
			
			if (isset ($_GET['language']) && trim ($_GET['language']) != '')
				Localization::singleton ()->setLanguage ($_GET['language']);
			
			$itemId = 0;
			
			define ('XOAD_AUTOHANDLE', true);
			
			require_once $instance->getCorePath () .'class/Xoad.php';
			
			if (file_exists ($section->getCompPath () .'_ajax.php'))
				include $section->getCompPath () .'_ajax.php';
			else
				require_once $instance->getCorePath () .'class/Ajax.php';
			
			$allow = array ('Xoad', 'Ajax');
			
			foreach ($instance->getTypes () as $type => $path)
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
			
			require_once $instance->getCorePath () .'extra/fckEditor/fckeditor.php';
			require_once $instance->getCorePath () .'extra/htmlPurifier/HTMLPurifier.standalone.php';
			
			include $action->getFullPathTo (Action::PREPARE);
			
			ob_start ();
			
			include $action->getFullPathTo (Action::VIEW);
			
			$_OUTPUT ['SECTION'] = ob_get_clean ();
			
			include $instance->getCorePath () .'output/register.php';
			
			break;
		
		case 'captcha':
			$instance = Instance::singleton ();
			
			require $instance->getCorePath () .'extra/captcha/captcha.php';
			
			$captcha = new Securimage ();
			
			$captcha->show ();
			
			break;
		
		case 'tScript':
			if (!isset ($_GET['type']) || !isset ($_GET['file']))
				throw new Exception ('Invalid link!');
			
			$authenticate = isset ($_GET['auth']) && (int) $_GET['auth'] ? TRUE : FALSE;
			
			$instance = Instance::singleton ();
			
			foreach ($instance->getTypes () as $type => $path)
				require_once $path . $type .'.php';
			
			if ($authenticate)
				session_name ($instance->getSession ());
			else
				session_name ($instance->getSession () .'_PUBLIC_');
			
			session_start ();
			
			if ($authenticate)
				require $instance->getCorePath () .'system/control.php';
			
			$business = Business::singleton ();
			
			if (!$instance->typeExists ($_GET ['type']))
				throw new Exception ('Invalid link! Unknown type.');
			
			$type = $_GET ['type'];
			
			$path = $instance->getTypePath ($type);
			
			$file = str_replace ('..', '', $_GET['file']);
			
			if (!file_exists ($path .'_script/'. $file .'.php'))
				throw new Exception ('Invalid link! Unknown script.');
			
			include $path .'_script/'. $file .'.php';
			
			break;
		
		case 'script':
			if (!isset ($_GET['toSection']) || !isset ($_GET['file']))
				throw new Exception ('Invalid link!');
			
			$authenticate = isset ($_GET['auth']) && (int) $_GET['auth'] ? TRUE : FALSE;
			
			$file = $_GET['file'];
			
			$instance = Instance::singleton ();
			
			if ($authenticate)
				session_name ($instance->getSession ());
			else
				session_name ($instance->getSession () .'_PUBLIC_');

			session_start ();
			
			if ($authenticate)
				require $instance->getCorePath () .'system/control.php';
			
			foreach ($instance->getTypes () as $type => $path)
				require_once $path . $type .'.php';
			
			$business = Business::singleton ();
			
			if (!$business->sectionExists ($_GET ['toSection']))
				throw new Exception ('Invalid link! Unknown section.');
			
			$section = $business->getSection ($_GET ['toSection']);
			
			$action = $section->getAction (Action::TSCRIPT);
			
			Business::singleton ()->setCurrent ($section, $action);
			
			if (file_exists ($section->getCompPath () .'_class.php'))
				include $section->getCompPath () .'_class.php';
			
			if (file_exists ($section->getCompPath () .'_function.php'))
				include $section->getCompPath () .'_function.php';
			
			if (!file_exists ($section->getCompPath () .'_script/'. str_replace ('..', '', $file) .'.php'))
				throw new Exception ('Invalid link! Unknown script.');
			
			include $section->getCompPath () .'_script/'. str_replace ('..', '', $file) .'.php';
			
			break;
		
		case 'resource':
			if (!isset ($_GET['toSection']) || !isset ($_GET['file']) || trim ($_GET['toSection']) == '' || trim ($_GET['file']) == '')
				throw new Exception ('Invalid link!');
			
			$_section = trim ($_GET['toSection']);
			$_file = trim ($_GET['file']);
			
			include $corePath .'system/loadResource.php';
			
			break;
		
		case 'graph':
			$instance = Instance::singleton ();
			
			if (!isset ($_GET['pieces']) || !is_array ($_GET['pieces']))
				throw new Exception ('Impossible generate graphic! Missing data.');
			
			$pieces = $_GET['pieces'];
			
			$type = isset ($_GET['type']) ? $_GET['type'] : 'PIE';
			
			include $instance->getCorePath () .'extra/jpGraph/jpgraph.php';
			
			function cbFmtPercentage ($value)
			{
				return sprintf ("%.1f%%", 100 * $value);
			}
			
			$width = isset ($_GET['width']) ? (int) $_GET['width'] : 920;
			$height = isset ($_GET['height']) ? (int) $_GET['height'] : 400;
			
			switch ($type)
			{
				default:
				case 'PIE':
					include $instance->getCorePath () .'extra/jpGraph/jpgraph_pie.php';
					include $instance->getCorePath () .'extra/jpGraph/jpgraph_pie3d.php';
					
					$graph = new PieGraph ($width, $height, 'auto');
					
					$graph->SetFrame (FALSE);
					
					$graph->SetColor ('white');
					
					$graph->img->SetTransparent ('white');
					
					if (isset ($_GET['title']))
					{
						$graph->title->Set (urldecode ($_GET['title']));
						
						$graph->title->SetFont (FF_VERDANA, FS_BOLD, 12);
					}
					
					$pie = new PiePlot3D ($pieces);
					
					$pie->SetHeight (15);
					
					$pie->SetCenter (0.5);
					
					$pie->value->SetFont (FF_TREBUCHE, FS_BOLD, 8);
					
					$pie->value->SetColor (array (68, 68, 68));
					
					$pie->ExplodeAll (10);
					
					if (isset ($_GET['legends']) && is_array ($_GET['legends']) && sizeof ($_GET['legends']) == sizeof ($pieces))
					{
						$aux = array ();
						
						foreach ($_GET['legends'] as $key => $value)
							$aux [] = urldecode ($value ." - ". number_format ($pieces [$key], 0, ',', '.') ." (%.1f%%)");
						
						$pie->SetLabels ($aux, 1);
					}
					
					$graph->Add ($pie);
					
					$graph->Stroke ();
					
					break;
				
				case 'BAR':
					include $instance->getCorePath () .'extra/jpGraph/jpgraph_bar.php';
					
					$graph = new Graph ($width, $height);
					
					$graph->SetScale ('textlin');
					
					$graph->SetFrame (FALSE);
					
					$graph->SetColor ('white');
					
					$graph->img->SetTransparent ('white');
					
					if (isset ($_GET['title']))
					{
						$graph->title->Set (urldecode ($_GET['title']));
						
						$graph->title->SetFont (FF_VERDANA, FS_BOLD, 12);
					}
					
					$total = array_sum ($pieces);
					
					$aux = array ();
					foreach ($pieces as $trash => $value)
						$aux [] = ($value * 100) / $total;
					
					$bar = new BarPlot ($pieces);
					
					if (isset ($_GET['legends']) && is_array ($_GET['legends']) && sizeof ($_GET['legends']) == sizeof ($pieces))
					{
						$legs = array ();
						
						foreach ($_GET['legends'] as $key => $value)
							$legs [] = urldecode ($value ."\n". number_format ($aux [$key], 1, ',', '.') ."%");
						
						$graph->xaxis->SetTickLabels ($legs);
					}	
					
					$graph->Add ($bar);
					
					$graph->Stroke ();
					
					break;
			}
			
			break;
		
		case 'earth':
			if (!isset ($_GET['latitude']) || !isset ($_GET['longitude']))
				throw new Exception ('Missing data!');
			
			header ('Content-Type: application/vnd.google-earth.kml+xml');
			header ('Content-Disposition: inline; filename=earth_'. randomHash (10) .'.kml;');
			
			echo '<?xml version="1.0" encoding="UTF-8"?>';
			?>
			<kml xmlns="http://earth.google.com/kml/2.1">
			  <Placemark>
				<name><?= isset ($_GET['title']) ? urldecode ($_GET['title']) : Instance::singleton ()->getName () ?></name>
				<description>
					<![CDATA[
					<?= isset ($_GET['description']) ? urldecode ($_GET['description']) : '' ?>
					]]>
				</description>
				<Point>
				  <coordinates><?= Coordinate::toKml ($_GET['longitude']) ?>,<?= Coordinate::toKml ($_GET['latitude']) ?>,<?= isset ($_GET['altitude']) ? urldecode ($_GET['altitude']) : '0' ?></coordinates>
				</Point>
			  </Placemark>
			</kml>
			<?
			break;
		
		case 'license':
			include $corePath .'output/license.php';
			
			break;
		
		case 'unreadAlerts':
			if (!isset ($_GET ['id']) || !is_numeric ($_GET ['id']) || !(int) $_GET ['id'])
				die ('0');
			
			if (!Database::tableExists ('_alert'))
				die ('0');
			
			$sql = "SELECT count(*) FROM _alert_user u INNER JOIN _alert a ON a._id = u._alert WHERE
					u._user = '". (int) $_GET['id'] ."' AND u._read = B'0' AND u._delete = B'0' AND (a._until IS NULL OR a._until > CURRENT_TIMESTAMP)";
			
			$query = Database::singleton ()->query ($sql);
			
			echo $query->fetchColumn ();
			
			exit ();
		
		case 'itemsInShoppingCart':
			
			if (!isset ($_GET ['id']) || !is_numeric ($_GET ['id']) || !(int) $_GET ['id'])
				die ('0');
			
			if (!Shopping::isActive ())
				die ('0');
			
			echo Shopping::singleton ()->getNumberOfItemsInShoppingCart ($_GET ['id']);
			
			exit ();
		
		case 'schedule':
			if (!isset ($_GET['hash']) || trim ($_GET['hash']) == '' || Schedule::singleton ()->getHash () != $_GET['hash'])
			{
				sleep (3);
				
				die ('Invalid hash!');
			}
			
			if (!isset ($_GET['job']) || trim ($_GET['job']) == '')
				die ('No job specified!');
			
			Schedule::run ($_GET['job']);
			
			break;
		
		case 'disableAlerts':
			
			sleep (1);
			
			if (!Database::tableExists ('_alert'))
				die ('Invalid link!');
			
			if (!isset ($_GET['login']) || trim ($_GET['login']) == '' || !isset ($_GET['hash']) || strlen (trim ($_GET['hash'])) != 8)
				die ('Invalid link!');
			
			$login = trim (urldecode ($_GET['login']));
			
			try
			{
				$db = Database::singleton ();
				
				$sth = $db->prepare ("SELECT _name, _email, _id FROM _user WHERE _active = B'1' AND _deleted = B'0' AND _login = :login");
				
				$sth->bindParam (':login', $login, PDO::PARAM_STR);
				
				$sth->execute ();
				
				$obj = $sth->fetch (PDO::FETCH_OBJ);
				
				$hash = Security::singleton ()->getHash ();
				
				$h = shortlyHash (md5 ($hash . $obj->_name . $hash . $obj->_id . $hash . $obj->_email . $hash));
				
				if (trim ($_GET['hash']) != $h)
					die ('Invalid link!');
				
				$sth = $db->prepare ("UPDATE _user SET _alert = B'0' WHERE _login = :login");
				
				$sth->bindParam (':login', $login, PDO::PARAM_STR);
				
				$sth->execute ();
				
				include $corePath .'output/alert.php';
				
				exit ();
			}
			catch (PDOException $e)
			{
				toLog ($e->getMessage ());
				
				die ('Invalid link!');
			}
			
			break;
		
		case 'setClientTimeZone':
			
			if (!isset ($_GET['z']) || trim ($_GET['z']) == '' || isset ($_COOKIE['_TITAN_TIMEZONE_']))
				exit ();
			
			setcookie ('_TITAN_TIMEZONE_', trim ($_GET['z']), time () + 60 * 60 * 24 * 30 * 12);
			
			break;
		
		case 'api':
			
			try
			{
				set_error_handler ('apiPhpError', E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
				
				if (isset ($_GET['language']) && trim ($_GET['language']) != '')
					Localization::singleton ()->setLanguage ($_GET['language']);
				
				switch (@$_GET ['function'])
				{
					case 'auth':
						
						require $corePath .'api/auth.php';
						
						break;
					
					case 'alerts':
						
						require $corePath .'api/alerts.php';
						
						break;
				}
				
				restore_error_handler ();
			}
			catch (ApiException $e)
			{
				header ('HTTP/1.1 '. $e->getCode () .' '. ApiException::$status [$e->getCode ()]);
				header ('Content-Type: application/json');
				
				$array = array ('ERROR' => $e->getTitanErrorCode (),
								'MESSAGE' => $e->getMessage (),
								'TECHNICAL' => $e->getTitanTechnical ());
				
				echo json_encode ($array);
			}
			catch (PDOException $e)
			{
				toLog ($e->getMessage ());
				
				header ('HTTP/1.1 '. ApiException::INTERNAL_SERVER_ERROR .' '. ApiException::$status [ApiException::INTERNAL_SERVER_ERROR]);
				header ('Content-Type: application/json');
				
				$array = array ('ERROR' => 'DATABASE_ERROR',
								'MESSAGE' => __ ('Database error! Please, contact administrator.'),
								'TECHNICAL' => $e->getMessage ());
				
				echo json_encode ($array);
			}
			catch (Exception $e)
			{
				toLog ($e->getMessage ());
				
				header ('HTTP/1.1 '. ApiException::INTERNAL_SERVER_ERROR .' '. ApiException::$status [ApiException::INTERNAL_SERVER_ERROR]);
				header ('Content-Type: application/json');
				
				$array = array ('ERROR' => 'SYSTEM_ERROR',
								'MESSAGE' => 'System error! Please, contact administrator.',
								'TECHNICAL' => $e->getMessage ());
				
				echo json_encode ($array);
			}
			
			break;
		
		case 'tools':
			
			if (!isset ($_GET['tool']) || trim ($_GET['tool']) == '')
				throw new Exception ('Invalid link!');
			
			if (!Instance::singleton ()->onDebugMode ())
				throw new Exception ('Permission denied!');
			
			$tools = Instance::singleton ()->getTools ();
			
			if (!array_key_exists ($_GET['tool'], $tools))
				throw new Exception ('Tool not available!');
			
			require $tools [$_GET['tool']];
			
			break;
	}
}
catch (Exception $e)
{
	echo $e->getMessage ();
}
catch (PDOException $e)
{
	echo $e->getMessage ();
}
?>