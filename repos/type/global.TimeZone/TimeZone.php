<?php
class TimeZone extends Phrase
{
	public function __construct ($table, $field)
	{
		$this->table = '_user';
		
		$this->name = '_timezone';
		
		if (isset ($_COOKIE['_TITAN_TIMEZONE_']) && trim ($_COOKIE['_TITAN_TIMEZONE_']) != '')
			$this->value = $_COOKIE['_TITAN_TIMEZONE_'];
		
		parent::__construct ($table, $field);
	}
}
?>