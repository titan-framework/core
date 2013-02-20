<?
class Ajax
{
	public function deleteFiles ($array)
	{
		$message = Message::singleton ();

		$db = Database::singleton ();

		$instance = Instance::singleton ();

		$archive = Archive::singleton ();

		$success = array ();

		$fail = array ();

		try
		{
			if (!sizeof ($array))
				throw new Exception ('Nenhum arquivo selecionado!');

			$sql = "SELECT _id, _name FROM _file WHERE _id = '". implode ("' OR _id = '", $array) ."'";

			$sth = $db->prepare ($sql);

			$sth->execute ();

			$files = array ();
			while ($obj = $sth->fetch (PDO::FETCH_OBJ))
				$files [$obj->_id] = $obj->_name;

			foreach ($array as $trash => $id)
			{
				if (!$id)
					continue;

				try
				{
					$sql = "DELETE FROM _file WHERE _id = '". $id ."'";

					$db->exec ($sql);

					$file = $archive->getDataPath () . 'file_' . str_pad ($id, 7, '0', STR_PAD_LEFT);

					if (!@unlink ($file) && file_exists ($file) && $instance->onDebugMode ())
						throw new Exception ('O arquivo ['. $files [$id] .' ('. $id .')] foi apagado do BD, mas não pôde ser deletado da pasta de arquivos.');
				}
				catch (PDOException $e)
				{
					$warning = 'O arquivo ['. $files [$id] .' ('. $id .')] não pôde ser apagado. Provavelmente está sendo utilizado em alguma seção.';

					if ($instance->onDebugMode ())
						$warning .= ' ['. $e->getMessage () .']';
					else
						toLog ($e->getMessage ());

					$message->addWarning ($warning);

					$fail [] = $id;

					continue;
				}
				catch (Exception $e)
				{
					if ($instance->onDebugMode ())
						$message->addWarning ($e->getMessage ());
					else
						toLog ($e->getMessage ());
				}

				$success [] = $id;
			}

			if (!sizeof ($success))
				throw new Exception ('Nenhum arquivo pôde ser apagado!');
			
			Lucene::singleton ()->deleteFile ($success);
			
			$aux = array ();

			foreach ($success as $trash => $id)
				$aux [] = $files [$id];

			$message->addMessage ('Os seguinte(s) arquivo(s) foram apagado(s) com sucesso: '. implode (', ', $aux) .'.');
			
			Log::singleton ()->add ('Arquivo(s) apagado(s) do sistema: '. implode (', ', $aux) .'.');
		}
		catch (Exception $e)
		{
			$message->addMessage ($e->getMessage ());
		}
		catch (PDOException $e)
		{
			$message->addWarning ($e->getMessage ());
		}

		$message->save ();

		$this->showMessages ();

		return "'". implode ("','", $success) ."'";
	}

	public function saveFieldFile ($id, $column, $value)
	{
		$message = Message::singleton ();

		$return = TRUE;

		try
		{
			$db = Database::singleton ();

			$sth = $db->prepare ("UPDATE _file SET _". $column ." = '". addslashes ($value) ."' WHERE _id = ". $id);

			$sth->execute ();

			$message->addMessage ('Alteração realizada com sucesso!');
			
			Log::singleton ()->add ('Dados de arquivo modificados [ID: '. $id .'].');
		}
		catch (PDOException $e)
		{
			$message->addWarning ($e->getMessage ());

			$return = FALSE;
		}
		catch (Exception $e)
		{
			$message->addWarning ($e->getMessage ());

			$return = FALSE;
		}

		$message->save ();

		$this->showMessages ();

		return $return;
	}

	public function delay ()
	{
		sleep (1);
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

	public function xoadGetMeta()
	{
		XOAD_Client::mapMethods ($this, array ('showMessages', 'delay', 'saveFieldFile', 'deleteFiles'));

		XOAD_Client::publicMethods ($this, array ('showMessages', 'delay', 'saveFieldFile', 'deleteFiles'));

		XOAD_Client::privateMethods ($this, array ());
	}
}
?>