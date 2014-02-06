<?
if (empty ($value))
	return (float) 0.0;

return (float) Float::validate ($value);
?>