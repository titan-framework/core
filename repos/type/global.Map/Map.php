<?php

class Map extends Type
{
	private $latitude = 0.0;
	private $longitude = 0.0;

	public function __construct ($table, $field)
	{
		$this->setSortable (FALSE);
		
		parent::__construct ($table, $field);

		self::$useMap = TRUE;

		$this->setBind (TRUE);

		$this->setBindType (PDO::PARAM_STR);
	}

	public function setValue ($value)
	{
		if (is_string ($value))
			$value = explode (',', $value);

		if (!is_array ($value) || sizeof ($value) != 2)
			return;

		$this->setLatitude ($value [0]);
		$this->setLongitude ($value [1]);
	}

	public function getValue ()
	{
		return array ($this->latitude, $this->longitude);
	}

	public function setLatitude ($latitude)
	{
		$this->latitude = (float) $latitude;
	}

	public function getLatitude ()
	{
		return $this->latitude;
	}

	public function setLongitude ($longitude)
	{
		$this->longitude = (float) $longitude;
	}

	public function getLongitude ()
	{
		return $this->longitude;
	}

	public function isEmpty ()
	{
		return !$this->latitude || !$this->longitude;
	}

	public function __toString ()
	{
		return self::dec2dms ($this->getLatitude(), $this->getLongitude());
	}

	public static function dec2dms ($latitude, $longitude)
	{
		$latitudeDirection = $latitude < 0 ? 'S': 'N';
		$longitudeDirection = $longitude < 0 ? 'W': 'E';

		$aux = abs ($latitude);
		$latitudeDegrees = floor ($aux);
		$aux = ($aux - $latitudeDegrees) * 60;
		$latitudeMinutes = floor ($aux);
		$latitudeSeconds = number_format (($aux - $latitudeMinutes) * 60, 2, '.', '');

		$aux = abs ($longitude);
		$longitudeDegrees = floor ($aux);
		$aux = ($aux - $longitudeDegrees) * 60;
		$longitudeMinutes = floor ($aux);
		$longitudeSeconds = number_format (($aux - $longitudeMinutes) * 60, 2, '.', '');

		return sprintf('%s°%s\'%s"%s, %s°%s\'%s"%s',
			$latitudeDegrees,
			$latitudeMinutes,
			$latitudeSeconds,
			$latitudeDirection,
			$longitudeDegrees,
			$longitudeMinutes,
			$longitudeSeconds,
			$longitudeDirection
		);
	}
}
