<?
class DocumentCertificate extends Pdf
{
	function Footer ()
	{}
}

$pdf = new DocumentCertificate ('P', 'mm', array (200, 200));

$pdf->SetSubject ($_label);
$pdf->SetTitle ($_label);
$pdf->SetAuthor (User::singleton ()->getName ());
$pdf->SetCreator (Instance::singleton ()->getName ());

$pdf->AliasNbPages ();

$pdf->SetDisplayMode (100);

$pdf->AddPage ();

$x = 25;
$y = 25;

$pdf->SetMargins (25, 25, 25);

$pdf->SetXY ($x, $y);

$image = $_doc->getField ('_BACK_')->getValue ();

$archive = Archive::singleton ();

if ((int) $image && isset ($files [$image]))
{
	$extension = strtoupper ($archive->getExtensionByMime ($files [$image]));
	
	if (in_array ($extension, array ('JPG', 'JPEG', 'PNG', 'GIF')))
		$pdf->Image (Archive::singleton ()->getFilePath ($image), 0, 0, 200, 200, $extension);
}

/* Line 1 */

$y = $pdf->GetY () + 30;

$pdf->SetXY ($x, $y);

$pdf->SetFont ('Helvetica', 'I', 12);

$pdf->SetTextColor (21, 90, 158);

$pdf->MultiCell (0, 5, Form::toText ($_doc->getField ('_VERB_')), 0, 'C');

/* Line 2 */

$y = $pdf->GetY () + 2;

$pdf->SetXY ($x, $y);

$pdf->SetFont ('Helvetica', 'BI', 16);

$pdf->SetTextColor (0, 0, 0);

$pdf->MultiCell (0, 6, Form::toText ($_doc->getField ('_NAME_')), 0, 'C');

/* Line 3 */

$y = $pdf->GetY () + 3;

$pdf->SetXY ($x, $y);

$pdf->SetFont ('Helvetica', 'I', 12);

$pdf->SetTextColor (21, 90, 158);

$pdf->MultiCell (0, 5, Form::toText ($_doc->getField ('_EVENT_CALL_')), 0, 'C');

/* Line 4 */

$y = $pdf->GetY () + 2;

$pdf->SetXY ($x, $y);

$pdf->SetFont ('Helvetica', 'BI', 16);

$pdf->SetTextColor (0, 0, 0);

$pdf->MultiCell (0, 6, Form::toText ($_doc->getField ('_EVENT_')), 0, 'C');

/* Line 5 */

$y = $pdf->GetY () + 3;

$pdf->SetXY ($x, $y);

$pdf->SetFont ('Helvetica', 'I', 12);

$pdf->SetTextColor (21, 90, 158);

$pdf->MultiCell (0, 5, Form::toText ($_doc->getField ('_QUALIFYING_CALL_')), 0, 'C');

/* Line 6 */

$y = $pdf->GetY () + 2;

$pdf->SetXY ($x, $y);

$pdf->SetFont ('Helvetica', 'BI', 16);

$pdf->SetTextColor (0, 0, 0);

$pdf->MultiCell (0, 6, Form::toText ($_doc->getField ('_QUALIFYING_')), 0, 'C');

/* Line 7 */

$y = $pdf->GetY () + 3;

$pdf->SetXY ($x, $y);

$pdf->SetFont ('Helvetica', 'I', 12);

$pdf->SetTextColor (21, 90, 158);

$pdf->MultiCell (0, 5, Form::toText ($_doc->getField ('_PERIOD_CALL_')), 0, 'C');

/* Line 8 */

$y = $pdf->GetY () + 2;

$pdf->SetXY ($x, $y);

$pdf->SetFont ('Helvetica', 'BI', 14);

$pdf->SetTextColor (0, 0, 0);

$pdf->MultiCell (0, 6, Form::toText ($_doc->getField ('_PERIOD_')), 0, 'C');

/* Register */

$pdf->AddPage ();

$x = 10;
$y = 10;

$pdf->SetMargins (10, 10, 10);

$pdf->SetAutoPageBreak (TRUE, 10);

$pdf->SetXY ($x, $y);

$pdf->SetFont ('Helvetica', 'B', 12);

$pdf->Cell (0, 5, strtoupper (Form::toText ($_doc->getField ('_HEADER_'))), 0, 1, 'C');

$y = $pdf->GetY () + 5;

$pdf->SetXY ($x, $y);

$pdf->SetFont ('Helvetica', '', 12);

$pdf->MultiCell (0, 5, Form::toText ($_doc->getField ('_REGISTER_')), 0, 'L');

/* Assigns */

$a1 = Form::toText ($_doc->getField ('_ASSIGN_1_'));

if (trim ($a1) != '')
{
	$pdf->SetFont ('Helvetica', '', 10);
	
	$y = $pdf->GetY () + 10;
	
	$pdf->SetXY ($x, $y);
	
	$pdf->Cell (85, 4, '', 'B', 1, 'C');
	
	$pdf->MultiCell (85, 4, $a1, 0, 'C');
	
	$a2 = Form::toText ($_doc->getField ('_ASSIGN_2_'));

	if (trim ($a2) != '')
	{
		$pdf->SetXY (-95, $y);
		
		$pdf->Cell (85, 4, '', 'B', 0, 'C');
		
		$pdf->SetXY (-95, $y + 5);
		
		$pdf->MultiCell (85, 4, $a2, 0, 'C');
	}
}

/* Activity */

$y = $pdf->GetY () + 10;

$pdf->SetXY ($x, $y);

$pdf->SetFont ('Helvetica', 'B', 12);

$pdf->Cell (0, 5, strtoupper (Form::toText ($_doc->getField ('_ACTIVITY_HEADER_'))), 0, 1, 'C');

if (Database::tableExists ('trainee.trainee_activity'))
{
	$y = $pdf->GetY () + 2;
	
	$pdf->SetXY ($x, $y);
	
	$pdf->SetFont ('Helvetica', '', 10);
	
	$sql = "SELECT description FROM trainee.v_all_activity WHERE trainee = '". $_item ."'";
	
	$sth = Database::singleton ()->prepare ($sql);
	
	$sth->execute ();
	
	while ($obj = $sth->fetch (PDO::FETCH_OBJ))
		$pdf->MultiCell (0, 5, $obj->description, 1);
}

/* Authentication */

if ($pdf->GetY () > 160)
	$pdf->AddPage ();

$pdf->SetFont ('Helvetica', 'B', 8);

if ($_validate)
{
	$pdf->SetXY ($x, -38);
	
	$pdf->MultiCell (135, 4, __ ('Warning! The authenticity of this [1] may be verified by accessing the following Web address: [2]', trim ($_label), Instance::singleton ()->getUrl ()), 0, 'J', FALSE);
}

$pdf->SetFillColor (221, 221, 221);

$pdf->SetXY ($x, -22);

$pdf->Cell (135, 4, __ ('Control Number') .': '. str_pad ($_file, 7, '0', STR_PAD_LEFT), 'LTR', 0, 'L', TRUE);

$pdf->SetXY ($x, -18);

$pdf->Cell (135, 4, __ ('Version of Document') .': '. $_version .' ('. __ ('of') .' '. $_create .')', 'LR', 0, 'L', TRUE);

$pdf->SetXY ($x, -14);

$pdf->Cell (135, 4, __ ('Authentication') .': '. Document::genAuth ($_hash), 'LBR', 0, 'L', TRUE);

if ($_validate && file_exists ($_qr))
	$pdf->Image ($_qr, 155, 155, 40, 40, 'PNG');

$pdf->Close ();
?>
