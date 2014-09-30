<?php

function __autoload ($class)
{
	$file = Instance::singleton ()->getCorePath () .'class'. DIRECTORY_SEPARATOR . $class . '.php';
	
	if (!file_exists ($file))
		return FALSE;
	
	require_once $file;
}

function __ ()
{
	return Localization::singleton ()->translate (func_get_args ());
}

function month ($mes = NULL)
{
	if ($mes == NULL || $mes > 12 || $mes < 1)
		$mes = date ('m');

	$meses = array ( 1 => 'Janeiro',
					 2 => 'Fevereiro',
					 3 => 'Mar&ccedil;o',
					 4 => 'Abril',
					 5 => 'Maio',
					 6 => 'Junho',
					 7 => 'Julho',
					 8 => 'Agosto',
					 9 => 'Setembro',
					10 => 'Outubro',
					11 => 'Novembro',
					12 => 'Dezembro');

	return $meses [(int) $mes];
}

function fileName ($str)
{
	$str = substr ($str, 0, 255);

	$str = strtolower ($str);

	$trade = array ('á'=>'a','à'=>'a','ã'=>'a',
					'ä'=>'a','â'=>'a',
					'Á'=>'A','À'=>'A','Ã'=>'A',
					'Ä'=>'A','Â'=>'A',
					'é'=>'e','è'=>'e',
					'ë'=>'e','ê'=>'e',
					'É'=>'E','È'=>'E',
					'Ë'=>'E','Ê'=>'E',
					'í'=>'i','ì'=>'i',
					'ï'=>'i','î'=>'i',
					'Í'=>'I','Ì'=>'I',
					'Ï'=>'I','Î'=>'I',
					'ó'=>'o','ò'=>'o','õ'=>'o',
					'ö'=>'o','ô'=>'o',
					'Ó'=>'O','Ò'=>'O','Õ'=>'O',
					'Ö'=>'O','Ô'=>'O',
					'ú'=>'u','ù'=>'u',
					'ü'=>'u','û'=>'u',
					'Ú'=>'U','Ù'=>'U',
					'Ü'=>'U','Û'=>'U',
					'$'=>'_','@'=>'_','!'=>'_',
					'#'=>'_','%'=>'_','"'=>'',
					'^'=>'_','&'=>'_','*'=>'_',
					'('=>'_',')'=>'_',"'"=>'',
					'-'=>'_','+'=>'_','='=>'_',
					'\\'=>'_','|'=>'_',
					'`'=>'_','~'=>'_','/'=>'_',
					'\"'=>'_','\''=>'_',
					'<'=>'_','>'=>'_','?'=>'_',
					','=>'_','ç'=>'c','Ç'=>'C',' '=>'_');

	$str = strtr ($str, $trade);

	return $str;
}

function removeAccents ($str)
{
	$str = htmlentities (html_entity_decode ($str, ENT_NOQUOTES, 'UTF-8'), ENT_NOQUOTES, 'UTF-8');

	$str = preg_replace ('/&([a-zA-Z])(uml|acute|grave|circ|tilde|cedil);/', '$1', $str);

	return html_entity_decode ($str, ENT_NOQUOTES, 'UTF-8');
}

