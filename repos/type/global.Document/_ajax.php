<?
class xDocument
{
	public function save ($formData, $fatherId, $id, $relation, $template, $validate)
	{
		$message = Message::singleton ();
		
		try
		{
			set_error_handler ('logPhpError');
			
			$form = new DocumentForm ($template);
			
			if (!$form->recovery ($formData))
				throw new Exception (__ ('Unable to retrieve the data submitted!'));
			
			$version = $form->save ($relation, $fatherId, $id, $validate);
			
			if (!$version)
				throw new Exception (__ ('Unable to save the data submitted!'));
			
			$message->addMessage (__ ('Data saved with success!'));
			
			restore_error_handler ();
			
			$message->save ();
			
			return $version;
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
	
	public function register ($relation, $id, $itemId, $version, $template, $label)
	{
		return Document::register ($relation, $id, $itemId, $version, $template, $label);
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