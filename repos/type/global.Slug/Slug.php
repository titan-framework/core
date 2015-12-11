<?
class Slug extends Phrase
{
	protected $base = '';
	
	public function __construct ($table, $field)
	{
		parent::__construct ($table, $field);
		
		if (array_key_exists ('base', $field))
			$this->setBase ($field ['base']);
	}
	
	public function getBase ()
	{
		return $this->base;
	}
	
	public function setBase ($base)
	{
		$this->base = (string) trim ($base);
	}
	
	public static function format ($string)
	{
		$string = strtolower ($string);
		
		$ascii ['a'] = range (224, 230); 
		$ascii ['e'] = range (232, 235); 
		$ascii ['i'] = range (236, 239); 
		$ascii ['o'] = array_merge (range (242, 246), array (240, 248)); 
		$ascii ['u'] = range (249, 252);
		
		$ascii ['b'] = array (223); 
		$ascii ['c'] = array (231); 
		$ascii ['d'] = array (208); 
		$ascii ['n'] = array (241); 
		$ascii ['y'] = array (253, 255); 
		
		foreach ($ascii as $key => $item)
		{
			$accents = '';
			
			foreach ($item as $trash => $code)
				$accents .= chr ($code);
			
			$change [$key] = '/['.$accents.']/i';
		}
		
		$string = preg_replace (array_values ($change), array_keys ($change), $string);
		
		$string = preg_replace ('/ /i', '-', $string);
		$string = preg_replace ('/-{2,}/i', '-', $string);
		$string = preg_replace ('/[^a-z0-9-]/i', '', $string); 
		$string = trim ($string, '-');
		
		return $string;
	}
}
?>