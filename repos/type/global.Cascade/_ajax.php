<?php
class xCascade
{
	public function load ($table, $primary, $father, $view, $id)
	{
		$message = Message::singleton ();
		
		try
		{					
			$db = Database::singleton ();
			
			$field = new Cascade ($table, array ('column' => '', 'link-table' => $table, 'link-column' => $primary, 'link-view' => $view, 'father' => $father));
			
			$columns = implode (", ", $field->getColumnsView ());
			
			$sth = $db->prepare ("SELECT ". $columns .", ". $primary ." FROM ". $table ." WHERE ". $father ." = '". $id ."' ORDER BY ". $columns);
			
			$sth->execute ();
			
			$ids = array ();
			$labels = array ();
			
			while ($item = $sth->fetch (PDO::FETCH_OBJ))
			{
				$labels [] = $field->makeView ($item);
				$ids [] = $item->$primary;
			}
			
			if (sizeof ($ids))
			{
				$buffer  = "var ids = new Array ('". implode ("', '", $ids) ."'); ";
				$buffer .= "var lbs = new Array ('". implode ("', '", $labels) ."');";
				
				return $buffer;
			}
		}
		catch (PDOException $e)
		{
			toLog ($e->getMessage ());
		}
		catch (Exception $e)
		{
			toLog ($e->getMessage ());
		}
		
		return "var ids = null;";
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