<?php
$form =& Form::singleton ('delete.xml', 'view.xml', 'all.xml');

if (!$form->load ($itemId))
	throw new Exception (__ ('Unable to load data!'));
?>