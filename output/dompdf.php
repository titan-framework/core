<?php

use Dompdf\Dompdf;

require Instance::singleton ()->getCorePath () .'assembly/breadcrumb.php';

require Instance::singleton ()->getCorePath () .'assembly/section.php';

require Instance::singleton ()->getCorePath () .'extra/htmlPurifier/HTMLPurifier.standalone.php';

$config = HTMLPurifier_Config::createDefault ();

$config->set ('Core.Encoding', 'UTF-8');

$config->set('HTML.Doctype', 'HTML 4.01 Strict');

if (!Instance::singleton ()->onDebugMode ())
{
	$path = Instance::singleton ()->getCachePath () .'purifier';

	if (!file_exists ($path) && !@mkdir ($path, 0777))
	{
		toLog ('Impossível criar diretório ['. $path .'].');

		$path = sys_get_temp_dir ();
	}

	$config->set ('Cache.SerializerPath', $path);
}
else
	$config->set ('Cache.DefinitionImpl', NULL);

$purifier = new HTMLPurifier ($config);

$path = Instance::singleton ()->getCachePath () .'pdf';

if (!file_exists ($path) && !@mkdir ($path, 0777))
{
	toLog ('Impossível criar diretório ['. $path .'].');

	$path = sys_get_temp_dir ();
}

$dompdf = new Dompdf ();

$dompdf->set_option ('tempDir', $path);

ob_start ();

include Instance::singleton ()->getCorePath () .'output/pdf.php';

$dompdf->loadHtml ($purifier->purify (ob_get_clean ()));

$dompdf->setPaper ('A4', 'landscape');

$dompdf->render ();

$dompdf->stream ('titan.pdf');
