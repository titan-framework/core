<?php

if ($field->isEmpty ())
	return NULL;

return '{ "'. number_format ($field->getLatitude (), 6, '.', '') .'", "'. number_format ($field->getLongitude (), 6, '.', '') .'" }';