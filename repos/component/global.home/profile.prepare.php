<?php
$form =& Form::singleton ('../'. $user->getType ()->getName () .'/'. $user->getType ()->getModify ());

$itemId = $user->getId ();

if (!$form->load ($itemId))
	throw new Exception ('Não foi possível carregar os seus dados cadastrais!');
?>