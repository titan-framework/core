<?php
$form =& Form::singleton ('edit.xml', 'all.xml');

$action = $form->goToAction ('fail');

if (!$form->recovery ())
	throw new Exception ('Não foi possível recuperar os dados submetidos!');

if (!$form->save ($itemId, FALSE))
	throw new Exception ('Não foi possível salvar os dados submetidos!');

$action = $form->goToAction ('success');

$message->addMessage ('Dados modificados com sucesso!');

if (isset ($_POST["_SEND_"]) && (int) $_POST["_SEND_"])
{
	try
	{
		$user = User::singleton ();
		
		$msg  = $form->getField ('_RESPONSE_')->getValue ();
		$msg .= "\n\nVocê escreveu:\n> ";
		$msg .= chunk_split ($form->getField ('_MSG_')->getValue (), 50, "\n> ");
		
		if (!@mail ($form->getField ('_EMAIL_')->getValue (), '=?utf-8?B?'. base64_encode ('Re: '. $form->getField ('_SUBJECT_')->getValue ()) .'?=', $msg, "From: ". $user->getName () ." <". Instance::singleton ()->getEmail () .">\r\nReply-To: ". $user->getEmail () ."\r\nContent-Type: text/plain; charset=utf-8"))
			throw new Exception ('Impossível enviar a mensagem. Tente novamente mais tarde.');
		
		$db = Database::singleton ();
		
		$sth = $db->prepare ("UPDATE _contact SET _responser = '". $user->getId () ."', _response_date = now(), _responsed = '1' WHERE _id = '". $itemId ."'");
		
		$sth->execute ();
		
		$message->addMessage ('E-mail enviado com sucesso!');
		
		Log::singleton ()->add ('REPLY', $form->getResume ($itemId));
	}
	catch (Exception $e)
	{
		$message->addWarning ($e->getMessage ());
	}
	catch (PDOException $e)
	{
		$message->addWarning ($e->getMessage ());
	}
}
?>