<?
$form =& Form::singleton ('create.xml');

$action = $form->goToAction ('fail');

if (!isset ($_FILES) || !sizeof ($_FILES))
	throw new Exception ('Não foi escolhido nenhum arquivo para efetuar <i>upload</i>!');

$files = array ();
foreach ($_FILES as $trash => $file)
	if ($file ['size'] && !$file ['error'])
		$files [] = $file;

if (!sizeof ($files))
	throw new Exception ('Não foi escolhido nenhum arquivo para efetuar <i>upload</i> ou houve erro no envio dos dados!');

$archive = Archive::singleton ();

$db = Database::singleton ();

$success = array ();

foreach ($files as $trash => $file)
{
	$fileTemp = $file ['tmp_name'];
	$fileSize = $file ['size'];
	$fileType = $file ['type'];
	$fileName = $file ['name'];
	
	try
	{
		if ($fileType == 'application/save' && !($fileType = $archive->getMimeByExtension (array_pop (explode ('.', $fileName)))))
			throw new Exception ('O arquivo ['. $fileName .'] não é aceito pelo sistema ('. $fileType .')!');
		
		if (!$archive->isAcceptable ($fileType))
			throw new Exception ('O arquivo ['. $fileName .'] não é aceito pelo sistema ('. $fileType .')!');
	}
	catch (Exception $e)
	{
		$message->addWarning ($e->getMessage ());
		
		continue;
	}
	
	try
	{
		$db->beginTransaction ();
		
		$fileId = Database::nextId ('_file', '_id');
		
		$sth = $db->prepare ("	INSERT INTO _file (_id, _name, _mimetype, _size, _user) 
								VALUES (". $fileId .", '". $fileName ."', '". $fileType ."', ". $fileSize .", '". User::singleton ()->getId () ."')");
		
		$sth->execute ();
		
		if (!move_uploaded_file ($fileTemp, Archive::singleton ()->getDataPath () . 'file_'. str_pad ($fileId, 7, '0', STR_PAD_LEFT)))
			throw new Exception ('O arquivo ['. $fileName .'] não pode ser copiado na pasta ['. Archive::singleton ()->getDataPath () .'].');
		
		$db->commit ();
		
		$success [$fileId] = $fileName;
	}
	catch (Exception $e)
	{
		$db->rollBack ();
		
		$message->addWarning ($e->getMessage ());
	}
	catch (PDOException $e)
	{
		$db->rollBack ();
		
		$warning = 'O arquivo ['. $fileName .'] não pôde ser inserido no BD.';
		
		if (Instance::singleton ()->onDebugMode ())
			$warning .= ' ['. $e->getMessage () .']';
		else
			toLog ($e->getMessage ());
		
		$message->addWarning ($warning);
	}
}

if (sizeof ($success))
{
	Lucene::singleton ()->saveFile (array_keys ($success));
	
	$message->addMessage ('O(s) seguinte(s) arquivo(s) foi(ram) enviado(s) com sucesso: '. implode (', ', $success) .'.');

	Log::singleton ()->add ('Novo(s) arquivo(s) enviado(s) ao sistema: '. implode (', ', $success) .'.');
}

$action = $form->goToAction ('success');
?>