function resize ($file, $type, $width = 0, $height = 0, $force = FALSE, $bw = FALSE)
{
	$buffer = FALSE;

	switch ($type)
	{
		case 'image/jpeg':
		case 'image/pjpeg':
			$buffer = imagecreatefromjpeg ($file);
			break;

		case 'image/gif':
			$buffer = imagecreatefromgif ($file);
			break;

		case 'image/png':
			$buffer = imagecreatefrompng ($file);
			imagealphablending ($buffer, TRUE);
			imagesavealpha ($buffer, TRUE);
			break;
	}

	if (!$buffer)
		throw new Exception ('MimeType do arquivo inválido ou a imagem não existe!');
	
	if ($bw)
		@imagefilter ($buffer, IMG_FILTER_GRAYSCALE);
	
	$vetor = getimagesize ($file);

	$atualWidth  = $vetor [0];
	$atualHeight = $vetor [1];

	if(!$force)
	{
		if (!$width || !$height)
		{
			if (!$width && !$height)
			{
				$width = $atualWidth;
				$height = $atualHeight;
			}
			elseif ($width && !$height)
				$height = ($atualHeight * $width) / $atualWidth;
			else
				$width = ($atualWidth * $height) / $atualHeight;
		}

		if ($atualWidth < $atualHeight && $width > $height)
		{
			$aux = $width;
			$width = $height;
			$height = $aux;
		}

		if ((int) $atualWidth < (int) $width)
		{
			$width = $atualWidth;

			$height = ($atualHeight * $width) / $atualWidth;
		}
	}

	if ($type != 'image/gif')
	{
		$thumb = imagecreatetruecolor ($width, $height);
		$color = imagecolorallocatealpha ($thumb, 255, 255, 255, 75);
		imagefill ($thumb, 0, 0, $color);
	}
	else
		$thumb = imagecreate ($width, $height);

	$ok = imagecopyresized ($thumb, $buffer, 0, 0, 0, 0, $width, $height, $atualWidth, $atualHeight);

	if (!$ok)
		throw new Exception ('Impossível redimensionar a imagem!');

	header ('Content-Type: '. $type);

	switch ($type)
	{
		case 'image/jpeg':
		case 'image/pjpeg':
			imagejpeg ($thumb, NULL, 100);
			break;

		case 'image/gif':
			imagegif ($thumb);
			break;

		case 'image/png':
			imagepng ($thumb);
			break;
	}

	imagedestroy ($thumb);

	exit ();
}

function getBreadPath ($section, $withLink = TRUE, $setMenu = TRUE)
{
	if (!$section)
		return '';

	$business = Business::singleton ();

	$father = $business->getSection ($section->getFather ());
	
	if ($setMenu)
	{
		global $menuPosition;
	
		$menuPosition [] = $section->getName ();
	}
	
	return getBreadPath ($father, $withLink) . ($withLink && !$section->isFake () ? '<a href="titan.php?target=body&amp;toSection='. $section->getName () .'">'. $section->getLabel () .'</a>' : $section->getLabel ()) .' &raquo; ';
}

function keyboard ($link = NULL, $color = '999999')
{
	static $counter = 0;

    if (!$link || empty ($link) || trim ($link) == '')
		$link = $_SERVER['PHP_SELF'] .'?'. $_SERVER['QUERY_STRING'];
    ?>
    <table align="center" width="1px" border="0" cellpadding="0" cellspacing="3">
		<form name="keyboard<?= $counter ?>" action="<?= $link ?>" method="POST">
		<input type="hidden" id="keyboard_id_<?= $counter ?>" name="letter" value="">
		<tr>
			<?
			for ($i = 0 ; $i < 13 ; $i++)
			{
				?>
				<td width="1px">
					<input type="submit" class="button" style="width: 20px; color: #<?= $color ?>; border-color: #<?= $color ?>;" value="<?= chr($i + 65) ?>" onclick="JavaScript: document.getElementById ('keyboard_id_<?= $counter ?>').value = '<?= chr ($i + 65) ?>'; return true;">
				</td>
				<?
			}
			?>
			<td width="1px" rowspan=2>
				<input type="submit" class="button" style="width: 50px; height: 43px; color: #<?= $color ?>; border-color: #<?= $color ?>;" value="Todas">
			</td>
		</tr>
		<tr>
			<?
			for ($i = 13 ; $i < 26 ; $i++)
			{
				?>
				<td width="1px">
					<input type="submit" class="button" style="width: 20px; color: #<?= $color ?>; border-color: #<?= $color ?>;" value="<?= chr ($i + 65) ?>" onclick="JavaScript: document.getElementById ('keyboard_id_<?= $counter ?>').value = '<?= chr ($i + 65) ?>'; return true;">
				</td>
				<?
			}
			?>
		</tr>
		</form>
	</table>
    <?
	$counter++;
}

