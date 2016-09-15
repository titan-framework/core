<div style="font: 12px 'Courier New', Courier, monospace; margin: 0px 10px;">
<?php
$log = Log::singleton ();

$log->loadActivities ();

echo nl2br ($log->getContent ($itemId));
?>
</div>