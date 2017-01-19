<?php
/**
 * Implements global search on instance using Lucene.
 *
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @category class
 * @package core
 * @subpackage util
 * @copyright 2005-2017 Titan Framework
 * @license http://www.titanframework.com/license/ BSD License (3 Clause)
 * @see Instance, Section, Action, Business
 * @link https://lucene.apache.org/
 */
class Lucene
{
	static private $lucene = FALSE;

	static private $index = FALSE;

	private $path = FALSE;

	const ITEM	= '__ITEM__';
	const FILE 	= '__FILE__';
	const MEDIA = '__MEDIA__';

	private final function __construct ()
	{
		$array = Instance::singleton ()->getLucene ();

		if (!is_array ($array) || !array_key_exists ('index-path', $array))
			return;

		if (!class_exists ('Zend_Search_Lucene', FALSE))
			return;

		$this->path = $array ['index-path'];

		try
		{
			Zend_Search_Lucene_Search_QueryParser::setDefaultEncoding ('iso-8859-1');

			if (file_exists ($this->getPath ()))
			{
				self::$index = new Zend_Search_Lucene ($this->getPath ());

				return;
			}

			set_time_limit (0);

			self::$index = new Zend_Search_Lucene ($this->getPath (), TRUE);

			$aSection = Business::singleton ()->getSection (Section::TCURRENT)->getName ();
			$aAction  = Business::singleton ()->getAction (Action::TCURRENT)->getName ();

			$db = Database::singleton ();

			set_error_handler ('logPhpError');

			while ($section = Business::singleton ()->getSection ())
			{
				$targetActions = array ();

				while ($aux = $section->getAction ())
				{
					$indexTo = trim ($aux->getIndex ());

					if ($indexTo == '' || !$section->actionExists ($indexTo) || in_array ($indexTo, $targetActions))
						continue;

					$targetActions [] = $indexTo;

					$action = $section->getAction ($indexTo);

					Business::singleton ()->setCurrent ($section->getName (), $action->getName ());

					$form = new Form (array ($action->getXmlPath (), $action->getName () .'.xml', $action->getEngine () .'.xml', 'all.xml'));

					try
					{
						$sql = "SELECT ". $form->getPrimary () ." FROM ". $form->getTable () ." WHERE _deleted = B'0'";

						$sth = $db->prepare ($sql);

						$sth->execute ();
					}
					catch (PDOException $e)
					{
						try
						{
							$sql = "SELECT ". $form->getPrimary () ." FROM ". $form->getTable ();

							$sth = $db->prepare ($sql);

							$sth->execute ();
						}
						catch (PDOException $e)
						{
							toLog ('Lucene Exception: Impossível capturar tuplas de ['. $form->getTable () .'].');

							continue;
						}
					}

					$primary = $form->getPrimary ();

					while ($obj = $sth->fetch (PDO::FETCH_OBJ))
					{
						$itemId = $obj->$primary;

						$url = 'titan.php?target=body&toSection='. $section->getName () .'&toAction='. $indexTo .'&itemId='. $itemId;

						$doc = new Zend_Search_Lucene_Document ();

						$doc->addField (Zend_Search_Lucene_Field::Keyword ('url', $url, 'iso-8859-1'));

						$doc->addField (Zend_Search_Lucene_Field::Keyword ('type', self::ITEM, 'iso-8859-1'));

						$doc->addField (Zend_Search_Lucene_Field::Text ('local', getBreadPath ($section, FALSE, FALSE) . $section->getAction ($indexTo)->getLabel (), 'iso-8859-1'));

						$doc->addField (Zend_Search_Lucene_Field::Text ('content', $form->getResume ($itemId, TRUE), 'iso-8859-1'));

						self::$index->addDocument ($doc);

						$form->setLoad (FALSE);
					}
				}
			}

			self::$index->commit ();

			self::$index->optimize ();

			$this->saveFile (array (), TRUE);

			restore_error_handler ();

			Log::singleton ()->add (__ ('Automatic Lucene Index creation. Added [1] documents.', self::$index->count ()));

			Message::singleton ()->addMessage (__ ('Automatic Lucene Index creation. Added [1] documents.', self::$index->count ()));

			Business::singleton ()->setCurrent ($aSection, $aAction);
		}
		catch (Exception $e)
		{
			$error = Instance::singleton ()->onDebugMode () ? $e->getMessage () : '';

			Message::singleton ()->addWarning (__ ('Error on automatic Lucene Index creation! Contact administrator. [1]', '['. $error .']'));

			toLog ($e->getMessage ());

			restore_error_handler ();
		}
		catch (PDOException $e)
		{
			$error = Instance::singleton ()->onDebugMode () ? $e->getMessage () : '';

			Message::singleton ()->addWarning (__ ('Error on automatic Lucene Index creation! Contact administrator. [1]', '['. $error .']'));

			toLog ($e->getMessage ());

			restore_error_handler ();

			return;
		}

		Message::singleton ()->save ();
	}

