<?php

$map = array ('Collection' => array ('String', 'TEXT'),
			  'Coordinate' =>  array ('String', 'TEXT'),
			  'Date' =>  array ('Date', 'INTEGER'),
			  'Float' =>  array ('Double', 'REAL'),
			  'Integer' =>  array ('Long', 'INTEGER'),
			  'Select' =>  array ('String', 'TEXT'),
			  'String' =>  array ('String', 'TEXT'),
			  'Time' =>  array ('Date', 'INTEGER'),
			  'Boolean' => array ('Boolean', 'INTEGER'));

function translateType ($field)
{
	global $map;
	
	$type = get_class ($field);
	
	$next = get_parent_class ($type);
	
	while (!array_key_exists ($type, $map) && $next != 'Type' && $next !== FALSE)
	{
		$type = $next;
		
		$next = get_parent_class ($next);
	}
	
	if (array_key_exists ($type, $map))
		return $map [$type][0];
	
	return 'String';
}

function translateFieldName ($name)
{
	$name = strtolower ($name);
	
	$array = explode ('_', $name);
	
	$first = $array [0];
	
	array_walk ($array, function (&$item, $key) { $item = ucwords ($item); });
	
	$array [0] = $first;
	
	return implode ('', $array);
}

function translateDatabase ($field)
{
	global $map;
	
	$type = get_class ($field);
	
	$next = get_parent_class ($type);
	
	while ($next != 'Type' && $next !== FALSE)
	{
		$type = $next;
		
		$next = get_parent_class ($next);
	}
	
	if (array_key_exists ($type, $map))
		return $map [$type][1];
	
	return 'TEXT';
}

function generate ($appication, $package, $section, $model, $xml, $table, $assets)
{
	$section = Business::singleton ()->getSection ($section);
	
	if (is_null ($section))
		die ('Impossible to load section ['. $section .']!');
	
	if (!file_exists ($section->getPath () . $xml) && !is_dir ($section->getPath () . $xml))
		die ('Impossible to open ['. $section->getPath () . $xml .']!');
	
	$useAssets = strtoupper (trim ($assets)) == 'TRUE' ? TRUE : FALSE;
	
	$modelUnderScore = strtolower (preg_replace ('/([a-z])([A-Z])/', '$1_$2', $model));
	
	$object = lcfirst ($model);
	
	$app = $package;
	
	if (trim ($appication) == '')
		$appName = ucwords (array_pop (explode ('.', $app)));
	else
		$appName = $appication;
	
	$action = $section->getAction (Action::TAPI);
	
	Business::singleton ()->setCurrent ($section, $action);
	
	foreach (Instance::singleton ()->getTypes () as $type => $path)
		require_once $path . $type .'.php';
	
	$view = new ApiList ($xml);
	
	if (trim ($table) == '')
		$table = array_pop (explode ('.', $view->getTable ()));
	
	$useCode = $view->useCode ();
	
	$primary = $view->getPrimary ();
	
	$code = $view->getCodeColumn ();
	
	$update = $view->getField ('_API_UPDATE_UNIX_TIMESTAMP_')->getApiColumn ();
	
	$fields = array ();
	
	if ($useCode)
		$fields [$primary] = (object) array ('json' => $code, 'class' => translateFieldName ($code), 'type' => 'String', 'db' => 'TEXT PRIMARY KEY', 'label' => 'Código', 'object' => new stdClass ());
	else
		$fields [$primary] = (object) array ('json' => $primary, 'class' => translateFieldName ($primary), 'type' => 'Long', 'db' => 'INTEGER PRIMARY KEY', 'label' => 'Identificador', 'object' => new stdClass ());
	
	while ($field = $view->getField ())
		$fields [$field->getApiColumn ()] = (object) array (
			'json' => $field->getApiColumn (), 
			'class' => translateFieldName ($field->getApiColumn ()), 
			'type' => translateType ($field),
			'db' => translateDatabase ($field),
			'label' => $field->getLabel (),
			'object' => $field
		);
	
	$base = Instance::singleton ()->getCachePath () .'mobile'. DIRECTORY_SEPARATOR . $appName . DIRECTORY_SEPARATOR .'android' . DIRECTORY_SEPARATOR;
	
	$path = $base .'src'. DIRECTORY_SEPARATOR . implode (DIRECTORY_SEPARATOR, explode ('.', $app)) . DIRECTORY_SEPARATOR;
	
	$packages = array ( 'contract' => 'Contract',
						'model' => '',
						'converter' => 'Converter',
						'dao' => 'DAO',
						'task' => 'Task',
						'ws' => 'WebService',
						'adapter' => 'Adapter');
	
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
	
	$output = require dirname (__FILE__) . DIRECTORY_SEPARATOR .'android'. DIRECTORY_SEPARATOR .'layoutView.php';
	
	$file = $path . $modelUnderScore .'_view.xml';
	
	if (file_put_contents ($file, $output))
		echo "SUCCESS > File generated! [". $file ."] \n";
	else
		echo "FAIL > Impossible to generate code! [". $file ."] \n";
	
	$output = require dirname (__FILE__) . DIRECTORY_SEPARATOR .'android'. DIRECTORY_SEPARATOR .'layoutRow.php';
	
	$file = $path . $modelUnderScore .'_row.xml';
	
	if (file_put_contents ($file, $output))
		echo "SUCCESS > File generated! [". $file ."] \n";
	else
		echo "FAIL > Impossible to generate code! [". $file ."] \n";
	
	if ($useAssets)
	{
		$path = $base .'assets'. DIRECTORY_SEPARATOR;
		
		if (!file_exists ($path) && !@mkdir ($path, 0777, TRUE))
			die ('Impossible to create folder ['. $path .'].');
		
		$output = require dirname (__FILE__) . DIRECTORY_SEPARATOR .'android'. DIRECTORY_SEPARATOR .'assets.php';
		
		$file = $path . $table .'.sql';
		
		if (file_put_contents ($file, $output))
			echo "SUCCESS > File generated! [". $file ."] \n";
		else
			echo "FAIL > Impossible to generate code! [". $file ."] \n";
		
		require dirname (__FILE__) . DIRECTORY_SEPARATOR .'android'. DIRECTORY_SEPARATOR .'db.php';
	}
}