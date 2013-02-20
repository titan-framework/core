<?
$array = $field->getMapping ();
					
if (!array_key_exists ($field->getValue (), $array))
	return '';

return $array [$field->getValue ()];
?>