<?php

$db = Database::singleton ();

$sql = "SELECT DISTINCT _mimetype FROM _file";

$sth = $db->prepare ($sql);

$sth->execute ();

while ($obj = $sth->fetch (PDO::FETCH_OBJ))
	echo $obj->_mimetype .' <br />';