function makeMenu ($previous = FALSE, $father = '', $sectionName = '')
{
	$business = Business::singleton ();

	$children = $business->getChildren ($father);

	if (!sizeof ($children))
		return FALSE;

	global $menuHeight;

	$user = User::singleton ();

	$array = array ();

	$output = array ();

	ob_start ();

	if ($previous === FALSE)
		echo '<li style="background-color: #333333; background-image: none;"></li>';
	else
	{
		?>
		<li style="background: #333333 url(titan.php?target=loadFile&amp;file=interface/image/arrow.left.gif) left no-repeat; padding-left: 3px;" onclick="JavaScript: backMenu ('<?= $father ?>', '<?= $previous ?>');">
			<label><?= __('Back')?></label>
		</li>
		<?
	}

	$header = ob_get_clean ();

	if ($father != '' && !$business->getSection ($father)->isFake () && !$business->getSection ($father)->isHidden ())
		if ($user->accessSection ($business->getSection ($father)->getName ()))
			$output [] = '<li style="background-image: none;" onclick="JavaScript: document.location = \'titan.php?target=body&amp;toSection='. $father .'\';" title="'. $business->getSection ($father)->getDescription () .'"><label>'. $business->getSection ($father)->getLabel () .'</label></li>';
		elseif (Instance::singleton ()->showAllSections ())
			$output [] = '<li style="background-image: none;"><label style="color: #AAAAAA;" title="'. $business->getSection ($father)->getDescription () .'">'. $business->getSection ($father)->getLabel () .'</label></li>';

	foreach ($children as $trash => $section)
	{
		if (array_key_exists ($father, $menuHeight))
			$menuHeight [$father]++;
		else
			$menuHeight [$father] = 1;
		
		if ($section->isHidden ())
			continue;

		$next = makeMenu ($father, $section->getName (), $section->getLabel ());

		if (is_array ($next))
		{
			$array = array_merge ($array, $next);
			$output [] = '<li onclick="JavaScript: slideMenu (\''. $father .'\', \''. $section->getName () .'\');"><label>'. $section->getLabel () .'</label></li>';
		}
		elseif ($user->accessSection ($section->getName ()))
			$output [] = '<li style="background-image: none;" onclick="JavaScript: document.location = \'titan.php?target=body&amp;toSection='. $section->getName () .'\';" title="'. $section->getDescription () .'"><label>'. $section->getLabel () .'</label></li>';
		elseif (Instance::singleton ()->showAllSections ())
			$output [] = '<li style="background-image: none;"><label style="color: #AAAAAA;" title="'. $section->getDescription () .'">'. $section->getLabel () .'</label></li>';
	}

	if (!sizeof ($output))
		return FALSE;

	ob_start ();
	?>
	<div class="menuMain" id="menuMain_<?= $father ?>" style="<?= $father == '' ? 'display: block; left: 0px;' : 'display: none; left: 200px;' ?>">
		<ul>
			<?
			echo $header;

			foreach ($output as $trash => $item)
				echo $item;
			?>
		</ul>
	</div>
	<?
	$array [] = ob_get_clean ();

	return $array;
}

