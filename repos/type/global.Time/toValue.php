<?
if (!is_array ($field->getValue ()) || !sizeof ($field->getValue ()) || array_sum ($field->getValue ()) < 0)
	return "'00:00:00'";

return "'". implode (':', $field->getValue ()) ."'";
?>