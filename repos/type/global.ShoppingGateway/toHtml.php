<?php
if (!Shopping::isActive ())
	return __ ('The Shopping Module in Titan Framework must be enable!');

$gw = Shopping::singleton ()->getGateway ($field->getValue ());

return $gw ['account'] .' '. __ ('in') .' '. $gw ['driver'];
?>