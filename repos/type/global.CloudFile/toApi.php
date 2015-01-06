<?php

if ($field->isEmpty ())
	return NULL;

$db = Database::singleton ();

$sql = "SELECT
			_code AS code,
			_mimetype AS mime_type,
			EXTRACT (EPOCH FROM _create_date) AS creation_date,
			EXTRACT (EPOCH FROM _create_date) AS last_change
		FROM _file WHERE _id = :id";

$sth = $db->prepare ($sql);

$sth->bindParam (':id', $field->getValue (), PDO::PARAM_INT);

$file = $obj->fetch (PDO::FETCH_ASSOC);

return json_encode ($file);