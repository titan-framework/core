<?php

class xFck
{	
	public function getFileResume ($id)
	{
		set_error_handler ('logPhpError');
		
		try
		{
			$output = Fck::synopsis ($id);
		}
		catch (Exception $e)
		{
			$output = '<div style="text-align: left; font-weight: bold; color: #900; margin: 10px;">'. $e->getMessage () .'</div>';
		}
		catch (PDOException $e)
		{
			$output = '<div style="text-align: left; font-weight: bold; color: #900; margin: 10px;">'. $e->getMessage () .'</div>';
		}
		
		restore_error_handler ();
		
		return $output;
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