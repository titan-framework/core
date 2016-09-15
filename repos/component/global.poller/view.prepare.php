<?php
$form =& Form::singleton ('view.xml', 'all.xml');

if (!$form->load ($itemId))
	throw new Exception ('Não foi possível carregar os dados do item!');

$db = Database::singleton ();

$sql = "SELECT * FROM ". $form->getTable () ."_answer WHERE _poller = '". $itemId ."' ORDER BY _order";

$sth = $db->prepare ($sql);

$sth->execute ();

$answer = array ();
$total = 0;
while ($obj = $sth->fetch (PDO::FETCH_OBJ))
{
	$answer [$obj->_order]['_LABEL_'] = $obj->_label;
	$answer [$obj->_order]['_VOTES_'] = $obj->_votes;
	
	$total += (int) $obj->_votes;
}
?>