function copyDir ($srcdir, $dstdir, $verbose = FALSE)
{
	$num = 0;

	$noCopy = array ('.', '..', 'Thumbs.db', '.svn');

	if(!is_dir($dstdir)) mkdir($dstdir);

	if($curdir = opendir($srcdir))
	{
		while($file = readdir($curdir))
		{
			if(!in_array ($file, $noCopy))
			{
				$srcfile = $srcdir . DIRECTORY_SEPARATOR . $file;
				$dstfile = $dstdir . DIRECTORY_SEPARATOR . $file;

				if(is_file ($srcfile))
				{
					if(is_file ($dstfile)) $ow = filemtime ($srcfile) - filemtime ($dstfile); else $ow = 1;

					if($ow > 0)
					{
						if ($verbose) echo "Copiando...<br />'$srcfile' &raquo; '$dstfile' ";

						if (copy($srcfile, $dstfile))
						{
							touch($dstfile, filemtime($srcfile)); $num++;
							if ($verbose) echo '<br /><label style="color: #009900;">OK</label><br />';
						}
						else
						{
							if ($verbose) echo '<br /><label style="color: #009900;">Error: File \'$srcfile\' could not be copied!</label><br />';
							exit ();
						}
					}
				}
				elseif(is_dir($srcfile))
					$num += copyDir ($srcfile, $dstfile, $verbose);
			}
		}

		closedir ($curdir);
	}

	return $num;
}

function removeDir ($path)
{
	if (is_dir ($path))
	{
		foreach (glob ($path .'/*') as $file)
			if (is_file ($file))
				unlink ($file);
			elseif (is_dir ($file))
				removeDir ($file);

		rmdir ($path);
	}
}

function dirSize ($dir)
{
	if (!is_dir($dir))
		return FALSE;

	$dh = opendir($dir);
	$size = 0;

	while (($file = readdir($dh)) !== false)
		if ($file != '.' && $file != '..')
		{
			$path = $dir .'/'. $file;

			if (is_dir ($path))
				$size += dirSize ($path);
			elseif (is_file($path))
				$size += filesize ($path);
		}

	closedir($dh);

	return $size;
}

function randomHash ($size = 32)
{
	$hash = '';

	while (strlen ($hash) < $size)
		$hash .= substr ('0123456789abcdef', mt_rand (0,15), 1);

	return $hash;
}

function logPhpError ($errno, $errstr, $errfile, $errline)
{
	$errorType = array (E_ERROR				=> 'ERROR',
						E_WARNING			=> 'WARNING',
						E_PARSE				=> 'PARSING ERROR',
						E_NOTICE			=> 'NOTICE',
						E_CORE_ERROR		=> 'CORE ERROR',
						E_CORE_WARNING		=> 'CORE WARNING',
						E_COMPILE_ERROR		=> 'COMPILE ERROR',
						E_COMPILE_WARNING	=> 'COMPILE WARNING',
						E_USER_ERROR		=> 'USER ERROR',
						E_USER_WARNING		=> 'USER WARNING',
						E_USER_NOTICE		=> 'USER NOTICE',
						E_STRICT			=> 'STRICT NOTICE',
						E_RECOVERABLE_ERROR	=> 'RECOVERABLE ERROR');

	$err = array_key_exists ($errno, $errorType) ? $errorType [$errno] : 'CAUGHT EXCEPTION';

	toLog ('['. $err .'] '. $errstr .' [File: '. $errfile .'] [Line: '. $errline .']');
	
	return TRUE;
}

function toLog ($message)
{
	if (file_exists ('FirePHPCore/FirePHP.class.php'))
		@include_once ('FirePHPCore/FirePHP.class.php');
	
	if (class_exists ('FirePHP', FALSE) && !headers_sent ())
	{
		$firePhp = FirePHP::getInstance (TRUE);
		
		$firePhp->log ($message);
	}
	
	$path = Instance::singleton ()->getCachePath () .'log/';

	if (!file_exists ($path) && !@mkdir ($path, 0777))
		throw new Exception ('Impossible to create folder ['. $path .'].');
	
	if (!file_exists ($path .'.htaccess') && !file_put_contents ($path .'.htaccess', 'deny from all'))
		throw new Exception ('Impossible to enhance security for folder ['. $path .'].');

	$fd = fopen ($path .'log.'. date ('Ym'), 'a');

	if (!$fd)
		throw new Exception ('Impossible to open/create LOG file. Verify permissions on folder ['. $path .']!');

	if (!fwrite ($fd, date ('d-m-Y H:i:s') ." [". $_SERVER['REMOTE_ADDR'] ."]\n". $message ."\n\n"))
		throw new Exception ('Impossible to write in LOG file. Verify permissions on folder and file ['. $path .']!');

	fclose ($fd);
}

