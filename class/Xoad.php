<?php
/**
 * Xoad.php
 *
 * Class with internal ajax functions.
 *
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @category class
 * @package core
 * @subpackage ajax
 * @copyright Creative Commons Attribution No Derivatives (CC-BY-ND)
 * @see Ajax, AjaxLogon, AjaxPasswd
 */
class Xoad
{
	public function validate ($file, $formData, $itemId = 0)
	{
		$message = Message::singleton ();
		
		try
		{
			set_error_handler ('logPhpError');
			
			$array = $this->validateRequired ($file, $formData);
			
			if (sizeof ($array))
				return "'". implode ("', '", $array) ."'";
			
			$array = $this->validateField ($file, $formData);
			
			if (sizeof ($array))
				return "'". implode ("', '", $array) ."'";
			
			$array = $this->validateUnique ($file, $formData, $itemId);
			
			if (sizeof ($array))
				return "'". implode ("', '", $array) ."'";
			
			restore_error_handler ();
			
			return "";
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
		
		return "";
	}
	
	public function validateField ($file, $formData)
	{
		$message = Message::singleton ();
		
		$return = array ();
		
		try
		{
			$form = new Form ($file);
			
			$form->recovery ($formData);
			
			$fields = $form->getFields ();
			
			$labels = array ();
			$assigns = array ();
			
			foreach ($fields as $key => $field)
				if (!$field->isReadOnly () && !$field->isValid ())
				{
					$assigns [] = $field->getAssign ();
					$labels [] = $field->getLabel ();
				}
			
			if (!sizeof ($assigns))
				return $return;
			
			if (sizeof ($labels) > 1)
			{
				$last = array_pop ($labels);
				
				$list = '"'. implode ('", "', $labels) .'" '. __ ('and') .' "'. $last .'"';
			}
			else
				$list = '"'. $labels [0] .'"';
			
			$message->addWarning (__ ('Enter valid values ​​in the fields: [1].', $list));
			
			$return = $assigns;
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
		
		return $return;
	}

	public function validateUnique ($file, $formData, $itemId = 0)
	{
		$message = Message::singleton ();
		
		$return = array ();
		
		try
		{
			$db = Database::singleton ();
			
			$form = new Form ($file);
			
			$form->recovery ($formData);
			
			$fields = $form->getUniques ();
			
			foreach ($fields as $key => $field)
			{
				if ($field->isEmpty () || $field->isReadOnly ())
					continue;
				
				$sth = $db->prepare ("SELECT * FROM ". $form->getTable () ." WHERE ". $field->getColumn () ." = ". Database::toValue ($field) ." AND ". $form->getPrimary () ." != '". $itemId ."'");
				
				$sth->execute ();
				
				$obj = $sth->fetch (PDO::FETCH_OBJ);
		
				if (!$obj)
					continue;
				
				$message->addWarning (__ ('The field [1] must be unique. There is already an occurrence for [2] in database.', '"'. $field->getLabel () .'"', '"'. Form::toHtml ($field) .'"'));
				
				$return [] = $field->getAssign ();
			}
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
		
		return $return;
	}
	
	public function validateRequired ($file, $formData)
	{
		$message = Message::singleton ();
		
		$return = array ();
		
		try
		{
			$form = new Form ($file);
			
			$form->recovery ($formData);
			
			$fields = $form->getRequireds ();
			
			$labels = array ();
			$assigns = array ();
			
			foreach ($fields as $key => $field)
				if (!$field->isReadOnly () && $field->isEmpty ())
				{
					$assigns [] = $field->getAssign ();
					$labels [] = $field->getLabel ();
				}
			
			if (!sizeof ($assigns))
				return $return;
			
			if (sizeof ($labels) > 1)
			{
				$last = array_pop ($labels);
				
				$list = '"'. implode ('", "', $labels) .'" '. __ ('and') .' "'. $last .'"';
			}
			else
				$list = '"'. $labels [0] .'"';
			
			$message->addWarning (__ ('Fields marked with * must be completed. Enter valid values ​​in the fields: [1].', $list));
			
			$return = $assigns;
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
		
		return $return;
	}
	
	public function save ($file, $formData, $itemId = 0)
	{
		$message = Message::singleton ();
		
		try
		{
			$instance = Instance::singleton ();
			
			$section = Business::singleton ()->getSection (Section::TCURRENT);
			
			$action = Business::singleton ()->getAction (Action::TCURRENT);
			
			if (!file_exists ($section->getCompPath () . $action->getName () .'.commit.php'))
				throw new Exception ('Arquivo ['. $section->getCompPath () . $action->getName () .'.commit.php] não encontrado.');
			
			set_error_handler ('logPhpError');
			
			require_once Instance::singleton ()->getCorePath () .'extra/htmlPurifier/HTMLPurifier.standalone.php';
			
			include $section->getCompPath () . $action->getName () .'.commit.php';
			
			restore_error_handler ();
			
			$message->save ();
			
			return TRUE;
		}
		catch (PDOException $e)
		{
			$message->addWarning ('Erro de execução de query: '. $e->getMessage ());
		}
		catch (Exception $e)
		{
			$message->addWarning ($e->getMessage ());
		}
		
		if (isset ($form) && is_object ($form) && get_class ($form) == 'Form')
			$form->setLoad (FALSE);
		
		restore_error_handler ();
		
		$message->save ();
		
		return FALSE;
	}
	
	public function getSuggest ($value, $filter, $owner)
	{
		$str  = "actb_self.actb_keywords = new Array();";
		$str .= "actb_self.actb_ids = new Array();";
		
		$names = array ();
		$ids = array ();
		
		$where = '';
		
		if (trim ($filter) != '')
			$where .= " AND (_mimetype = '". implode ("' OR _mimetype = '", explode (',', $filter)) ."')";
		
		if ((int) $owner)
			$where .= " AND _user = '". User::singleton ()->getId () ."'";
		
		try
		{
			$db = Database::singleton ();
			
			$sth = $db->prepare ("SELECT _id, _name FROM _file WHERE _name ILIKE '%". $value ."%'". $where);
			
			$sth->execute ();
			
			while ($obj = $sth->fetch (PDO::FETCH_OBJ))
			{
				$names [] = $obj->_name;
				$ids [] = $obj->_id;
			}
			
			$str  = "actb_self.actb_keywords = new Array('". implode ("','", $names) ."');";
			$str .= "actb_self.actb_ids = new Array('". implode ("','", $ids) ."');";
		}
		catch (PDOException $e)
		{
			toLog ($e->getMessage ());
		}
		catch (Exception $e)
		{
			toLog ($e->getMessage ());
		}
		
		return $str;
	}
	
	public function getFileResume ($id, $details = TRUE, $dimension = 100)
	{
		$message = Message::singleton ();
		
		try
		{
			return File::synopsis ($id, array (), $dimension);
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
	
	public function tableExists ($name)
	{
		$message = Message::singleton ();
		
		try
		{
			return Database::tableExists ($name);
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
	
	public function addFeed ($url)
	{
		$message = Message::singleton ();
		
		$return = TRUE;
		
		try
		{
			if (!$this->tableExists ('_rss'))
				throw new Exception ('Não há suporte para Feeds RSS no sistema. Instale o componente [global.home] para obter suporte.');
			
			$db = Database::singleton ();
			
			$user = User::singleton ();
			
			$sth = $db->prepare ("INSERT INTO _rss (_url, _user) VALUES ('". $url ."', '". $user->getId () ."')");
			
			$sth->execute ();
			
			$message->addMessage ('Feed RSS adicionado ao monitoramento do sistema.');
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
	
	public function makeVersionable ($table, $primary)
	{
		$message = Message::singleton ();
		
		$return = TRUE;
		
		try
		{
			Version::singleton ()->make ($table, $primary);
			
			$message->addMessage ('A seção agora está sob Controle de Versões! Qualquer nova alteração nos itens será logada e poderá ser revertida.');
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
	
	public function loadRevision ($itemId, $version)
	{
		$message = Message::singleton ();
		
		set_error_handler ('logPhpError');
		
		$return = FALSE;
		
		try
		{
			$instance = Instance::singleton ();
			
			$section = Business::singleton ()->getSection (Section::TCURRENT);
			
			$action = Business::singleton ()->getAction (Action::TCURRENT);
			
			$form = new VersionForm ('version.xml', 'view.xml', 'all.xml');
			
			if (!$form->load ($itemId, $version))
				throw new Exception ('Não foi possível carregar os dados do item!');
			
			$array = array ();
			while ($group = $form->getGroup ())
			{
				ob_start ();
				?>
				<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0" style="border-width: 0px;">
					<?php
					$backColor = 'FFFFFF';
					while ($field = $form->getField (FALSE, $group->getId ()))
					{
						$label = Form::toLabel ($field);
						$backColor = $backColor == 'FFFFFF' ? 'F4F4F4' : 'FFFFFF';
						?>
						<tr id="row_<?= $field->getAssign () ?>" height="18px" style="background-color: #<?= $backColor ?>;">
							<td width="20%" nowrap style="text-align: right;"><b><?= trim ($label) == '&nbsp;' ? '&nbsp;' : $label .':' ?></b></td>
							<td><?= Form::toHtml ($field) ?></td>
						</tr>
						<tr height="2px"><td></td></tr>
						<?php
					}
					?>
				</table>
				<?php
				$output = ob_get_clean ();
				
				if ($group->getId ())
				{
					ob_start ();
					?>
					<fieldset id="group_<?= $group->getId () ?>" class="<?= $group->isVisible () ? 'formGroup' : 'formGroupCollapse' ?>">
						<legend onclick="JavaScript: showGroup (<?= $group->getId () ?>); return false;">
							<?= $group->getLabel () ?>
						</legend>
						<div>
							<?= $output ?>
						</div>
					</fieldset>
					<?php
					$output = ob_get_clean ();
				}
				
				$array [] = $output;
			}
			
			$label = &XOAD_HTML::getElementById ('_REVISION_'. $version);
		
			$label->innerHTML = implode ('', $array);
			
			$return = TRUE;
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
		
		return $return;
	}
	
	public function revertRevision ($itemId, $version)
	{
		$message = Message::singleton ();
		
		set_error_handler ('logPhpError');
		
		$return = FALSE;
		
		try
		{
			$instance = Instance::singleton ();
			
			$section = Business::singleton ()->getSection (Section::TCURRENT);
			
			$action = Business::singleton ()->getAction (Action::TCURRENT);
			
			$form = new VersionForm ('version.xml', 'view.xml', 'all.xml');
			
			if (!$form->load ($itemId, $version))
				throw new Exception ('Não foi possível carregar os dados do item!');
			
			if (!$form->revert ())
				throw new Exception ('Impossível reverter revisão!');
			
			$message->addMessage ('Os item foi revertido para a revisão ['. $version .'] com sucesso!');
			
			$return = TRUE;
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
		
		return $return;
	}
	
	public function inPlaceStatusValue ($itemId, $table, $primary, $column)
	{
		$message = Message::singleton ();
		
		try
		{
			$sth = Database::singleton ()->prepare ("SELECT ". $column ." FROM ". $table ." WHERE ". $primary ." = '". $itemId ."'");
			
			$sth->execute ();
			
			$obj = $sth->fetch (PDO::FETCH_OBJ);
			
			if (!$obj)
				return NULL;
			
			return $obj->$column;
		}
		catch (PDOException $e)
		{
			$message->addWarning ($e->getMessage ());
		}
		
		$message->save ();
		
		return FALSE;
	}
	
	public function inPlaceStatusChange ($itemId, $table, $primary, $column, $value)
	{
		$message = Message::singleton ();
		
		try
		{
			if (!User::singleton ()->isRegistered ($table, $column, $itemId, $value))
				throw new Exception (__ ('You dont have permission to access, edit or delete this item!'));
			
			$db = Database::singleton ();
			
			try
			{
				$sth = $db->prepare ("UPDATE ". $table ." SET ". $column ." = '". $value ."', _update = now() WHERE ". $primary ." = '". $itemId ."'");
				
				$sth->execute ();
			}
			catch (PDOException $e)
			{
				$sth = $db->prepare ("UPDATE ". $table ." SET ". $column ." = '". $value ."' WHERE ". $primary ." = '". $itemId ."'");
				
				$sth->execute ();
			}

			$message->addMessage (__ ('The status has been changed!'));
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
		
		return FALSE;
	}
	
	public function sendBugReport ($formData)
	{
		$message = Message::singleton ();
		
		try
		{
			$instance = Instance::singleton ();
			
			$to = explode (',', $instance->getEmail ());
			
			array_walk ($to, 'cleanArray');
			
			if (!in_array ('bug@titanframework.com', $to))
				$to [] = 'bug@titanframework.com';
			
			$subject = '[Titan Framework] Bug Report';
			
			if (trim ($formData ['mail']) != '')
				$header = "From: ". Instance::singleton ()->getEmail () ."\r\nReply-To: ". trim ($formData ['mail']) ."\r\n";
			else
				$header = "From: ". Instance::singleton ()->getEmail ();
			
			$header .= "Content-Type: text/plain; charset=utf-8";
			
			ob_start ();
			?>

Application: <?= $instance->getName () ?> [<?= $instance->getUrl () ?>] 
Date: <?= date ('d-m-Y H:i:s') ?> 
Author: <?= $formData ['name'] ?> [<?= $formData ['mail'] ?>] [IP: <?= $_SERVER ['REMOTE_ADDR'] ?>] 
Browser: <?= $formData ['browser'] ?> [<?= $_SERVER ['HTTP_USER_AGENT'] ?>] 
Breadcrumb: <?= $formData ['bread'] ?> [<?= $instance->getUrl () . $_SERVER ['REQUEST_URI'] ?>] 
 
Description: 
<?= $formData ['description'] ?>
 
			<?php
			$msg = ob_get_clean ();
			
			if (!@mail (implode (',', $to), '=?utf-8?B?'. base64_encode ($subject) .'?=', $msg, $header))
				throw new Exception (__ ('Impossible to send bug report! Please, try again later.'));
			
			$message->addMessage (__ ('Report has been send! Comming soon you will receive a feedback.'));
		}
		catch (PDOException $e)
		{
			$message->addWarning ('Erro de execução de query: '. $e->getMessage ());
		}
		catch (Exception $e)
		{
			$message->addWarning ($e->getMessage ());
		}
		
		$message->save ();
		
		return FALSE;
	}
	
	public function changeLanguage ($language)
	{
		$message = Message::singleton ();
		
		try
		{
			Localization::singleton ()->setLanguage ($language);
			
			User::singleton ()->changeLanguage ($language);
			
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
	
	public function getAlerts ()
	{
		$message = Message::singleton ();
		
		try
		{
			if (!Database::tableExists ('_alert'))
				throw new Exception (__ ('Alerts module is not enable in this application!'));
			
			set_error_handler ('logPhpError');
			
			$array = Alert::singleton ()->getAlerts (User::singleton ()->getId ());
			
			$alerts = array ();
			
			foreach ($array as $id => $alert)
				$alerts [] = "{ id: ". $id .", message: '". $alert ['_MESSAGE_'] ."', icon: '". $alert ['_ICON_'] ."', link: '". $alert ['_GO_'] ."', read: ". $alert ['_READ_'] ." }";
			
			restore_error_handler ();
			
			return "var alerts = new Array (". implode (", ", $alerts) .");";
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
		
		return 'var alerts = new Array ();';
	}
	
	public function readAlert ($id)
	{
		try
		{
			if (!Database::tableExists ('_alert'))
				return FALSE;
			
			return Alert::singleton ()->read ($id, User::singleton ()->getId ());
			
		}
		catch (Exception $e)
		{
			toLog ($e->getMessage ());
		}
		catch (PDOException $e)
		{
			toLog ($e->getMessage ());
		}
		
		return FALSE;
	}
	
	public function deleteAlert ($id)
	{
		try
		{
			if (!Database::tableExists ('_alert'))
				return FALSE;
			
			return Alert::singleton ()->delete ($id, User::singleton ()->getId ());
			
		}
		catch (Exception $e)
		{
			toLog ($e->getMessage ());
		}
		catch (PDOException $e)
		{
			toLog ($e->getMessage ());
		}
		
		return FALSE;
	}
	
	public function getItemsInShoppingCart ()
	{
		$message = Message::singleton ();
		
		try
		{
			if (!Shopping::isActive ())
				throw new Exception (__ ('Shopping module is not enable in this application!'));
			
			set_error_handler ('logPhpError');
			
			$array = Shopping::singleton ()->getShoppingCartItems (User::singleton ()->getId ());
			
			$items = array ();
			
			foreach ($array as $id => $item)
				$items [] = "{ id: ". $id .", description: '". addslashes ($item ['_DESCRIPTION_']) ."', quantity: ". $item ['_QUANTITY_'] .", value: ". number_format ($item ['_VALUE_'], 2, '.', '') ." }";
			
			restore_error_handler ();
			
			return "var items = new Array (". implode (", ", $items) .");";
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
		
		return 'var items = new Array ();';
	}
	
	public function deleteItemFromShoppingCart ($id)
	{
		$message = Message::singleton ();
		
		try
		{
			if (!Shopping::isActive ())
				return FALSE;
			
			if (Shopping::singleton ()->deleteByOwner ($id, User::singleton ()->getId ()))
				return TRUE;
		}
		catch (Exception $e)
		{
			toLog ($e->getMessage ());
		}
		catch (PDOException $e)
		{
			toLog ($e->getMessage ());
		}
		
		$message->addWarning (__ ('Impossible to delete item from shopping cart! Verify if payment has been confirmed. In this case, you cannot delete this item.'));
		
		$message->save ();
		
		return FALSE;
	}
	
	public function copyItem ($itemId)
	{
		$message = Message::singleton ();
		
		try
		{
			if (!(int) $itemId)
				throw new Exception (__ ('Error! Data losted.'));
			
			set_error_handler ('logPhpError');
			
			$form = new Form (array ('copy.xml', 'create.xml', 'all.xml'));
			
			if (!$form->load ($itemId))
				throw new Exception (__('Unable to load the data of the item!'));
			
			$form->setId (0);
			
			$form->setLoad (FALSE);
						
			$newId = $form->save ();
			
			if (!$newId)
				throw new Exception (__ ('Unable to duplicate item!'));
			
			while ($field = $form->getField ())
			{
				if (!$field->isSavable ())
					try
					{
						$field->copy ($itemId, $newId);
					}
					catch (Exception $e)
					{
						$message->addWarning ($e->getMessage ());
					}
					catch (PDOException $e)
					{
						$message->addWarning ('DB Error: '. $e->getMessage ());
					}
			}
			
			restore_error_handler ();
			
			Log::singleton ()->add ('COPY', $form->getResume ($newId));
			
			$message->save ();
			
			return $newId;
		}
		catch (PDOException $e)
		{
			$message->addWarning ('Error: '. $e->getMessage ());
		}
		catch (Exception $e)
		{
			$message->addWarning ($e->getMessage ());
		}
		
		restore_error_handler ();
		
		$message->save ();
		
		return 0;
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
	
	public function xoadGetMeta ()
	{
		$methods = get_class_methods ($this);
		
		XOAD_Client::mapMethods ($this, $methods);

		XOAD_Client::publicMethods ($this, $methods);
		
		XOAD_Client::privateMethods ($this, array ());
	}
}
?>