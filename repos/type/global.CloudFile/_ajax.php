<?
class xCloudFile
{
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
			
			$sth = $db->prepare ("SELECT _id, _name FROM _cloud WHERE _ready = B'1' AND _deleted = B'0' AND _name ILIKE '%". $value ."%'". $where);
			
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
			if (!file_exists (Archive::singleton ()->getDataPath () . 'cloud_' . str_pad ($id, 7, '0', STR_PAD_LEFT)))
				throw new Exception (__ ('The file has not been fully loaded and can not be displayed until it is.'));
			
			$db = Database::singleton ();
			
			$sth = $db->prepare ("SELECT _name, _size, _mimetype FROM _cloud WHERE _id = :id AND _ready = B'1' AND _deleted = B'0'");
			
			$sth->bindParam (':id', $id, PDO::PARAM_INT);
			
			$sth->execute ();
			
			$obj = $sth->fetch (PDO::FETCH_OBJ);
			
			if (!$obj)
				throw new Exception (__ ('There is no associated file!'));
			
			ob_start ();
			
			if ($details)
			{
				?>
				<div style="position: absolute; width: 100px; height: 100px; top: 3px; left: 3px;">
					<a href="titan.php?target=tScript&amp;type=CloudFile&amp;file=open&amp;fileId=<?= $id ?>" target="_blank"><img src="titan.php?target=tScript&amp;type=CloudFile&amp;file=thumbnail&amp;fileId=<?= $id ?>&width=100&height=100" border="0" /></a>
				</div>
				<div style="position: relative; width: 190px; top: 10px; left: 110px; overflow: hidden; background-color: #FFF;">
					<b><?= $obj->_name ?></b> <br />
					<?= $obj->_size ?> Bytes <br />
					<?= $obj->_mimetype ?> <br />
				</div>
				<?
			}
			else
			{
				$alt = $obj->_name ."\n". $obj->_size ." Bytes\n". $obj->_mimetype;
				?>
				<a href="titan.php?target=tScript&amp;type=CloudFile&amp;file=open&amp;fileId=<?= $id ?>" target="_blank" title="<?= $alt ?>"><img src="titan.php?target=tScript&amp;type=CloudFile&amp;file=thumbnail&amp;fileId=<?= $id ?>&height=<?= $dimension ?>" alt="<?= $alt ?>" border="0" /></a>
				<?
			}
			
			return ob_get_clean ();
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