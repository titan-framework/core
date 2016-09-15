<?php
class DocumentBadge extends Pdf
{
	function Footer ()
	{}
}

$pdf = new DocumentBadge ('P', 'mm', array (118, 79));

$pdf->SetSubject ($_label);
$pdf->SetTitle ($_label);
$pdf->SetAuthor (User::singleton ()->getName ());
$pdf->SetCreator (Instance::singleton ()->getName ());

$pdf->AliasNbPages ();

$pdf->SetDisplayMode (100);

$pdf->AddPage ();

$pdf->SetMargins (0, 0, 0);

$pdf->SetXY (0, 0);

$image = $_doc->getField ('_BACK_')->getValue ();

$archive = Archive::singleton ();

if ((int) $image && isset ($files [$image]))
{
	$extension = strtoupper ($archive->getExtensionByMime ($files [$image]));
	
	if (in_array ($extension, array ('JPG', 'JPEG', 'PNG', 'GIF')))
		$pdf->Image (Archive::singleton ()->getFilePath ($image), 0, 0, 118, 79, $extension);
}

/* Content */

$pdf->SetTextColor (255, 255, 255);

$pdf->SetXY (0, 40);

$pdf->SetFont ('Helvetica', 'B', 18);

$pdf->Cell (0, 6, Form::toText ($_doc->getField ('_LINE_1_')), 0, 1, 'C');

$pdf->SetXY (0, 50);

$pdf->SetFont ('Helvetica', 'BI', 12);

$pdf->Cell (0, 5, Form::toText ($_doc->getField ('_LINE_2_')), 0, 1, 'C');

/* QR Code */

$pdf->AddPage ();

$pdf->SetAutoPageBreak (FALSE, 0);

$pdf->SetXY (0, 0);

if (file_exists ($_qr))
	$pdf->Image ($_qr, 24, 4, 70, 70, 'PNG');

$pdf->Close ();
?>
