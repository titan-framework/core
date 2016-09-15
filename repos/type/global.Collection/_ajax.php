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
				throw new Exception ('Não foi possível recuperar os dados submetidos!');
			
			if (!$itemId)
			{
				$father = array (	'type' => 	'Integer',
									'column' => $fatherColumn,
									'value'	 => $fatherId);
				
				$form->addField ($father);
			}
			
			$itemId = $form->save ($itemId, FALSE);
			
			if (!$itemId)
				throw new Exception ('Não foi possível salvar os dados submetidos!');
			
			$message->addMessage ('Os dados foram salvos com sucesso!');
			
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
	
	public function addRow ($itemId, $file, $fieldId)
	{
		$message = Message::singleton ();
		
		try
		{
			$instance = Instance::singleton ();
			
			set_error_handler ('logPhpError');
			
			$form = new Form ($file);
			
			if (!$form->load ($itemId))
				throw new Exception ('Não foi possível carregar os dados do item!');
			
			$output = array ();
			while ($field = $form->getField (FALSE))
				$output [] = View::toList ($field);
			
			$output [] = '<img src="titan.php?target=loadFile&amp;file=interface/icon/delete.gif" border="0" title="Apagar" style="cursor: pointer;" onclick="JavaScript: global.Collection.delRow (\''. $fieldId .'\', \''. $form->getFile () .'\', \''. $itemId .'\');" />&nbsp;';
			
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
				throw new Exception ('Não foi possível apagar o item!');
			
			restore_error_handler ();
			
			$message->addMessage ('Item apagado com sucesso!');
			
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