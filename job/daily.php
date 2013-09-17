<?php

if (Alert::isActive ())
{
	Alert::singleton ()->sendMail ();
	
	Alert::garbageCollector ();
}

if (Backup::singleton ()->isActive ())
	Backup::clear ();