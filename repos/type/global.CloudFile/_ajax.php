<?
class xCloudFile
{	
	public function getFileResume ($id, $dimension = 200)
	{
		try
		{
			return CloudFile::synopsis ($id, array (), $dimension);
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