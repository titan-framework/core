<?
$timezone = preg_replace ('/[^0-9]/', '', $value) * 36;

return timezone_name_from_abbr (NULL, $timezone, TRUE);
?>