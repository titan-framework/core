<?php
class Coordinate extends Type
{
	protected $value = array ("0 0' 0''N", "0 0' 0''E", 0);

	public function __construct ($table, $field)
	{
		$this->setSortable (FALSE);
		
		parent::__construct ($table, $field);

		$this->setBind (TRUE);

		$this->setBindType (PDO::PARAM_STR);

		if (array_key_exists ('value', $field))
			$this->setValue (explode (',', $field ['value']));
	}

	public function setValue ($value)
	{
		$this->value = is_array ($value) ? $value : explode (',', $value);
	}

	public function __toString ()
	{
		return implode (', ', $this->value);
	}

	public static function toKml ($coord)
	{
		$coord = urldecode ($coord);

		$array = explode (' ', $coord);

		if (sizeof ($array) == 1)
			return number_format ($coord, 12, '.', '');

		if (sizeof ($array) < 3)
			return number_format ($array [0], 12, '.', '');

		$degrees = Double::validate ($array [0]);

		$minutes = Double::validate ($array [1]);

		$seconds = Double::validate ($array [2]);

		$result = $degrees + ($minutes / 60) + ($seconds / 3600);

		if (sizeof ($array) > 3)
			$orient = $array [3];
		else
			$orient = substr ($array [2], -1);

		if ($orient == 'S' || $orient == 'W')
			return number_format (-$result, 12, '.', '');

		return number_format ($result, 12, '.', '');
	}
}
?>
