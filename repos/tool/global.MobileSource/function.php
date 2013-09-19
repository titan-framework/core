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