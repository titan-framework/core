<?php

class Slider extends Amount
{
	private $maximum = 100;
	private $minimum = 0;
	private $step = 1;
	
	public function __construct ($table, $field)
	{
		parent::__construct ($table, $field);
		
		if (array_key_exists ('maximum', $field))
			$this->setMaximum ($field ['maximum']);
		
		if (array_key_exists ('minimum', $field))
			$this->setMinimum ($field ['minimum']);
		
		if (array_key_exists ('step', $field))
			$this->setStep ($field ['step']);
		
		$this->value = $this->minimum;
	}
	
	public function setMaximum ($max)
	{
		$this->maximum = (int) $max;
	}
	
	public function getMaximum ()
	{
		return $this->maximum;
	}
	
	public function setMinimum ($min)
	{
		$this->minimum = (int) $min;
	}
	
	public function getMinimum ()
	{
		return $this->minimum;
	}
	
	public function setStep ($step)
	{
		$this->step = (int) $step;
	}
	
	public function getStep ()
	{
		return $this->step;
	}
}