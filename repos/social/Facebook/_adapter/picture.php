<?
try
{
	$db = Database::singleton ();
	
	$query = $db->query ("SELECT MIN(u._id) AS id FROM _user u JOIN _user_group ug ON ug._user = u._id JOIN _group g ON g._id = ug._group  WHERE g._admin = B'1'");
	
	$user = (int) $query->fetch (PDO::FETCH_COLUMN);
	
	if (!$user || trim ($value) == '')
		return NULL;
	
	$image = file_get_contents ('http://graph.facebook.com/'. $value .'/picture?type=large');
	
	$id = Database::nextId ('_file', '_id');
	
	$file = File::getFilePath ($id);
	
	if (!file_put_contents ($file, $image))
		return NULL;
	
	$mime = 'image/jpeg';
	
	$name = $value .'.jpg';
	
	$sql = "INSERT INTO _file (_id, _name, _mimetype, _size, _user) VALUES (:id, :name, :mime, :size, :user)";
	
	$sth = $db->prepare ($sql);
	
	$sth->bindParam (':id', $id, PDO::PARAM_INT);
	$sth->bindParam (':name', $name, PDO::PARAM_STR);
	$sth->bindParam (':mime', $mime, PDO::PARAM_STR);
	$sth->bindParam (':size', filesize ($file), PDO::PARAM_INT);
	$sth->bindParam (':user', $user, PDO::PARAM_INT);
	
	$sth->execute ();
	
	return $id;
}
catch (PDOException $e)
{
	toLog ($e->getMessage ());
}

return NULL;
?>