function apiPhpError ($errno, $errstr, $errfile, $errline)
{
	$errorType = array (E_ERROR				=> 'ERROR',
						E_WARNING			=> 'WARNING',
						E_PARSE				=> 'PARSING ERROR',
						E_NOTICE			=> 'NOTICE',
						E_CORE_ERROR		=> 'CORE ERROR',
						E_CORE_WARNING		=> 'CORE WARNING',
						E_COMPILE_ERROR		=> 'COMPILE ERROR',
						E_COMPILE_WARNING	=> 'COMPILE WARNING',
						E_USER_ERROR		=> 'USER ERROR',
						E_USER_WARNING		=> 'USER WARNING',
						E_USER_NOTICE		=> 'USER NOTICE',
						E_STRICT			=> 'STRICT NOTICE',
						E_RECOVERABLE_ERROR	=> 'RECOVERABLE ERROR');

	$err = array_key_exists ($errno, $errorType) ? $errorType [$errno] : 'CAUGHT EXCEPTION';
	
	toLog ('['. $err .'] '. $errstr .' [File: '. $errfile .'] [Line: '. $errline .']');
	
	header ('HTTP/1.1 500 Internal Server Error');
	header ('Content-Type: application/json');
	
	$array = array ('ERROR' => 'SYSTEM_ERROR',
					'MESSAGE' => 'System error! Please, contact administrator.',
					'TECHNICAL' => 'PHP Error: '. $err);
	
	echo json_encode ($array);
	
	exit ();
}

function xmlCache ($file, $array, $path = FALSE)
{
	if ($path === FALSE)
		$path = Instance::singleton ()->getCachePath () .'parsed/';
	
	if (!file_exists ($path) && !@mkdir ($path, 0777))
		throw new Exception ('Impossible to create folder ['. $path .'].');
	
	$content  = "<? \n";
	$content .= "/* ". date ('d-m-Y H:i:s') ." */ \n\n";
	$content .= "return ". var_export ($array, TRUE) ."; \n";
	$content .= "?>";

	@file_put_contents ($file, $content);
}

function isFirefox ($force = FALSE)
{
	if (!$force && isset ($_SESSION['_BROWSCAP_']))
		return $_SESSION['_BROWSCAP_'];

	$instance = Instance::singleton ();

	if (!$force && !$instance->onlyFirefox ())
	{
		$_SESSION['_BROWSCAP_'] = TRUE;

		return TRUE;
	}

	$cache = $instance->getCachePath ();

	if (!file_exists ($cache .'browscap') && !mkdir ($cache .'browscap', 0775))
		throw new Exception (__ ('Impossible to create cache directory. Verify on [<b>titan.xml</b>] file if propertie [<b>&lt;titan-configuration cache-path=""&gt;&lt;/titan-configuration&gt;</b>] has a valid path and <b>write permission</b>.'));

	try
	{
		$browscap = new Browscap ($cache .'browscap');

		$browser = $browscap->getBrowser ();
	}
	catch (Exception $e)
	{
		toLog ($e->getMessage ());

		$_SESSION['_BROWSCAP_'] = TRUE;

		return TRUE;
	}

	$compatible = array ('Firefox', 'Flock', 'IceWeasel', 'Madfox', 'SeaMonkey');

	$flag = (is_object ($browser) && in_array (trim ($browser->Browser), $compatible));

	if (!$flag)
		toLog ('Incompatible Browser: ['. $browser->Browser .']');
	
	if (!$force)
		$_SESSION['_BROWSCAP_'] = $flag;

	return $flag;
}

