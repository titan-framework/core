<?
ob_start ();

$oFCKeditor = new FCKeditor ($fieldName, $fieldId);

$oFCKeditor->BasePath = Instance::singleton ()->getCorePath () .'extra/fckEditor/';

$oFCKeditor->ToolbarSet = 'TitanToolbar';

$oFCKeditor->Value = $field->getValue ();

$oFCKeditor->Create ();

return ob_get_clean ();
?>