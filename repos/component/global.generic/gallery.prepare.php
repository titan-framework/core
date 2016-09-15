<?php
$form =& Form::singleton ('all.xml', 'view.xml', 'create.xml', 'edit.xml');

$table = $form->getTable () .'_media';

$db = Database::singleton ();

$db = Database::singleton ();

$sth = $db->prepare ("SELECT _media FROM ". $table ." WHERE _item = '". $itemId ."' ORDER BY _order");

$sth->execute ();
?>