function getBrowser ()
{
	$cache = Instance::singleton ()->getCachePath ();
	
	if (!file_exists ($cache .'browscap') && !mkdir ($cache .'browscap', 0775))
		return '';
	
	try
	{
		$browscap = new Browscap ($cache .'browscap');
		
		if (!is_object ($browscap))
			throw new Exception ('Error to get Browscap object!');
		
		$browser = $browscap->getBrowser ();
		
		if (!is_object ($browser) || !isset ($browser->Parent))
			throw new Exception ('Error to get Browscap BROWSER object!');
		
		return $browser->Parent;
	}
	catch (Exception $e)
	{
		toLog ($e->getMessage ());
	}
	
	return '';
}

function swf ($path, $width, $height)
{
	$name = randomHash ();
	?>
	<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="<?= $width ?>" height="<?= $height ?>" id="<?= $name ?>" align="middle">
	<param name="movie" value="<?= $path ?>" />
	<param name="wmode" value="transparent" />
	<param name="quality" value="best" />
	<param name="allowScriptAccess" value="sameDomain" />
	<embed wmode="transparent" src="<?= $path ?>" quality="best" width="<?= $width ?>" height="<?= $height ?>" align="middle" allowscriptaccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
	</object>
	<?
}

function encrypt ($input)
{
	$cipher = mcrypt_module_open (MCRYPT_BLOWFISH, '', 'cbc', '');
	
	mcrypt_generic_init ($cipher, Security::singleton ()->getHash (), '84826372');

	$encrypt = base64_encode (mcrypt_generic ($cipher, $input));
	
	mcrypt_generic_deinit ($cipher);
	
	return $encrypt;
}

function decrypt ($encrypted)
{
	$cipher = mcrypt_module_open (MCRYPT_BLOWFISH, '', 'cbc', '');
	
	mcrypt_generic_init ($cipher, Security::singleton ()->getHash (), '84826372');
	
	$decrypt = mdecrypt_generic ($cipher, base64_decode ($encrypted));
	
	mcrypt_generic_deinit ($cipher);
	
	return $decrypt;
}

function tableExists ($name)
{
	return Database::tableExists ($name);
}

function cleanArray (&$item, $key)
{
	$item = trim ($item);
}

function toUtf8 (&$item, $key)
{
	$item = utf8_encode ($item);
}

function relevant ($str, $terms)
{
	$str = strtolower ($str);
	
	$positions = array ();
	foreach ($terms as $trash => $term)
	{
		$pos = strpos ($str, $term);
		
		if ($pos === FALSE)
			continue;
		
		$positions [$term] = $pos;
	}
	
	asort ($positions);
	
	$pieces = array ();
	foreach ($positions as $trash => $start)
	{
		$next = next ($positions);
		
		$end = $start + 200;
		
		while ($next !== FALSE && $next < $end)
		{
			$end = $next + 200;
			
			$next = next ($positions);
		}
		
		prev ($positions);
		
		$start = $start - 50 < 0 ? 0 : $start - 50;
		
		$end = $end - 50;
		
		$start = !$start ? 0 : strrpos (substr ($str, 0, $start), ' ');
		
		if (!is_integer ($start))
			$start = 0;
		
		$sub = substr ($str, $start, $end - $start);
		
		$length = strrpos ($sub, ' ');
		
		$length = !$length ? strlen ($sub) - 1 : $length;
		
		$pieces [] = trim (substr ($str, $start, $length));
	}
	
	$output = '...'. implode ('...', $pieces) .'...';
	
	foreach ($positions as $term => $trash)
		$output = preg_replace ("|($term)|Ui", "<span style=\"background: #009; color: #FFF;\"><b>$1</b></span>", $output);
	
	return $output;
}

function translate ($str)
{
	$array = explode ('|', $str);

	if (sizeof ($array) < 2)
		return $str;
	
	$language = Localization::singleton ()->getLanguage ();

	foreach ($array as $key => $value)
	{
		$aux = explode (':', $value);

		if (!$key)
			$str = sizeof ($aux) > 1 ? $aux [1] : $aux [0];

		if ($language != trim ($aux [0]))
			continue;

		return trim ($aux [1]);
	}
	
	return $str;
}

