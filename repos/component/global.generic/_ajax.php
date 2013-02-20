<?
class Ajax
{
	public function saveSort ($str, $table, $primary)
	{
		$message = Message::singleton ();

		$return = TRUE;

		try
		{
			parse_str ($str, $array);

			$db = Database::singleton ();

			$sth = $db->prepare ("UPDATE ". $table ." SET _order = :order WHERE ". $primary ." = :id");

			$aux = '';

			foreach ($array ['sortableList'] as $order => $id)
				$sth->execute (array (':order' => $order, ':id' => $id));

			$message->addMessage (__ ('Sort successfully performed!'));
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

	public function saveImageSort ($str, $table, $itemId)
	{
		$message = Message::singleton ();

		$return = TRUE;

		try
		{
			parse_str ($str, $array);

			$db = Database::singleton ();

			$sth = $db->prepare ("UPDATE ". $table ." SET _order = :order WHERE _item = '". $itemId ."' AND _media = :id");

			$aux = '';

			foreach ($array ['idGallery'] as $order => $id)
				$sth->execute (array (':order' => $order, ':id' => $id));

			$message->addMessage (__ ('Sort successfully performed!'));
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

	public function addFile ($fileId, $itemId)
	{
		if (!$fileId || !$itemId)
			return FALSE;

		$message = Message::singleton ();

		$return = TRUE;

		try
		{
			$form = Form::singleton ('vinculate.xml', 'all.xml');

			$table = $form->getTable () .'_file';

			if (!$this->tableExists ($table))
				throw new Exception (__ ('There is no support for link files in this section. The DSM must contain table [ [1] ] for offer support to this.', $table));

			$db = Database::singleton ();

			$db->exec ("INSERT INTO ". $table ." (_item, _file) VALUES (". $itemId .", ". $fileId .")");

			$message->addMessage (__ ('Archive linked with success!'));
		}
		catch (PDOException $e)
		{
			$message->addWarning ($e->getMessage ());

			$return = FALSE;
		}

		$message->save ();

		$this->showMessages ();

		return $return;
	}

	public function removeFile ($fileId, $itemId)
	{
		if (!$fileId || !$itemId)
			return FALSE;

		$message = Message::singleton ();

		$return = TRUE;

		try
		{
			$form = Form::singleton ('vinculate.xml', 'all.xml');

			$table = $form->getTable () .'_file';

			$db = Database::singleton ();

			$db->exec ("DELETE FROM ". $table ." WHERE _file = ". $fileId ." AND _item = ". $itemId);

			$message->addMessage (__ ('Archive unlinked with success!'));
		}
		catch (PDOException $e)
		{
			$message->addWarning ($e->getMessage ());

			$return = FALSE;
		}

		$message->save ();

		$this->showMessages ();

		return $return;
	}

	public function loadFiles ($itemId)
	{
		$message = Message::singleton ();

		try
		{
			if (!$itemId)
				throw new Exception (__ ('Invalid Document!'));

			$form = Form::singleton ('vinculate.xml', 'all.xml');

			$table = $form->getTable () .'_file';

			if (!$this->tableExists ($table))
				throw new Exception (__ ('There is no support for link files in this section. The DSM must contain table [ [1] ] for offer support to this.', $table));

			$db = Database::singleton ();

			$sth = $db->prepare ("SELECT _file.* FROM ". $table ." AS vinculate LEFT JOIN _file ON _file._id = vinculate._file WHERE vinculate._item = ". $itemId);

			$sth->execute ();

			$aux = '';
			while ($obj = $sth->fetch (PDO::FETCH_OBJ))
			{
				ob_start ();
				?>
				<div id="content_basket_<?= $obj->_id ?>" style="display:; position: relative; width: 300px; height: 124px; border: #CCCCCC 1px solid; margin-top: 2px; background-color: #FFFFFF;">
					<div style="position: absolute; width: 100px; height: 100px; top: 3px; left: 3px;">
						<a href="titan.php?target=openFile&fileId=<?= $obj->_id ?>" target="_blank"><img src="titan.php?target=viewThumb&fileId=<?= $obj->_id ?>&width=100&height=100" border="0"></a>
					</div>
					<div style="position: relative; width: 190px; top: 10px; left: 110px; overflow: hidden;">
						<b><?= $obj->_name ?></b> <br />
						<?= $obj->_size ?> Bytes <br />
						<?= $obj->_mimetype ?> <br />
						<font color="#000000"><?= $obj->_description ?></font> <br />
					</div>
					<div style="position: absolute; top: 106px; width: 294px; height: 12px; background-color: #CCCCCC; text-align: right; padding: 3px;">
						<a href="#" onclick="JavaScript: ajaxRemoveProduct ('<?= $obj->_id ?>', '<?= $itemId ?>'); return false;" style="color: #FFFFFF;"><?= __ ('Remove') ?></a>
					</div>
				</div>
				<?
				$aux = ob_get_clean ();
			}

			$content = &XOAD_HTML::getElementById ('tiedFiles');

			$content->innerHTML = $aux;

			return TRUE;
		}
		catch (PDOException $e)
		{
			$message->addWarning ($e->getMessage ());
		}
		catch (Exception $e)
		{
			$message->addWarning ($e->getMessage ());
		}

		$message->save ();

		$this->showMessages ();

		return FALSE;
	}

	public function removePhoto ($fileId, $itemId)
	{
		if (!$fileId || !$itemId)
			return FALSE;

		$message = Message::singleton ();

		$return = TRUE;

		try
		{
			$form = Form::singleton ('all.xml', 'view.xml', 'create.xml', 'edit.xml');

			$table = $form->getTable () .'_media';

			$db = Database::singleton ();

			$db->exec ("DELETE FROM ". $table ." WHERE _media = ". $fileId ." AND _item = ". $itemId);

			$message->addMessage (__ ('Archive unlinked with success!'));
		}
		catch (PDOException $e)
		{
			$message->addWarning ($e->getMessage ());

			$return = FALSE;
		}

		$message->save ();

		$this->showMessages ();

		return $return;
	}

	public function getPhotos ($itemId)
	{
		if (!$itemId)
			return '';

		$message = Message::singleton ();

		$result = array ();

		try
		{
			$form = Form::singleton ('all.xml', 'view.xml', 'create.xml', 'edit.xml');

			$table = $form->getTable () .'_media';

			$db = Database::singleton ();

			$sth = $db->prepare ("SELECT _media FROM ". $table ." WHERE _item = ". $itemId);

			$sth->execute ();

			while ($obj = $sth->fetch (PDO::FETCH_OBJ))
				$result [] = $obj->_media;
		}
		catch (PDOException $e)
		{
			$message->addWarning ($e->getMessage ());
		}

		$message->save ();

		$this->showMessages ();

		return "'". implode ("','", $result) ."'";
	}

	public function tableExists ($name)
	{
		$message = Message::singleton ();

		try
		{
			$db = Database::singleton ();

			$array = explode ('.', $name);

			if (sizeof ($array) == 2)
			{
				$schema = $array [0];
				$table = $array [1];
			}
			else
			{
				$schema = $db->getSchema ();
				$table = $array [0];
			}

			$sth = $db->prepare ("SELECT tablename FROM pg_tables WHERE schemaname = '". $schema ."' AND tablename = '". $table ."'");

			$sth->execute ();

			if (!$sth->rowCount ())
				return FALSE;

			return TRUE;
		}
		catch (Exception $e)
		{
			$message->addWarning ($e->getMessage ());
		}
		catch (PDOException $e)
		{
			$message->addWarning ($e->getMessage ());
		}

		$message->save ();

		return FALSE;
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
		$methods = get_class_methods ($this);

		XOAD_Client::mapMethods ($this, $methods);

		XOAD_Client::publicMethods ($this, $methods);

		XOAD_Client::privateMethods ($this, array ());
	}
}
?>