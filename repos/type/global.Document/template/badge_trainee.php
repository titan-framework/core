<?
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

$pdf->SetAutoPageBreak (FALSE, 0);

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

$sql = "SELECT t.trainee, t.qr_auth, t.photo, f._mimetype
		FROM trainee.v_view_term t 
		LEFT JOIN _file f ON f._id = t.photo
		WHERE t.id = :id";

$sth = Database::singleton ()->prepare ($sql);

$sth->execute (array (':id' => $_item));

$qr = QR_CACHE_DIR .'null';

while ($obj = $sth->fetch (PDO::FETCH_OBJ))
{
	$content = $obj->trainee .'#'. $obj->qr_auth;
	
	$qr = QR_CACHE_DIR . md5 ($content) .'.png';
	
	QRcode::png ($content, $qr, 'H', 4, 0);
	
	if ((int) $obj->photo)
	{
		$extension = strtoupper ($archive->getExtensionByMime ($obj->_mimetype));
		
		if (in_array ($extension, array ('JPG', 'JPEG', 'PNG', 'GIF')))
			$pdf->Image (Archive::singleton ()->getFilePath ($obj->photo), 84, 0, 34, 34, $extension);
	}
}

/* Content */

$pdf->SetTextColor (255, 255, 255);

$pdf->SetXY (0, 40);

$pdf->SetFont ('Helvetica', 'B', 18);

$pdf->Cell (0, 6, Form::toText ($_doc->getField ('_LINE_1_')), 0, 1, 'C');

$pdf->SetXY (0, 50);

$pdf->SetFont ('Helvetica', 'BI', 12);

$pdf->Cell (0, 5, Form::toText ($_doc->getField ('_LINE_2_')), 0, 1, 'C');

$pdf->SetTextColor (0, 0, 0);

$pdf->SetXY (0, 60);

$pdf->SetFont ('Helvetica', 'BI', 12);

$pdf->Cell (0, 5, Form::toText ($_doc->getField ('_LINE_3_')), 0, 1, 'C');

$pdf->SetXY (0, 67);

$pdf->SetFont ('Helvetica', 'BI', 12);

$pdf->Cell (0, 5, Form::toText ($_doc->getField ('_LINE_4_')), 0, 1, 'C');

/* QR Code */

$pdf->AddPage ();

$pdf->SetAutoPageBreak (FALSE, 0);

$pdf->SetXY (0, 0);

if (file_exists ($qr))
	$pdf->Image ($qr, 24, 4, 70, 70, 'PNG');

$pdf->Close ();

@unlink ($qr);
?>
