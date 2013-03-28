<?
class Xml
{
	private $array = array ();
	
	public function __construct ($file)
	{
		if (!file_exists ($file))
			throw new Exception ('Arquivo XML ['. $file .'] não encontrado!');
		
		$this->array = self::xmlParser (file_get_contents ($file));
	}
	
	private static function xmlParser ($text) 
	{
		$regTag = '/<([a-zA-Z0-9-_]*)(\s.*?)?((>(.*?)<\/\\1>)|(\/>))/s';
		$regDirective = '/([a-zA-Z0-9-_]*)="(.*?)"/s';
		$regComentary = '/<!--(([^-])|(-[^-])|(--[^>]))*/s';
		
		$text = preg_replace ($regComentary, '', $text);
		
		preg_match_all ($regTag, $text, $matchTag);
		
		foreach ($matchTag [1] as $key => $value)
		{
			if (preg_match ($regTag, $matchTag [5][$key]))
			{
				if(preg_match_all ($regDirective, $matchTag [2][$key], $matchDirective) != 0)
				{
					array_shift ($matchDirective);
					
					$aux = array();
									
					foreach($matchDirective [0] as $keyAux => $valueAux)
						$aux [$valueAux] = $matchDirective [1][$keyAux];
					
					$array [$value][] = array_merge ($aux, self::xmlParser ($matchTag [5][$key]));
				}
				else
					$array [$value][] = self::xmlParser ($matchTag [5][$key]);
			}
			else
			{
				if(preg_match_all ($regDirective, $matchTag [2][$key], $matchDirective) != 0)
				{
					array_shift ($matchDirective);
					
					$aux = array();
									
					foreach ($matchDirective [0] as $keyAux => $valueAux)
						$aux [$valueAux] = $matchDirective [1][$keyAux];
						
					$matchTag [5][$key] = array_merge ($aux, array ($matchTag [5][$key]));
				}
				
				if (isset ($array [$value]))
					if (is_array($array [$value])) 
						$array [$value][] = $matchTag [5][$key];
					else 
						$array [$value] = array ($array [$value], $matchTag [5][$key]);
				else 
					if(is_array($matchTag [5][$key])) 
						$array [$value][] = $matchTag [5][$key];
					else
						$array [$value] = $matchTag [5][$key];
			}
		}
		
		if (strnatcmp (phpversion (), '5.3.4') >= 0)
			$table = get_html_translation_table (HTML_ENTITIES, ENT_COMPAT, 'UTF-8');
		else
		{
			$table = get_html_translation_table (HTML_ENTITIES);
			
			array_walk ($table, 'toUtf8');
		} 
		
		$table = array_flip ($table);
		
		return self::decode ($array, $table);
	}
	
	public function getArray ()
	{
		return $this->array;
	}
	
	public function getTag ($tag, $subtag = NULL, $key = 0, $array = NULL)
	{
		if($array == NULL)
			$array = $this->getArray();
			
		if( $subtag !== NULL && is_array ($array[$tag][$key]) && array_key_exists ($subtag, $array[$tag][$key]) )
			return $array[$tag][$key][$subtag];		
		
		if( $subtag === NULL && is_array ($array) && array_key_exists ($tag, $array) )
			return $array[$tag];
		
		if ( is_array (reset($array)) )
			return $this->getTag ($tag, $subtag, $key, reset($array));
		
		return FALSE;
	}
	
	public static function decode ($array, $table)
	{
		foreach ($array as $key => $cell)
			if (is_array ($cell))
				$array [$key] = self::decode ($cell, $table);
			else
				$array [$key] = strtr ($cell, $table);
		
		return $array;
	}
}
?>