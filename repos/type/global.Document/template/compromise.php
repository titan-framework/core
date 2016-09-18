<?php
class DocumentCompromise extends Pdf
{
	protected $foot = array ('', '');

	function setFoot ($top, $bottom)
	{
		$this->foot = array ($top, $bottom);
	}

	function Footer ()
	{
		$this->SetY (-28);

		global $_label, $_version, $_create, $_item, $_hash, $_file;

		$this->SetFont ('Helvetica', 'B', 8);

		$this->MultiCell (80, 4, $_label, 0, 'L', FALSE);

		$this->Cell (80, 4, __ ('Page') .' '. $this->PageNo () .' '. __ ('of') .' {nb}', 0, 0, 'L');

		$this->SetXY (110, -28);

		$this->Cell (0, 4, __ ('Control Number') .': '. str_pad ($_file, 7, '0', STR_PAD_LEFT), 'LTR', 0, 'R');

		$this->SetXY (110, -24);

		$this->Cell (0, 4, __ ('Version of Document') .': '. $_version .' ('. __ ('of') .' '. $_create .')', 'LR', 0, 'R');

		$this->SetXY (110, -20);

		$this->Cell (0, 4, __ ('Authentication') .': '. Document::genAuth ($_hash), 'LBR', 0, 'R');

		$this->SetXY (20, -14);

		$this->SetFont('Helvetica', 'I', 7);

		$this->MultiCell (60, 3, $this->foot [0], 0, 'L');

		$this->SetXY (90, -14);

		$this->MultiCell (0, 3, $this->foot [1], 0, 'R');

		$this->SetX (20);
	}

	function Assigns ($a1, $a2, $a3, $a4, $t1, $t2)
	{
		$y = $this->GetY () + 20;

		$this->SetFont ('Helvetica', '', 10);

		if ($y + 30 > 270)
		{
			$this->AddPage ();

			$y = $this->GetY ();
		}

		/* First Assign */

		$this->SetXY (20, $y);

		$this->Cell (70, 4, '', 'B', 0, 'C');

		$this->SetXY (20, $y + 5);

		$this->MultiCell (70, 4, $a1, 0, 'C');

		/* Second Assign */

		$this->SetXY (-90, $y);

		$this->Cell (70, 4, '', 'B', 0, 'C');

		$this->SetXY (-90, $y + 5);

		$this->MultiCell (70, 4, $a2, 0, 'C');

		$y += 30;

		if ($y + 30 > 270)
		{
			$this->AddPage ();

			$y = $this->GetY ();
		}

		/* Third Assign */

		$this->SetXY (20, $y);

		$this->Cell (70, 4, '', 'B', 0, 'C');

		$this->SetXY (20, $y + 5);

		$this->MultiCell (70, 4, $a3, 0, 'C');

		/* Forth Assign */

		$this->SetXY (-90, $y);

		$this->Cell (70, 4, '', 'B', 0, 'C');

		$this->SetXY (-90, $y + 5);

		$this->MultiCell (70, 4, $a4, 0, 'C');


		$y += 40;

		if ($y + 50 > 270)
		{
			$this->AddPage ();

			$y = $this->GetY ();
		}

		$this->SetXY (20, $y);

		$this->SetFont ('Helvetica', 'B', 10);

		$this->Cell (70, 4, __ ('WITNESSES'), 0, 0, 'L');

		$y += 20;

		$this->SetFont ('Helvetica', '', 10);

		/* First Attestant */

		$this->SetXY (20, $y);

		$this->Cell (70, 4, '', 'B', 0, 'C');

		$this->SetXY (20, $y + 5);

		$this->MultiCell (70, 4, $t1, 0, 'C');

		/* Second Attestant */

		$this->SetXY (-90, $y);

		$this->Cell (70, 4, '', 'B', 0, 'C');

		$this->SetXY (-90, $y + 5);

		$this->MultiCell (70, 4, $t2, 0, 'C');

		$this->SetXY (20, $y + 30);
	}
}

$pdf = new DocumentCompromise ('P', 'mm', 'A4');

$pdf->SetSubject ($_label);
$pdf->SetTitle ($_label);
$pdf->SetAuthor (User::singleton ()->getName ());
$pdf->SetCreator (Instance::singleton ()->getName ());

$pdf->AliasNbPages ();

$pdf->SetDisplayMode (100);

$pdf->setFoot (Form::toText ($_doc->getField ('_FOOTER_1_')), Form::toText ($_doc->getField ('_FOOTER_2_')));

$pdf->SetAutoPageBreak (TRUE, 35);

$pdf->AddPage ();

$x = 20;
$y = 10;

$pdf->SetMargins (20, 40, 20);

$pdf->SetXY ($x, $y);

$image = $_doc->getField ('_IMAGE_')->getValue ();

$archive = Archive::singleton ();

if ((int) $image && isset ($files [$image]))
{
	$extension = strtoupper ($archive->getExtensionByMime ($files [$image]));

	if (in_array ($extension, array ('JPG', 'JPEG', 'PNG', 'GIF')))
		$pdf->Image (Archive::singleton ()->getFilePath ($image), 65, NULL, 0, 0, $extension);
}

$y = $pdf->GetY () + 5;

$pdf->SetXY ($x + 65, $y);

$pdf->SetFont ('Helvetica', '', 12);

$pdf->MultiCell (0, 5, strtoupper (Form::toText ($_doc->getField ('_HEADER_'))));

$y = $pdf->GetY () + 10;

$pdf->SetXY ($x, $y);

$pdf->SetFont ('Helvetica', '', 10);

$pdf->MultiCell (0, 4, Form::toText ($_doc->getField ('_TEXT_')));

$pdf->Assigns (Form::toText ($_doc->getField ('_ASSIGN_1_')), Form::toText ($_doc->getField ('_ASSIGN_2_')), Form::toText ($_doc->getField ('_ASSIGN_3_')), Form::toText ($_doc->getField ('_ASSIGN_4_')), Form::toText ($_doc->getField ('_ATTESTANT_1_')), Form::toText ($_doc->getField ('_ATTESTANT_2_')));

if ($_validate && file_exists ($_qr))
{
	$pdf->SetFillColor (221, 221, 221);

	$pdf->SetFont ('Helvetica', 'B', 8);

	$y = $pdf->GetY ();

	if ($y + 40 > 270)
	{
		$pdf->AddPage ();

		$y = $pdf->GetY ();
	}

	$pdf->SetXY ($x, $y + 4);

	$pdf->MultiCell (120, 4, __ ('Warning! The authenticity of this [1] may be verified by accessing the following Web address: [2]', trim ($_label), Instance::singleton ()->getUrl ()), 0, 'J', FALSE);

	$pdf->SetXY ($x, $y + 20);

	$pdf->Cell (120, 4, __ ('Control Number') .': '. str_pad ($_file, 7, '0', STR_PAD_LEFT), 'LTR', 0, 'L', TRUE);

	$pdf->SetXY ($x, $y + 24);

	$pdf->Cell (120, 4, __ ('Version of Document') .': '. $_version .' ('. __ ('of') .' '. $_create .')', 'LR', 0, 'L', TRUE);

	$pdf->SetXY ($x, $y + 28);

	$pdf->Cell (120, 4, __ ('Authentication') .': '. Document::genAuth ($_hash), 'LBR', 0, 'L', TRUE);

	$pdf->Image ($_qr, 150, $y, 40, 40, 'PNG');
}

$pdf->Close ();
?>
