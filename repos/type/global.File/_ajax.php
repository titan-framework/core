<?php

class xFile
{	
	public function getFileResume ($id, $dimension = 200)
	{
		try
		{
			return File::synopsis ($id, array (), $dimension);
		}
		catch (Exception $e)
		{
			return '<div style="text-align: left; font-weight: bold; color: #900; margin: 10px;">'. $e->getMessage () .'</div>';
		}
		catch (PDOException $e)
		{
			return '<div style="text-align: left; font-weight: bold; color: #900; margin: 10px;">'. $e->getMessage () .'</div>';
		}
	}
	
	public function search ($term, $filter, $owner)
	{
		$term = trim ($term);
		
		if (strlen ($term) < 3)
			return '[]';
		
		$term = '%'. $term .'%';
		
		try
		{
			$array = explode (',', $filter);
			
			$mimetypes = array ();
			
			foreach ($array as $trash => $mime)
				if (Archive::singleton ()->isAcceptable ($mime))
					$mimetypes [] = $mime;
			
			if (sizeof ($mimetypes))
				$sql = "SELECT f.*, u._name AS user, u._email AS email,
						EXTRACT (EPOCH FROM f._create_date) AS taken
						FROM _file f
						JOIN _user u ON u._id = f._user 
						WHERE f._name ILIKE (:term) AND f._mimetype IN ('". implode ("', '", $mimetypes) ."')". ((bool) $owner ? " AND f._user = '". User::singleton ()->getId () ."'" : "") ."
						ORDER BY f._create_date DESC";
			else
				$sql = "SELECT f.*, u._name AS user, u._email AS email,
						EXTRACT (EPOCH FROM f._create_date) AS taken
						FROM _file f
						JOIN _user u ON u._id = f._user 
						WHERE f._name ILIKE (:term)". ((bool) $owner ? " AND f._user = '". User::singleton ()->getId () ."'" : "") ."
						ORDER BY f._create_date DESC";
			
			$sth = Database::singleton ()->prepare ($sql);
			
			$sth->bindParam (':term', $term, PDO::PARAM_STR);
			
			$sth->execute ();
			
			$result = array ();
			
			while ($obj = $sth->fetch (PDO::FETCH_OBJ))
			{
				if (!is_null (@$obj->_public) && !(int) $obj->_public)
					continue;
				
				if (!file_exists (File::getFilePath ($obj->_id)) && !file_exists (File::getLegacyFilePath ($obj->_id)))
					continue;
				
				$result [] = (object) array (
					'id' => $obj->_id,
					'name' => $obj->_name,
					'size' => File::formatFileSizeForHuman ($obj->_size),
					'mimetype' => $obj->_mimetype,
					'author' => __ ('By [1] ([2]) on [3].', $obj->user, $obj->email, strftime ('%x %X', $obj->taken))
				);
			}
			
			return json_encode ($result);
		}
		catch (Exception $e)
		{
			toLog ($e->getMessage ());
			
			return '[]';
		}
		catch (PDOException $e)
		{
			toLog ($e->getMessage ());
			
			return '[]';
		}
	}
	
	public function last ($filter, $owner)
	{
		try
		{
			$array = explode (',', $filter);
			
			$mimetypes = array ();
			
			foreach ($array as $trash => $mime)
				if (Archive::singleton ()->isAcceptable ($mime))
					$mimetypes [] = $mime;
			
			if (sizeof ($mimetypes))
				$sql = "SELECT f.*, u._name AS user, u._email AS email,
						EXTRACT (EPOCH FROM f._create_date) AS taken
						FROM _file f
						JOIN _user u ON u._id = f._user 
						WHERE f._mimetype IN ('". implode ("', '", $mimetypes) ."')". ((bool) $owner ? " AND f._user = '". User::singleton ()->getId () ."'" : "") ."
						ORDER BY f._create_date DESC
						LIMIT 20";
			else
				$sql = "SELECT f.*, u._name AS user, u._email AS email,
						EXTRACT (EPOCH FROM f._create_date) AS taken
						FROM _file f
						JOIN _user u ON u._id = f._user 
						WHERE 1 = 1". ((bool) $owner ? " AND f._user = '". User::singleton ()->getId () ."'" : "") ."
						ORDER BY f._create_date DESC
						LIMIT 20";
			
			$sth = Database::singleton ()->prepare ($sql);
			
			$sth->execute ();
			
			$result = array ();
			
			while ($obj = $sth->fetch (PDO::FETCH_OBJ))
			{
				if (!is_null (@$obj->_public) && !(int) $obj->_public)
					continue;
				
				if (!file_exists (File::getFilePath ($obj->_id)) && !file_exists (File::getLegacyFilePath ($obj->_id)))
					continue;
				
				$result [] = (object) array (
					'id' => $obj->_id,
					'name' => $obj->_name,
					'size' => File::formatFileSizeForHuman ($obj->_size),
					'mimetype' => $obj->_mimetype,
					'author' => __ ('By [1] ([2]) on [3].', $obj->user, $obj->email, strftime ('%x %X', $obj->taken))
				);
			}
			
			return json_encode ($result);
		}
		catch (Exception $e)
		{
			toLog ($e->getMessage ());
			
			return '[]';
		}
		catch (PDOException $e)
		{
			toLog ($e->getMessage ());
			
			return '[]';
		}
	}
	
	public function showMessages ()
	{
		$message = Message::singleton ();

		if (!is_object ($message) || !$message->has ())
			return FALSE;

		$str = '';
		while ($msg = $message->get ())
			$str .= $msg;

		$msgs = &XOAD_HTML::getElementById ('labelMessage');

		$msgs->innerHTML = '<div id="idMessage">'. $str .'</div>';

		$message->clear ();

		return TRUE;
	}

	public function delay ()
	{
		sleep (1);
	}

	public function xoadGetMeta()
	{
		$methods = get_class_methods ($this);
		
		XOAD_Client::mapMethods ($this, $methods);

		XOAD_Client::publicMethods ($this, $methods);
		
		XOAD_Client::privateMethods ($this, array ());
	}
}