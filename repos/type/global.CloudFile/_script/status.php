<?php

if (isset ($_GET ['unique']) && isset ($_SESSION ['_CLOUD_FILE_STATUS_'][$_GET ['unique']]))
	echo $_SESSION ['_CLOUD_FILE_STATUS_'][$_GET ['unique']];
else
	echo __ ('Wait! Sending...');