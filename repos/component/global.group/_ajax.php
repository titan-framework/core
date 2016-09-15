<?php
class Ajax
{

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