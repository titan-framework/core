<pre>
<?php
set_time_limit (0);

require Instance::singleton ()->getCorePath () .'extra/GoogleTranslate.php';

$xmls = glob ('configure/*.xml');

foreach ($xmls as $trash => $file)
{
	translateXml ($file, $file);
	
	echo " SUCCESS > Generated [". $file ."] \n";
}

echo " SUCCESS > All configuration files translated! \n\n";

$source = 'section/';

$ignore = array ('.', '..', '.svn');

if (is_dir ($source) && $dh = opendir ($source))
	while (($dir = readdir ($dh)) !== false)
	{
		if (!is_dir ($source . $dir) || in_array ($dir, $ignore))
			continue;
		
		$xmls = glob ($source . $dir .'/*.xml');
		
		foreach ($xmls as $trash => $file)
		{
			translateXml ($file, $file);
		
			echo " SUCCESS > Generated [". $file ."] \n";
		}
	}

@closedir ($dh);

echo " SUCCESS > All sections translated! \n\n";

echo " SUCCESS > All files translated!";

function translateXml ($source, $destination)
{
	$xml = file ($source);
	
	$atts = array ('label', 'doc', 'help', 'description', 'warning');
	
	$langs = array ('pt_BR' => 'pt-br', 'en_US' => 'en', 'es_ES' => 'es');
	
	$buffer = "";
	
	foreach ($xml as $trash => $line)
	{
		foreach ($atts as $trash => $att)
		{
			unset ($match);
			unset ($pieces);
			
			$match = array ();
			
			preg_match ('/'. $att .'=\"([^\"]*)\"/i', $line, $match);
			
			if (sizeof ($match) < 2 || trim ($match [1]) == '')
				continue;
			
			$pieces = array ();
			
			$tAux = explode ('|', $match [1]);
			
			if (sizeof ($tAux) > 1)
				foreach ($tAux as $trash => $inLang)
				{
					$aux = explode (':', $inLang);
					
					if (sizeof ($aux) > 1)
						$pieces [trim ($aux [0])] = trim ($aux [1]);
					else
						$pieces ['en_US'] = trim ($aux [0]);
				}
			else
				$pieces ['pt_BR'] = trim ($match [1]);
			
			foreach ($langs as $lang => $fLang)
			{
				if (array_key_exists ($lang, $pieces))
					continue;
				
				$gt = new GoogleTranslateWrapper ();
		
				$gt->setCredentials ('AIzaSyBPbS5T59fw1aup7_H8AxfAawEeW9PjjCE', '127.0.0.1');
				
				switch ($lang)
				{
					case 'pt_BR':
						continue;
					
					case 'en_US':
						$pieces ['en_US'] = $gt->translate ($pieces ['pt_BR'], 'en', 'pt-br');
						break;
					
					case 'es_ES':
						$pieces ['es_ES'] = $gt->translate ($pieces ['pt_BR'], 'es', 'pt-br');
						break;
				}
			}
			
			$line = str_replace ($att .'="'. $match [1] .'"', $att .'="'. $pieces ['en_US'] .' | pt_BR: '. $pieces ['pt_BR'] .' | es_ES: '. $pieces ['es_ES'] .'"', $line);
		}
		
		$buffer .= $line;
	}
	
	file_put_contents ($destination, $buffer);
}
?>
</pre>