function shortlyHash ($hash)
{
	$valid = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxwz_-';
	
	$i = 0;
	
	$short = '';
	while ($i < strlen ($hash))
		$short .= $valid [hexdec ($hash [$i++] . $hash [$i++] . $hash [$i++] . $hash [$i++]) % 64];
	
	return $short;
}

function retrievePut ()
{
	$_POST = array ();
	$_FILES = array ();
	
	$raw = file_get_contents ('php://input');
	
	$boundary = substr ($raw, 0, strpos($raw, "\r\n"));
	
	if (empty ($boundary))
	{
		parse_str ($raw, $_POST);
		
		return;
	}
	
	$parts = array_slice (explode ($boundary, $raw), 1);
	
	$data = array ();
	
	foreach ($parts as $part)
	{
		// If this is the last part, break
		if ($part == "--\r\n")
			break;
		
		// Separate content from headers
		$part = ltrim ($part, "\r\n");
		
		list ($rawHeaders, $body) = explode ("\r\n\r\n", $part, 2);
		
		// Parse the headers list
		$rawHeaders = explode ("\r\n", $rawHeaders);
		
		$headers = array ();
		
		foreach ($rawHeaders as $header)
		{
			list ($name, $value) = explode (':', $header);
			
			$headers [strtolower ($name)] = ltrim ($value, ' ');
		}
		
		// Parse the Content-Disposition to get the field name, etc.
		if (isset ($headers ['content-disposition']))
		{
			$filename = NULL;
			
			$tmp_name = NULL;
		
			preg_match ('/^(.+); *name="([^"]+)"(; *filename="([^"]+)")?/', $headers ['content-disposition'], $matches);
			
			list(, $type, $name) = $matches;
			
			//Parse File
			if (isset ($matches[4]))
			{
				//if labeled the same as previous, skip
				if (isset ($_FILES [$matches [2]]))
					continue;
				
				//get filename
				$filename = $matches [4];
				
				//get tmp name
				$filename_parts = pathinfo ($filename);
				
				$tmp_name = tempnam (ini_get ('upload_tmp_dir'), $filename_parts ['filename']);
				
				//populate $_FILES with information, size may be off in multibyte situation
				$_FILES [$matches [2]] = array (
					'error' => 0,
					'name' => trim ($filename),
					'tmp_name' => $tmp_name,
					'size' => strlen ($body),
					'type' => trim ($value)
				);
	
				//place in temporary directory
				file_put_contents ($tmp_name, $body);
			}
			else
				$data [$name] = substr ($body, 0, strlen ($body) - 2);
		}
	}
	
	$_POST = $data;
}

function convertApiParametersToUtf8 ()
{
	require Instance::singleton ()->getCorePath () .'extra/Encoding.php';
	
	array_walk_recursive($_POST, function (&$item, $key)
	{
		$item = Encoding::toUTF8 ($item);
	});
}

if (!function_exists ('apache_request_headers'))
{
	function apache_request_headers()
	{
		$arh = array ();
		
		$rx_http = '/\AHTTP_/';
	  	
		foreach ($_SERVER as $key => $val)
			if (preg_match ($rx_http, $key)) 
			{
				$arh_key = preg_replace ($rx_http, '', $key);
				
				$rx_matches = array ();
				
				$rx_matches = explode ('_', $arh_key);
				
				if (count ($rx_matches) > 0 && strlen ($arh_key) > 2) 
				{
					foreach ($rx_matches as $ak_key => $ak_val)
						$rx_matches[$ak_key] = ucfirst($ak_val);
					
					$arh_key = implode ('-', $rx_matches);
				}
				
				$arh [strtolower ($arh_key)] = $val;
	    	}
		
	  	return ($arh);
	}
}