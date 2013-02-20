<?
class XmlMaker
{
	private $xml;
	
	private $indent;
	
	private $stack = array();

	private $breakLine = TRUE;
	
	public function __construct ($indent = "  ", $breakLine = TRUE)
	{
		$this->indent = $indent;

		$this->breakLine = $breakLine;
		
		$this->xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	}
	
	private function indent ()
	{
		for ($i = 0, $j = count($this->stack); $i < $j; $i++)
			$this->xml .= $this->indent;
	}
	
	public function push ($element, $attributes = array())
	{
		$this->xml .= '<'. $element;
		
		foreach ($attributes as $key => $value)
			if (!is_numeric ($key))
				$this->xml .= ($this->breakLine ? "\n" . $this->indent() : '') .' '. $key .'="'. $value .'"';
		
		$this->xml .= ">\n";
		
		$this->stack[] = $element;
	}
	
	public function element ($element, $content, $attributes = array())
	{
		$this->xml .= '<'. $element;
		
		foreach ($attributes as $key => $value)
			if (!is_numeric ($key))
				$this->xml .= ($this->breakLine ? "\n" . $this->indent() : '') .' '. $key .'="'. $value .'"';
		
		$this->xml .= '>'. htmlentities ($content) .'</'. $element .'>' . "\n";
	}
	
	public function emptyElement ($element, $attributes = array())
	{
		$this->xml .= ($this->breakLine ? "\n" : '') . $this->indent() .'<'. $element;
		
		foreach ($attributes as $key => $value)
			if (!is_numeric ($key))
				$this->xml .= ($this->breakLine ? "\n" . $this->indent() : '') .' '. $key .'="'. $value .'"';
		
		$this->xml .= " />\n";
	}
	
	public function collapseElement ($element, $attributes = array())
	{
		$this->xml .= $this->indent()."<".$element;
		
		foreach ($attributes as $key => $value)
			if (!is_numeric ($key))
				$this->xml .= ($this->breakLine ? "\n" . $this->indent() : '') .' '. $key .'="'. $value .'"';
		
		$this->xml .= " />\n";
	}
	
	public function pop ()
	{
		$element = array_pop ($this->stack);
		
		$this->xml .= "</". $element .">\n";
	}
	
	public function getXml ()
	{
		return $this->xml;
	}
}
?>