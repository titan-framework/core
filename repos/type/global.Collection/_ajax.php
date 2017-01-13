<?php
class xCollection
{
	public function save ($file, $formData, $fatherId, $fatherColumn, $itemId = 0)
	{
		$message = Message::singleton ();

		try
		{
			$instance = Instance::singleton ();

			set_error_handler ('logPhpError');

			$section = Business::singleton ()->getSection (Section::TCURRENT);

			$action = Business::singleton ()->getAction (Action::TCURRENT);

			$form = new Form ($file);

			if (!$form->recovery ($formData))
				throw new Exception (__ ('Unable to retrieve submitted data!'));

			if (!$itemId)
			{
				$father = array (	'type' => 	'Integer',
									'column' => $fatherColumn,
									'value'	 => $fatherId);

				$form->addField ($father);
			}

			$itemId = $form->save ($itemId, FALSE);

			if (!$itemId)
				throw new Exception (__ ('Unable to save submitted data!'));

			$message->addMessage (__ ('Your data has been successfully saved!'));

			restore_error_handler ();

			$message->save ();

			return $itemId;
		}
		catch (PDOException $e)
		{
			$message->addWarning ($e->getMessage ());
		}
		catch (Exception $e)
		{
			$message->addWarning ($e->getMessage ());
		}

		restore_error_handler ();

		$message->save ();

		return FALSE;
	}

	public function addRow ($itemId, $file, $fieldId, $fatherColumn)
	{
		$message = Message::singleton ();

		try
		{
			$instance = Instance::singleton ();

			set_error_handler ('logPhpError');

			$view = new View ($file);
			$form = new Form ($file);

			if (!$form->load ($itemId))
				throw new Exception (__ ('Could not load item data!'));

			$output = array ();
			while ($field = $form->getField (FALSE))
				$output [] = View::toList ($field);

			$icons = array ();

			if ($view->isSortable ())
			{
				$icons [] = '<input type="hidden" name="idForSort" value="'. $itemId .'" />';
				$icons [] = '<img src="titan.php?target=loadFile&file=interface/icon/arrow.up.gif" border="0" title="'. __ ('Up') .'" style="cursor: pointer;" onclick="JavaScript: global.Collection.up (this);" />&nbsp;';
				$icons [] = '<img src="titan.php?target=loadFile&file=interface/icon/arrow.down.gif" border="0" title="'. __ ('Down') .'" style="cursor: pointer;" onclick="JavaScript: global.Collection.down (this);" />&nbsp;';
			}

			$icons [] = '<img src="titan.php?target=loadFile&file=interface/icon/edit.gif" border="0" title="'. __ ('Edit') .'" style="cursor: pointer;" onclick="JavaScript: global.Collection.edit (\''. $fieldId .'\', \''. $file .'\', \''. $itemId .'\', \''. $fatherColumn .'\');" />&nbsp;';
			$icons [] = '<img src="titan.php?target=loadFile&amp;file=interface/icon/delete.gif" border="0" title="'. __ ('Delete') .'" style="cursor: pointer;" onclick="JavaScript: global.Collection.delRow (\''. $fieldId .'\', \''. $form->getFile () .'\', \''. $itemId .'\');" />&nbsp;';

			$output [] = implode ('\r\n', $icons);

			foreach ($output as $key => $value)
				$output [$key] = str_replace ("'", "\'", $value);

			restore_error_handler ();

			return "var columns = new Array ('". implode ("', '", $output) ."');";
		}
		catch (PDOException $e)
		{
			$message->addWarning ($e->getMessage ());
		}
		catch (Exception $e)
		{
			$message->addWarning ($e->getMessage ());
		}

		restore_error_handler ();

		$message->save ();

		return '<span style="font-weight: bold; color: #900;">Falhou!</span>';
	}

	public function delRow ($itemId, $file)
	{
		$message = Message::singleton ();

		try
		{
			$instance = Instance::singleton ();

			set_error_handler ('logPhpError');

			$section = Business::singleton ()->getSection (Section::TCURRENT);

			$action = Business::singleton ()->getAction (Action::TCURRENT);

			$form = new Form ($file);

			if (!$form->delete ($itemId))
				throw new Exception (__ ('Could not delete item!'));

			restore_error_handler ();

			$message->addMessage (__ ('Item deleted successfully!'));

			$message->save ();

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

		restore_error_handler ();

		$message->save ();

		return FALSE;
	}

	public function saveSort ($file, $sort)
	{
		$message = Message::singleton ();

		set_error_handler ('logPhpError');

		try
		{
			$instance = Instance::singleton ();

			$section = Business::singleton ()->getSection (Section::TCURRENT);

			$action = Business::singleton ()->getAction (Action::TCURRENT);

			$view = new View ($file);

			if (!$view->isSortable ())
				throw new Exception (__ ('This field is not sortable!'));

			$sql = "UPDATE ". $view->getTable () ." SET _order = :order WHERE ". $view->getPrimary () ." = :id";

			$db = Database::singleton ();

			try
			{
				$db->beginTransaction ();

				$sth = $db->prepare ($sql);

				foreach ($sort as $order => $id)
					$sth->execute (array (':order' => $order, ':id' => $id));

				$db->commit ();
			}
			catch (PDOException $e)
			{
				$db->rollBack ();

				throw $e;
			}
			catch (Exception $e)
			{
				$db->rollBack ();

				throw $e;
			}

			$message->addMessage (__ ('The data has been sorted successfully!'));
		}
		catch (PDOException $e)
		{
			$message->addWarning ($e->getMessage ());
		}
		catch (Exception $e)
		{
			$message->addWarning ($e->getMessage ());
		}

		restore_error_handler ();

		$message->save ();
	}

	public function loadEditForm ($file, $itemId, $fatherColumn, $fieldId)
	{
		$message = Message::singleton ();

		$output = '';

		set_error_handler ('logPhpError');

		try
		{
			$instance = Instance::singleton ();

			$section = Business::singleton ()->getSection (Section::TCURRENT);

			$action = Business::singleton ()->getAction (Action::TCURRENT);

			$form = new Form ($file);

			if (!$form->load ($itemId))
				throw new Exception (__ ('Could not load item data!'));

			$output = require 'edit.php';
		}
		catch (PDOException $e)
		{
			$message->addWarning ($e->getMessage ());
		}
		catch (Exception $e)
		{
			$message->addWarning ($e->getMessage ());
		}

		restore_error_handler ();

		$message->save ();

		return $output;
	}

	public function showMessages ($fieldId)
	{
		$message = Message::singleton ();

		if (!is_object ($message) || !$message->has ())
			return FALSE;

		$str = '';
		while ($msg = $message->get ())
			$str .= $msg;

		$msgs = &XOAD_HTML::getElementById ('collectionLabelMessage_'. $fieldId);

		$msgs->innerHTML = '<div class="message">'. $str .'</div>';

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