	static public function singleton ()
	{
		if (self::$lucene !== FALSE)
			return self::$lucene;

		$class = __CLASS__;

		self::$lucene = new $class ();

		return self::$lucene;
	}

	public function getPath ()
	{
		return $this->path;
	}

	public function getIndex ()
	{
		return self::$index;
	}

	public function isActive ()
	{
		return self::$index !== FALSE;
	}

	public function save ($url, $content, $indexTo = FALSE, $section = FALSE, $local = '')
	{
		if (!$this->isActive ())
			return FALSE;

		if (is_numeric ($url))
		{
			try
			{
				if ($section === FALSE)
					$oSection = Business::singleton ()->getSection (Section::TCURRENT);
				else
					$oSection = Business::singleton ()->getSection ($section);

				if ($indexTo === FALSE)
					$indexTo = Business::singleton ()->getAction (Action::TCURRENT)->getIndex ();

				if (trim ($indexTo) == '' || !$oSection->actionExists ($indexTo))
					return FALSE;

				$oAction = $oSection->getAction ($indexTo);

				$url = 'titan.php?target=body&toSection='. $oSection->getName () .'&toAction='. $oAction->getName () .'&itemId='. $url;

				if ($local == '')
					$local = getBreadPath ($oSection, FALSE, FALSE) . $oAction->getLabel ();
			}
			catch (Exception $e)
			{
				toLog ($e->getMessage ());

				return FALSE;
			}
		}

		try
		{
			set_time_limit (0);

			$index = $this->getIndex ();

			$term = new Zend_Search_Lucene_Index_Term ($url, 'url');

			$ids  = $index->termDocs ($term);

			foreach ($ids as $id)
				$index->delete ($id);

			$doc = new Zend_Search_Lucene_Document ();

			$doc->addField (Zend_Search_Lucene_Field::Keyword ('url', $url, 'iso-8859-1'));

			$doc->addField (Zend_Search_Lucene_Field::Keyword ('type', self::ITEM, 'iso-8859-1'));

			$doc->addField (Zend_Search_Lucene_Field::Text ('local', $local, 'iso-8859-1'));

			$doc->addField (Zend_Search_Lucene_Field::Text ('content', $content, 'iso-8859-1'));

			$index->addDocument ($doc);

			$index->commit ();
		}
		catch (Exception $e)
		{
			toLog (__ ('Impossible create index search for [[1]]: [2].', $url, $e->getMessage ()));

			return FALSE;
		}

		return TRUE;
	}

	public function delete ($url)
	{
		if (!$this->isActive ())
			return FALSE;

		if (is_numeric ($url))
		{
			try
			{
				$oSection = Business::singleton ()->getSection (Section::TCURRENT);

				$indexTo = Business::singleton ()->getAction (Action::TCURRENT)->getIndex ();

				if (trim ($indexTo) == '')
					return FALSE;

				$oAction = $oSection->getAction ($indexTo);

				$url = 'titan.php?target=body&toSection='. $oSection->getName () .'&toAction='. $oAction->getName () .'&itemId='. $url;
			}
			catch (Exception $e)
			{
				toLog ($e->getMessage ());

				return FALSE;
			}
		}

		try
		{
			$index = $this->getIndex ();

			$term = new Zend_Search_Lucene_Index_Term ($url, 'url');

			$ids  = $index->termDocs ($term);

			foreach ($ids as $id)
				$index->delete ($id);

			$index->commit ();
		}
		catch (Exception $e)
		{
			toLog (__ ('Impossible delete index search for [[1]]: [2].', $url, $e->getMessage ()));

			return FALSE;
		}
		catch (Zend_Search_Lucene_Search_QueryParserException $e)
		{
			toLog (__ ('Impossible delete index search for [[1]]: [2].', $url, $e->getMessage ()));

			return FALSE;
		}

		return TRUE;
	}

