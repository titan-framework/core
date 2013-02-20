<?
if (!isset ($_SESSION['UNIX_NAME']) || !isset ($_SESSION['DBH_'. $_SESSION['UNIX_NAME']]))
	throw new Exception ('Houve perda de variáveis.');

$itemId = $_SESSION['UNIX_NAME'];

if (isset ($_SESSION['SECTIONS_'. $itemId]))
	$array = $_SESSION['SECTIONS_'. $itemId];
else
{
	$file = 'instance/'. $itemId .'/configure/business.xml';
	
	if (!file_exists ($file))
		throw new Exception ('Arquivos da instância não encontrados! ['. $file .']');
	
	$xml = new Xml ($file);
			
	$array = $xml->getArray ();
	
	if (!isset ($array ['section-mapping'][0]['section']))
		throw new Exception ('A tag &lt;section-mapping&gt;&lt;/section-mapping&gt; não existe no arquivo ['. $file .'].');
	
	$aux = $array ['section-mapping'][0]['section'];
	
	$array = array ();
	
	foreach ($aux as $trash => $value)
		$array [$value ['name']] = $value;
	
	$_SESSION['SECTIONS_'. $itemId] = $array;
}

if (isset ($_SESSION['PACKS_'. $itemId]))
	$packs = $_SESSION['PACKS_'. $itemId];
else
{
	$packs = array ();
	
	$repos = Instance::singleton ()->getReposPath ();
	
	$count = 1;
	
	do
	{
		$file = $repos .'package/package.xml';
	
		if (!file_exists ($file))
			continue;
		
		$xml = new Xml ($file);
		
		$aux = $xml->getArray ();
		
		if (!isset ($aux ['package-mapping'][0]['package']))
			throw new Exception ('A tag &lt;package-mapping&gt;&lt;/package-mapping&gt; não existe no arquivo ['. $file .'].');
		
		$aux = $aux ['package-mapping'][0]['package'];
		
		foreach ($aux as $trash => $value)
		{
			if (!array_key_exists ('name', $value) || !array_key_exists ('component', $value) || array_key_exists ($value ['name'], $packs))
				continue;
			
			$pathPackage = $repos .'package/'. $value ['name'] .'/';
			
			$pathComponent = $repos .'component/'. $value ['component'] .'/';
			
			if (!file_exists ($pathPackage) || !is_dir ($pathPackage) || !file_exists ($pathComponent) || !is_dir ($pathComponent))
				continue;
			
			$value ['pPackage'] = $pathPackage;
			
			if ($count > 1)
				$value ['pComponent'] = $pathComponent;
			else
				$value ['pComponent'] = $value ['component'];
			
			$packs [$value ['name']] = $value;
		}
	}
	while ($repos = Business::singleton ()->getSection (Section::TCURRENT)->getDirective ('_LOCAL_REPOS_'. $count++ .'_'));
	
	$_SESSION['PACKS_'. $itemId] = $packs;
}

$menu =& Menu::singleton ();
$menu->addJavaScript ('Inserir Seção', 'create.png', 'showCreateSection ();');
$menu->addJavaScript ('Preview do Sistema', 'view.png', 'openPopup (\'instance/'. $itemId .'/titan.php?target=login\', \'popup_'. $itemId .'\')');
$menu->addJavaScript ('Salvar Ordenação', 'save.png', 'saveSort ()');
?>