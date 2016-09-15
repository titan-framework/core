<?php
class xNote
{
	public function locations ($note)
	{
		$message = Message::singleton ();
		
		try
		{
			$sql = "SELECT m._id AS id, m._type AS type, c._mimetype AS mimetype, EXTRACT (EPOCH FROM m._date) AS date,
					m._longitude AS longitude, m._latitude AS latitude, m._altitude AS altitude, m._file AS file
					FROM _note_media m
					JOIN _note n ON n._id = m._note
					JOIN _cloud c ON c._id = m._file
					WHERE n._id = :id AND n._deleted = B'0' AND m._deleted = B'0' AND c._deleted = B'0' AND c._ready = B'1'";
			
			$sth = Database::singleton ()->prepare ($sql);
			
			$sth->bindParam (':id', $note, PDO::PARAM_INT);
			
			$sth->execute ();
			
			$array = $sth->fetchAll (PDO::FETCH_ASSOC);
			
			foreach ($array as $key => $item)
				$array [$key]['date'] = strftime ('%x %X', $item ['date']);
			
			$message->save ();
			
			return json_encode ($array);
		}
		catch (PDOException $e)
		{
			$message->addWarning ($e->getMessage ());
		}
		
		$message->save ();
		
		return '[]';
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
?>