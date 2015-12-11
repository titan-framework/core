<?
class Color extends Phrase
{
	public function __construct ($table, $field)
	{
		parent::__construct ($table, $field);
		
		$this->maxLength = 6;
	}
	
	public static function validate ($str)
	{
		return preg_replace ('/[^0-9A-F]/i', '', $str);
	}
	
	public static function contrast ($color)
	{
		if (substr ($color, 0, 1) == '#')
			$color = substr ($color, 1);
		
		if (strlen ($color) == 3)
		{
			$rh = substr ($color, 0, 1) . substr ($color, 0, 1);
			$gh = substr ($color, 1, 1) . substr ($color, 1, 1);
			$bh = substr ($color, 2, 1) . substr ($color, 2, 1);
		}
		else
		{
			$rh = substr ($color, 0, 2);
			$gh = substr ($color, 2, 2);
			$bh = substr ($color, 4, 2);
		}
		
		$r = (int) hexdec ($rh);
		$g = (int) hexdec ($gh);
		$b = (int) hexdec ($bh);
		
		return ($r * 0.3 + $g * 0.59 + $b * 0.11) > 128 ? '000' : 'FFF';
	}
}
?>