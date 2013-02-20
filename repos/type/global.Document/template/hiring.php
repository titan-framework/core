<?
class DocumentHiring extends Pdf
{
	protected $foot = array ('', '');
	
	function setFooter ($top, $bottom)
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
	
	function Assigns ($a1, $a2, $a3, $a4)
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
		
		if (trim ($a3) != '')
		{
			$this->SetXY (20, $y);
			
			$this->Cell (70, 4, '', 'B', 0, 'C');
			
			$this->SetXY (20, $y + 5);
			
			$this->MultiCell (70, 4, $a3, 0, 'C');
		}
		
		/* Forth Assign */
		
		if (trim ($a4) != '')
		{
			$this->SetXY (-90, $y);
			
			$this->Cell (70, 4, '', 'B', 0, 'C');
			
			$this->SetXY (-90, $y + 5);
			
			$this->MultiCell (70, 4, $a4, 0, 'C');
		}
	}
}

$pdf = new DocumentHiring ('P', 'mm', 'A4');

$pdf->SetSubject ($_label);
$pdf->SetTitle ($_label);
$pdf->SetAuthor (User::singleton ()->getName ());
$pdf->SetCreator (Instance::singleton ()->getName ());

$pdf->AliasNbPages ();

$pdf->SetDisplayMode (100);

$pdf->setFooter (Form::toText ($_doc->getField ('_FOOTER_1_')), Form::toText ($_doc->getField ('_FOOTER_2_')));

$pdf->SetAutoPageBreak (FALSE, 0);

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

$pdf->SetXY ($x, $y);

$pdf->SetFont ('Helvetica', '', 12);

$pdf->Cell (0, 5, strtoupper (Form::toText ($_doc->getField ('_HEADER_'))), 0, 0, 'C');

$y = $pdf->GetY () + 10;

$pdf->SetXY ($x, $y);

$pdf->SetFillColor (221, 221, 221);

/* Request */

$pdf->SetFont ('Helvetica', 'I', 10);

$pdf->Cell (0, 5, strtoupper (Form::toText ($_doc->getField ('_REQUEST_HEADER_'))), 1, 1, 'L', TRUE);

$pdf->SetFont ('Helvetica', 'B', 10);

$pdf->MultiCell (0, 5, Form::toText ($_doc->getField ('_REQUEST_CONTENT_')), 1);

$y = $pdf->GetY () + 5;

$pdf->SetXY ($x, $y);

/* Unity */

$pdf->SetFont ('Helvetica', 'I', 10);

$pdf->Cell (0, 5, strtoupper (Form::toText ($_doc->getField ('_UNITY_HEADER_'))), 1, 1, 'L', TRUE);

$pdf->MultiCell (0, 5, Form::toText ($_doc->getField ('_UNITY_CONTENT_')), 1);

$y = $pdf->GetY () + 5;

$pdf->SetXY ($x, $y);

/* Trainee */

$pdf->Cell (0, 5, strtoupper (Form::toText ($_doc->getField ('_ID_HEADER_'))), 1, 1, 'L', TRUE);

$pdf->MultiCell (0, 5, Form::toText ($_doc->getField ('_ID_CONTENT_')), 1);

$y = $pdf->GetY () + 5;

$pdf->SetXY ($x, $y);

/* Period */

$pdf->Cell (0, 5, strtoupper (Form::toText ($_doc->getField ('_PERIOD_HEADER_'))), 1, 1, 'L', TRUE);

$pdf->MultiCell (0, 5, Form::toText ($_doc->getField ('_PERIOD_CONTENT_')), 1);

$y = $pdf->GetY () + 5;

$pdf->SetXY ($x, $y);

/* Supervisor */

$pdf->Cell (0, 5, strtoupper (Form::toText ($_doc->getField ('_SUPERVISOR_HEADER_'))), 1, 1, 'L', TRUE);

$pdf->MultiCell (0, 5, Form::toText ($_doc->getField ('_SUPERVISOR_CONTENT_')), 1);

$y = $pdf->GetY () + 5;

$pdf->SetXY ($x, $y);

/* Justify */

$pdf->Cell (0, 5, strtoupper (Form::toText ($_doc->getField ('_JUSTIFY_HEADER_'))), 1, 1, 'L', TRUE);

$pdf->MultiCell (0, 5, Form::toText ($_doc->getField ('_JUSTIFY_CONTENT_')), 1);

/* Auth and Feedback */

$pdf->AddPage ();
	
$x = 20;
$y = 10;

$pdf->SetXY ($x, $y);

$pdf->MultiCell (83, 5, strtoupper (Form::toText ($_doc->getField ('_AUTH_HEADER_'))), 1, 'L', TRUE);

$y1 = $pdf->GetY ();

$pdf->SetXY (-103, $y);

$pdf->MultiCell (0, 5, strtoupper (Form::toText ($_doc->getField ('_FEEDBACK_HEADER_'))), 1, 'L', TRUE);

$y2 = $pdf->GetY ();

$pdf->SetXY ($x, $y1);

$pdf->MultiCell (83, 5, Form::toText ($_doc->getField ('_AUTH_CONTENT_')), 'LR');

$y1 = $pdf->GetY ();

$pdf->SetXY (-103, $y2);

$pdf->SetFont ('Helvetica', 'B', 10);

$pdf->MultiCell (0, 5, Form::toText ($_doc->getField ('_FEEDBACK_CONTENT_')), 'LR');

$pdf->SetFont ('Helvetica', 'I', 10);

$y2 = $pdf->GetY ();

$str = "\n\n\n__________________________________\n";

$pdf->SetXY ($x, $y1);

$pdf->MultiCell (83, 5, $str . Form::toText ($_doc->getField ('_AUTH_ASSIGN_')), 'LBR', 'C');

$pdf->SetXY (-103, $y2);

$pdf->MultiCell (0, 5, $str . Form::toText ($_doc->getField ('_FEEDBACK_ASSIGN_')), 'LBR', 'C');

$pdf->Close ();
?>