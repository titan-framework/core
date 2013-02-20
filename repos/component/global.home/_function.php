<?
function update ($make = FALSE)
{
	$return = FALSE;
	
	ob_start ();
	
	system ('svn --version', $return);
	
	ob_end_clean ();

	if ($return)
		throw new Exception ('O SVN Client aparentemente não está instalado no sistema operacional. Mais informações sobre como proceder para instalar em <a href="http://subversion.tigris.org/" target="_blank">http://subversion.tigris.org/</a>.');
	
	$updateFile = 'configure/update.xml';
	
	if (!file_exists ($updateFile))
		throw new Exception ('Arquivos XML ['. $updateFile .'] não encontrado!');
	
	$serverFile = 'update/update-server.xml';
	
	if (!file_exists ($serverFile))
		throw new Exception ('Arquivos XML ['. $serverFile .'] não encontrado!');
	
	ob_start ();
	
	system ('svn up '. $serverFile .' -q --non-interactive', $return);
	
	ob_end_clean ();
	
	if ($return)
		throw new Exception ('Impossível atualizar o arquivo ['. $serverFile .'].');
	
	$xml = new Xml ($updateFile);
	
	$arrayUpdate = $xml->getArray ();
	
	$xml = new Xml ($serverFile);
	
	$arrayServer = $xml->getArray ();
	
	if (!isset ($arrayUpdate ['update-mapping'][0]))
		throw new Exception ('A tag &lt;update-mapping&gt;&lt;/update-mapping&gt; não foi encontrada no XML ['. $updateFile .']!');
	
	if (!isset ($arrayServer ['version'][0]))
		throw new Exception ('A tag &lt;version&gt;&lt;/version&gt; não foi encontrada no XML ['. $serverFile .']!');
	
	$arrayServer = $arrayServer ['version'][0];
	
	$arrayUpdate = $arrayUpdate ['update-mapping'][0];
	
	if (!array_key_exists ('module', $arrayUpdate))
		return array ();
	
	$instance = Instance::singleton ();
	
	$tag = array ('[core-path]', '[repos-path]');
	$rep = array ($instance->getCorePath (), $instance->getReposPath ());
	
	$actual = array ();
	$arrayFinal = array ();
	
	foreach ($arrayUpdate ['module'] as $trash => $module)
	{
		if (!array_key_exists ('name', $module) || !array_key_exists ('path', $module))
			continue;
		
		$name = $module ['name'];
		$path = $module ['path'];
		
		$path = str_replace ($tag, $rep, $path);
		
		if ($name == 'INSTANCE')
			$forUp [$name] = array ('./', 'update/update-client.xml');
		else
			$forUp [$name] = array ($path, $path .'update.xml');
		
		$xmlPath = $forUp [$name][1];
		
		$actual [$name] = 0;
		$arrayFinal [$name] = array (	'name' 		=> $name,
										'version' 	=> 0,
										'changelog'	=> '');
		
		if (!file_exists ($xmlPath))
			continue;
		
		$xml = new Xml ($xmlPath);
		
		$array = $xml->getArray ();
		
		if (!isset ($array ['version'][0]['module']))
			continue;
		
		$array = $array ['version'][0]['module'];
		
		foreach ($array as $trash => $aux)
			if (isset ($aux ['name']) && $aux ['name'] == $name)
			{
				$actual [$name] = (int) $aux ['version'];
				$arrayFinal [$name] = $aux;
			}	
	}
	
	if (!array_key_exists ('module', $arrayServer) || !is_array ($arrayServer ['module']))
		throw new Exception ('As tags &lt;module /&gt; não foram encontradas no XML ['. $serverFile .']!');
	
	$update = array ();
	$changelog = array ();
	foreach ($arrayServer ['module'] as $trash => $module)
	{
		if (!array_key_exists ('name', $module) || !array_key_exists ('version', $module))
			continue;
		
		if (array_key_exists ('changelog', $module))
			$changelog [$module ['name']] = $module ['changelog'];
		else
			$changelog [$module ['name']] = '';
		
		if (array_key_exists ($module ['name'], $actual) && $actual [$module ['name']] < (int) $module ['version'])
			$update [$module ['name']] = (int) $module ['version'];
	}
	
	if (!$make)
		return $update;
	
	$updated = array ();
	foreach ($forUp as $key => $paths)
		if (array_key_exists ($key, $update))
		{
			if (!is_writable ($paths [0]))
			{
				$updated [$key] = array (FALSE, 'O <b>Titan Lite</b> n&atilde;o possui permiss&otilde;es de escrita no diret&oacute;rio ['. $paths [0] .'].');
				continue;
			}
			
			ob_start ();
	
			system ('svn up '. $paths [0] .' -r '. $update [$key] .' -q --non-interactive', $return);
			
			ob_end_clean ();
			
			if ($return)
			{
				$updated [$key] = array (FALSE, 'Ocorreu um erro inesperado durante a atualiza&ccedil;&otilde;o do diret&oacute;rio ['. $paths [0] .'].');
				continue;
			}
			
			$updated [$key] = array (TRUE, 'O diret&oacute;rio ['. $paths [0] .'] foi atualizado com sucesso.'. (trim ($changelog [$key]) != '' ? ' <a href="'. $changelog [$key] .'" target="_blank">Ver <i>changelog</i> &raquo;</a>' : ''));
			
			$arrayFinal [$key]['version'] = $update [$key];
			$arrayFinal [$key]['changelog'] = $changelog [$key];
		}
	
	$xml = new XmlMaker ();
	
	$xml->push ('version');
	
	foreach ($arrayFinal as $trash => $module)
		$xml->collapseElement ('module', $module);
	
	$xml->pop ();
	
	$newFile = $xml->getXml ();
	
	foreach ($forUp as $key => $paths)
		file_put_contents ($paths [1], $newFile);
	
	return $updated;
}
?>