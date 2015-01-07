<?php

class CloudFile extends File
{	
	public function __construct ($table, $field)
	{
		if (!Database::tableExists ('_cloud'))
			throw new Exception ('The mandatory table [_cloud] do not exists! Its necessary to use type CloudFile.');
		
		parent::__construct ($table, $field);
	}
	
	public static function formatFileSizeForHuman ($bytes, $decimals = 0)
	{
		$size = array ('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
		
		$factor = floor ((strlen ($bytes) - 1) / 3);
		
		return sprintf ("%.{$decimals}f", $bytes / pow (1024, $factor)) .' '. @$size [$factor];
	}
}