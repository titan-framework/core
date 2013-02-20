<?
class Enum extends String
{
	protected $enum = array ();
	
	protected $forGraph = TRUE;
	
	public function __construct ($table, $field)
	{		
		parent::__construct ($table, $field);
		
		$itens = NULL;
		
		if (array_key_exists ('item', $field))
			$itens = $field ['item'];
		elseif (array_key_exists ('enum-mapping', $field))
			$itens = $field ['enum-mapping'];
		
		if (!is_null ($itens))
			foreach ($itens as $trash => $item)
			{
				if (array_key_exists ('value', $item))
					$label = $item ['value'];
				elseif (array_key_exists ('column', $item))
					$label = $item ['column'];
				else
					continue;
				
				if (array_key_exists (0, $item) && trim ($item [0]) != '')
					$label = $item [0];
				
				if (array_key_exists ('label', $item))
					$label = translate ($item ['label']);
				
				if (array_key_exists ('value', $item))
					$this->enum [$item ['value']] = $label;
				else
					$this->enum [$item ['column']] = $label;
			}
	}
	
	public function getMapping ()
	{
		return $this->enum;
	}
}
?>