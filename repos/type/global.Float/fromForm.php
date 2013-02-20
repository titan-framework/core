<?
if (empty ($value))
	return (float) 0.0;
	
$value = str_replace ('.', '', $value);
$value = str_replace (',', '.', $value);

return (float) Float::validate ($value);
?>