	public function saveFile ($ids, $indexAll = FALSE)
	{
		if (!$this->isActive ())
			return FALSE;

		if (!is_array ($ids))
			$ids = array ($ids);

		$sql = "SELECT _file.*, _user._name AS _user_name,
				to_char(_file._create_date, 'DD-MM-YYYY HH24:MI:SS') AS _date
				FROM _file LEFT JOIN _user ON _user._id = _file._user";

		if ($indexAll !== TRUE)
			if (sizeof ($ids))
				$sql .= " WHERE _file._id IN ('". implode ("', '", $ids) ."')";
			else
				return TRUE;

		$db = Database::singleton ();

		$sth = $db->prepare ($sql);

		$sth->execute ();

		$flag = FALSE;

		while ($obj = $sth->fetch (PDO::FETCH_OBJ))
		{
			$doc = FALSE;

			try
			{
				switch (trim ($obj->_mimetype))
				{
					case 'application/pdf':
						$doc = self::pdfToIndex ($obj);
						break;

					case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
						$doc = self::docxToIndex ($obj);
						break;

					case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
						$doc = self::pptxToIndex ($obj);
						break;

					case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
						$doc = self::xlsxToIndex ($obj);
						break;

					case 'text/plain':
						$doc = self::txtToIndex ($obj);
						break;

					case 'application/msword':
						$doc = self::docToIndex ($obj);
						break;

					case 'text/html':
						$doc = self::htmlToIndex ($obj);
						break;

					default:
						continue;
				}
			}
			catch (Exception $e)
			{
				toLog ($e->getMessage ());

				continue;
			}

			if (!is_object ($doc))
				continue;

			self::$index->addDocument ($doc);

			$flag = TRUE;
		}

		if ($flag)
		{
			set_time_limit (0);

			self::$index->commit ();

			self::$index->optimize ();
		}

		return TRUE;
	}

	public function deleteFile ($fileIds)
	{
		if (!$this->isActive ())
			return FALSE;

		if (!is_array ($fileIds))
			$ids = array ($fileIds);

		try
		{
			$index = $this->getIndex ();

			foreach ($fileIds as $trash => $fileId)
			{
				$url = 'titan.php?target=openFile&fileId='. $fileId;

				$term = new Zend_Search_Lucene_Index_Term ($url, 'url');

				$ids  = $index->termDocs ($term);

				foreach ($ids as $id)
					$index->delete ($id);
			}

			$index->commit ();

			$index->optimize ();
		}
		catch (Exception $e)
		{
			toLog (__ ('Impossible delete index search for [[1]]: [2].', $url, $e->getMessage ()));

			return FALSE;
		}

		return TRUE;
	}

	static private function pdfToIndex ($obj)
	{
		$file = 'file_'. str_pad ($obj->_id, 7, '0', STR_PAD_LEFT);

		$original = File::getFilePath ($obj->_id);

		if (!file_exists ($original))
			$original = File::getLegacyFilePath ($obj->_id);

		if (!file_exists ($original))
			throw new Exception (__ ('File dont exists [[1]].', $original));

		$path = Instance::singleton ()->getCachePath () .'text/';

		if (!file_exists ($path) && !@mkdir ($path, 0777))
			throw new Exception (__ ('Impossible to create [[1]].', $path));

		if (!file_exists ($path . $file) || !(int) filesize ($path . $file))
			@system ('pdftotext -enc Latin1 -nopgbrk -q '. $original .' '. $path . $file .' > '. $path .'log', $trash);

		if (!file_exists ($path . $file) || !(int) filesize ($path . $file))
			throw new Exception  (__ ('Impossible to create [[1]].', $path . $file));

		$content = $obj->_name ." \n\n". $obj->_date ." \n\n". $obj->_user_name ." \n\n". file_get_contents ($path . $file);

		$url = 'titan.php?target=openFile&fileId='. $obj->_id;

		$doc = new Zend_Search_Lucene_Document ();

		$doc->addField (Zend_Search_Lucene_Field::Keyword ('url', $url, 'iso-8859-1'));

		$doc->addField (Zend_Search_Lucene_Field::Keyword ('type', self::FILE, 'iso-8859-1'));

		$doc->addField (Zend_Search_Lucene_Field::Text ('local', __ ('File'), 'iso-8859-1'));

		$doc->addField (Zend_Search_Lucene_Field::Text ('content', $content, 'iso-8859-1'));

		return $doc;
	}

