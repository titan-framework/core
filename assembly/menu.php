<?php
$_OUTPUT ['MENU'] = '';

$menuHeight = array ();
	
ob_start ();

try
{
	?>
	<div id="menuBox" style="display: none; left: 0px; top: 0px;">
		<?= implode ('', makeMenu ()) ?>
	</div>
	<?php
	$_OUTPUT ['MENU'] = ob_get_clean ();
	
	$max = 0;
	foreach ($menuHeight as $key => $value)
		if ($value > $max) $max = $value;
	
	$menuHeight = $max;
}
catch (PDOException $e)
{
	ob_end_clean ();
	
	$message->addWarning ('Não foi possível carregar as seções do sistema: '. $e->getMessage ());
}
?>