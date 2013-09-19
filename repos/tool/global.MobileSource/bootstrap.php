<pre>
<?
set_time_limit (0);
ini_set ('memory_limit', '-1');

if (!isset ($_GET ['section']) || !Business::singleton ()->sectionExists ($_GET ['section']) || !isset ($_GET ['model']) || !isset ($_GET ['package']))
	die ('Parameter [section] or [model] is missing!');

$section = Business::singleton ()->getSection ($_GET ['section']);

if (is_null ($section))
	die ('Impossible to load section ['. $_GET ['section'] .']!');

if (!file_exists ($section->getPath () .'api.xml'))
	die ('Impossible to open ['. $section->getPath () .'api.xml' .']!');

$model = $_GET ['model'];

$modelUnderScore = strtolower (preg_replace ('/([a-z])([A-Z])/', '$1_$2', $model));

$object = lcfirst ($model);

$app = $_GET ['package'];

require dirname (__FILE__) . DIRECTORY_SEPARATOR .'function.php';

$action = $section->getAction (Action::TAPI);

Business::singleton ()->setCurrent ($section, $action);

foreach (Instance::singleton ()->getTypes () as $type => $path)
	require_once $path . $type .'.php';

$view = new View ('api.xml');

if (!isset ($_GET ['table']))
	$table = array_pop (explode ('.', $view->getTable ()));
else
	$table = $_GET ['table'];

$fields = array ();

$fields [$view->getPrimary ()] = (object) array ('json' => $view->getPrimary (), 'class' => translateFieldName ($view->getPrimary ()), 'type' => 'Long', 'db' => 'INTEGER PRIMARY KEY');

while ($field = $view->getField ())
	$fields [$field->getApiColumn ()] = (object) array (
		'json' => $field->getApiColumn (), 
		'class' => translateFieldName ($field->getApiColumn ()), 
		'type' => translateType ($field),
		'db' => translateDatabase ($field),
		'label' => $field->getLabel ()
	);

$base = Instance::singleton ()->getCachePath () .'mobile'. DIRECTORY_SEPARATOR .'android' . DIRECTORY_SEPARATOR;

$path = $base .'src'. DIRECTORY_SEPARATOR . implode (DIRECTORY_SEPARATOR, explode ('.', $app)) . DIRECTORY_SEPARATOR;

$packages = array ( 'contract' => 'Contract',
					'model' => '',
					'converter' => 'Converter',
					'dao' => 'DAO');

foreach ($packages as $pack => $sufix)
{
	if (!file_exists ($path . $pack) && !@mkdir ($path . $pack, 0777, TRUE))
		die ('Impossible to create folder ['. $path . $pack .'].');
	
	$output = require dirname (__FILE__) . DIRECTORY_SEPARATOR .'android'. DIRECTORY_SEPARATOR . $pack .'.php';
	
	$file = $path . $pack . DIRECTORY_SEPARATOR . $model . $sufix .'.java';
	
	if (file_put_contents ($file, $output))
		echo "SUCCESS > File generated! [". $file ."] \n";
	else
		echo "FAIL > Impossible to generate code! [". $file ."] \n";
}

$output = require dirname (__FILE__) . DIRECTORY_SEPARATOR .'android'. DIRECTORY_SEPARATOR .'view.php';

$file = $path . $model .'ViewActivity.java';

if (file_put_contents ($file, $output))
	echo "SUCCESS > File generated! [". $file ."] \n";
else
	echo "FAIL > Impossible to generate code! [". $file ."] \n";

$path = $base .'res'. DIRECTORY_SEPARATOR .'layout'. DIRECTORY_SEPARATOR;

if (!file_exists ($path) && !@mkdir ($path, 0777, TRUE))
	die ('Impossible to create folder ['. $path .'].');

$output = require dirname (__FILE__) . DIRECTORY_SEPARATOR .'android'. DIRECTORY_SEPARATOR .'layout.php';

$file = $path . $modelUnderScore .'_view.xml';

if (file_put_contents ($file, $output))
	echo "SUCCESS > File generated! [". $file ."] \n";
else
	echo "FAIL > Impossible to generate code! [". $file ."] \n";
?>
</pre>