	static private function docxToIndex ($obj)
	{
		$file = 'file_'. str_pad ($obj->_id, 7, '0', STR_PAD_LEFT);

		$original = File::getFilePath ($obj->_id);

		if (!file_exists ($original))
			$original = File::getLegacyFilePath ($obj->_id);

		if (!file_exists ($original))
			throw new Exception (__ ('File dont exists [[1]].', $original));

		$doc = Zend_Search_Lucene_Document_Docx::loadDocxFile ($original, FALSE);

		$content = $obj->_name ." \n\n". $obj->_date ." \n\n". $obj->_user_name ." \n\n". $doc->body;

		$url = 'titan.php?target=openFile&fileId='. $obj->_id;

		$doc->addField (Zend_Search_Lucene_Field::Keyword ('url', $url, 'iso-8859-1'));

		$doc->addField (Zend_Search_Lucene_Field::Keyword ('type', self::FILE, 'iso-8859-1'));

		$doc->addField (Zend_Search_Lucene_Field::Text ('local', __ ('File'), 'iso-8859-1'));

		$doc->addField (Zend_Search_Lucene_Field::Text ('content', $content, 'iso-8859-1'));

		return $doc;
	}

	static private function pptxToIndex ($obj)
	{
		$file = 'file_'. str_pad ($obj->_id, 7, '0', STR_PAD_LEFT);

		$original = File::getFilePath ($obj->_id);

		if (!file_exists ($original))
			$original = File::getLegacyFilePath ($obj->_id);

		if (!file_exists ($original))
			throw new Exception (__ ('File dont exists [[1]].', $original));

		$doc = Zend_Search_Lucene_Document_Pptx::loadPptxFile ($original, FALSE);

		$content = $obj->_name ." \n\n". $obj->_date ." \n\n". $obj->_user_name ." \n\n". $doc->body;

		$url = 'titan.php?target=openFile&fileId='. $obj->_id;

		$doc->addField (Zend_Search_Lucene_Field::Keyword ('url', $url, 'iso-8859-1'));

		$doc->addField (Zend_Search_Lucene_Field::Keyword ('type', self::FILE, 'iso-8859-1'));

		$doc->addField (Zend_Search_Lucene_Field::Text ('local', __ ('File'), 'iso-8859-1'));

		$doc->addField (Zend_Search_Lucene_Field::Text ('content', $content, 'iso-8859-1'));

		return $doc;
	}

	static private function xlsxToIndex ($obj)
	{
		$file = 'file_'. str_pad ($obj->_id, 7, '0', STR_PAD_LEFT);

		$original = File::getFilePath ($obj->_id);

		if (!file_exists ($original))
			$original = File::getLegacyFilePath ($obj->_id);

		if (!file_exists ($original))
			throw new Exception (__ ('File dont exists [[1]].', $original));

		$doc = Zend_Search_Lucene_Document_Xlsx::loadXlsxFile ($original, FALSE);

		$content = $obj->_name ." \n\n". $obj->_date ." \n\n". $obj->_user_name ." \n\n". $doc->body;

		$url = 'titan.php?target=openFile&fileId='. $obj->_id;

		$doc->addField (Zend_Search_Lucene_Field::Keyword ('url', $url, 'iso-8859-1'));

		$doc->addField (Zend_Search_Lucene_Field::Keyword ('type', self::FILE, 'iso-8859-1'));

		$doc->addField (Zend_Search_Lucene_Field::Text ('local', __ ('File'), 'iso-8859-1'));

		$doc->addField (Zend_Search_Lucene_Field::Text ('content', $content, 'iso-8859-1'));

		return $doc;
	}

