<?
$form =& Form::singleton ('view.xml', 'all.xml');

if (!$form->load ($itemId))
	throw new Exception (__('Unable to load the data of the item!'));
?>