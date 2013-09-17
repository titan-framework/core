<?php

if (Alert::isActive ())
	Alert::sendMobileNotification ();