<?
if (!isset ($_GET ['id']) || trim ($_GET ['id']) == '' || !is_numeric ($_GET ['id']) || !isset ($_GET ['pk']) || trim ($_GET ['pk']) == '')
	die ('Invalid parameters!');

require Instance::singleton ()->getCorePath () .'extra/QRCode/qrlib.php';

$_qr = QR_CACHE_DIR . $_GET ['id'] .'-'. $_GET ['pk'] .'.png';

QRcode::png ($_GET ['id'] .'#'. $_GET ['pk'], $_qr, QR_ECLEVEL_H, 12);
?>
<html><body style="margin: 0px; padding: 0px;"><img src="<?= $_qr ?>" border="0" /></body></html>