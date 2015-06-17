<?php

if (Api::getHttpRequestMethod () != Api::GET)
	throw new ApiException (__ ('Invalid URI request method!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::METHOD_NOT_ALLOWED);

if (!isset ($_uri [2]) || trim ($_uri [2]) == '')
	throw new ApiException (__ ('Invalid URI! The CODE of note is required!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

$code = trim ($_uri [2]);

$db = Database::singleton ();

$sth = $db->prepare ("SELECT
						_title AS title,
						_note AS note,
						_longitude AS longitude,
						_latitude AS latitude,
						_altitude AS altitude,
						EXTRACT (EPOCH FROM _devise)::integer AS devise,
						EXTRACT (EPOCH FROM _change)::integer AS change,
						_deleted AS deleted
					  FROM _note WHERE _code = :code");

$sth->bindParam (':code', $code, PDO::PARAM_STR);

$sth->execute ();

$obj = $sth->fetch (PDO::FETCH_OBJ);

if (!$obj)
	throw new ApiException (__ ('This note does not exists!'), ApiException::ERROR_INVALID_PARAMETER, ApiException::BAD_REQUEST);

$sth = $db->prepare ("SELECT
						m._code AS code,
						m._type AS type,
						c._code AS file,
						m._longitude AS longitude,
						m._latitude AS latitude,
						m._altitude AS altitude,
						EXTRACT (EPOCH FROM m._date)::integer AS date,
						CASE WHEN m._deleted = B'1' OR c._deleted = B'1' THEN B'1' ELSE B'0' END AS deleted
					  FROM _note_media m
					  JOIN _note n ON n._id = m._note
					  JOIN _cloud c ON c._id = m._file
					  WHERE n._code = :code");

$sth->bindParam (':code', $code, PDO::PARAM_STR);

$sth->execute ();

$obj->media = $sth->fetchAll (PDO::FETCH_ASSOC);

echo json_encode ($obj);
