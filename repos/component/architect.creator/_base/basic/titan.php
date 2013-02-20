<?
$file = 'configure/titan.xml';

if (!file_exists ($file))
	die ('Arquivo de configuração <b>[configure/titan.xml]</b> não encontrado.');

$xml = file_get_contents ($file);

$regTag = '/<core-path>(.*?)<\/core-path>/s';

preg_match_all ($regTag, $xml, $match);

if (!isset ($match [1][0]))
{
	$regTag = '/core-path="(.*?)"/s';

	preg_match_all ($regTag, $xml, $match);
	
	if (!isset ($match [1][0]))
		die ('A diretiva <b>&lt;core-path&gt;&lt;/core-path&gt;</b> deve estar devidamente setada no arquivo de configuração <b>[configure/titan.xml]</b>.');
}

$corePath = $match [1][0];

if (!file_exists ($corePath .'switch.php'))
	die ('O core do Titan não foi localizado no caminho especificado em <b>&lt;core-path&gt;'. $corePath .'&lt;/core-path&gt;</b> no arquivo de configuração <b>[configure/titan.xml]</b>.');

require $corePath .'switch.php';
?>