	static private function txtToIndex ($obj)
	{
		$file = 'file_'. str_pad ($obj->_id, 7, '0', STR_PAD_LEFT);

		$original = File::getFilePath ($obj->_id);

		if (!file_exists ($original))
			$original = File::getLegacyFilePath ($obj->_id);

		if (!file_exists ($original))
			throw new Exception (__ ('File dont exists [[1]].', $original));

		$content = $obj->_name ." \n\n". $obj->_date ." \n\n". $obj->_user_name ." \n\n". file_get_contents ($original);

		$url = 'titan.php?target=openFile&fileId='. $obj->_id;

		$doc = new Zend_Search_Lucene_Document ();

		$doc->addField (Zend_Search_Lucene_Field::Keyword ('url', $url, 'iso-8859-1'));

		$doc->addField (Zend_Search_Lucene_Field::Keyword ('type', self::FILE, 'iso-8859-1'));

		$doc->addField (Zend_Search_Lucene_Field::Text ('local', __ ('File'), 'iso-8859-1'));

		$doc->addField (Zend_Search_Lucene_Field::Text ('content', $content, 'iso-8859-1'));

		return $doc;
	}

	static private function docToIndex ($obj)
	{
		$file = 'file_'. str_pad ($obj->_id, 7, '0', STR_PAD_LEFT);

		$original = File::getFilePath ($obj->_id);

		if (!file_exists ($original))
			$original = File::getLegacyFilePath ($obj->_id);

		if (!file_exists ($original))
			throw new Exception (__ ('File dont exists [[1]].', $original));

		$path = Instance::singleton ()->getCachePath () .'text/';

		if (!file_exists ($path) && !@mkdir ($path, 0777))
			throw new Exception (__ ('Impossible to create [[1]].', $path));

		if (!file_exists ($path . $file) || !(int) filesize ($path . $file))
			@system ('antiword '. $original .' 1> '. $path . $file .' 2> '. $path .'log', $trash);

		if (!file_exists ($path . $file) || !(int) filesize ($path . $file))
			throw new Exception  (__ ('Impossible to create [[1]].', $path . $file));

		$content = $obj->_name ." \n\n". $obj->_date ." \n\n". $obj->_user_name ." \n\n". file_get_contents ($path . $file);

		$url = 'titan.php?target=openFile&fileId='. $obj->_id;

		$doc = new Zend_Search_Lucene_Document ();

		$doc->addField (Zend_Search_Lucene_Field::Keyword ('url', $url, 'iso-8859-1'));

		$doc->addField (Zend_Search_Lucene_Field::Keyword ('type', self::FILE, 'iso-8859-1'));

		$doc->addField (Zend_Search_Lucene_Field::Text ('local', __ ('File'), 'iso-8859-1'));

		$doc->addField (Zend_Search_Lucene_Field::Text ('content', $content, 'iso-8859-1'));

		return $doc;
	}

	static private function htmlToIndex ($obj)
	{
		$file = 'file_'. str_pad ($obj->_id, 7, '0', STR_PAD_LEFT);

		$original = File::getFilePath ($obj->_id);

		if (!file_exists ($original))
			$original = File::getLegacyFilePath ($obj->_id);

		if (!file_exists ($original))
			throw new Exception (__ ('File dont exists [[1]].', $original));

		$doc = Zend_Search_Lucene_Document_Html::loadHTMLFile ($original, FALSE);

		$content = $obj->_name ." \n\n". $obj->_date ." \n\n". $obj->_user_name ." \n\n". $doc->body;

		$url = 'titan.php?target=openFile&fileId='. $obj->_id;

		$doc->addField (Zend_Search_Lucene_Field::Keyword ('url', $url, 'iso-8859-1'));

		$doc->addField (Zend_Search_Lucene_Field::Keyword ('type', self::FILE, 'iso-8859-1'));

		$doc->addField (Zend_Search_Lucene_Field::Text ('local', __ ('File'), 'iso-8859-1'));

		$doc->addField (Zend_Search_Lucene_Field::Text ('content', $content, 'iso-8859-1'));

		return $doc;
	}
}
