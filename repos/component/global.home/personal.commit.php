<?
$user = User::singleton ();

$form =& Form::singleton ('../'. $user->getType ()->getName () .'/'. $user->getType ()->getModify ());

$action = $form->goToAction ('fail');

if (!$form->recovery ())
	throw new Exception ('Não foi possível recuperar os dados submetidos!');

if (!$form->save ($user->getId ()))
	throw new Exception ('Não foi possível salvar os dados submetidos!');

if ($user->getType ()->useLdap ())
	if (!$form->saveOnLdap ($user->getType ()->getLdap (), FALSE, $user->getLogin ()))
		throw new Exception ('Não foi possível salvar os dados submetidos no servidor LDAP!');

User::singleton ()->update ();

Alert::remove ('_UPDATE_PROFILE_', User::singleton ()->getId ());

$action = $form->goToAction ('success');

$message->addMessage ('Dados modificados com sucesso!');

Log::singleton ()->add ('PROFILE', $form->getResume ($itemId));
?>
