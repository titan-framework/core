<?
header ('Content-Type: application/xml');

echo '<?xml version="1.0" encoding="UTF-8" ?>';
?>
<rss version="2.0">
	<channel>
		<title><?= $section->getLabel () ?></title>
		<description><?= $section->getDescription () ?></description>
		<link><?= Instance::singleton ()->getUrl () ?></link>
		
		<?= $_OUTPUT ['SECTION'] ?>
		
	</channel>
</rss>