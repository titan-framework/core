<?
$value = Localization::negotiatedLanguage ($value);

return trim ($value) == '' ? Localization::singleton ()->getLanguage () : $value;
?>