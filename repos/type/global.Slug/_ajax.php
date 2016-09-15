<?php
class xSlug
{
	public function generateSlug ($string, $table, $column)
	{
		$string = Slug::format ($string);
		
		try
		{
			$db = Database::singleton ();
			
			$count = 0;
			
			$suffix = '';
			
			do
			{
				if ($count++)
					$suffix = '-'. ($count - 1);
				
				$sth = $db->prepare ("SELECT ". $column ." FROM ". $table ." WHERE ". $column ." = '". $string . $suffix ."'");
			
				$sth->execute ();
				
				$obj = $sth->fetch (PDO::FETCH_OBJ);
			} while ($obj);
			
			$string .= $suffix;
		}
		catch (PDOException $e)
		{
			$string = __ ('Impossible to generate SLUG!');
			
			toLog ($e->getMessage ());
		}
		toLog ('#'. $string .'#');
		return $string;
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