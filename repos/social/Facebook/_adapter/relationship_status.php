<?php

switch ($value)
{
	default:
	case 'Single':
		return '_SINGL_';
		break;

	case 'In a relationship':
	case 'Engaged':
	case 'Married':
	case 'It\'s complicated':
	case 'In an open relationship':
	case 'Widowed':
	case 'In a civil union':
	case 'In a domestic partnership':
		return '_MARRI_';
		break;

	case 'Separated':
	case 'Divorced':
		return '_DIVOR_';
		break;
}
