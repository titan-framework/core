<?php

$_OUTPUT ['MENU'] = '';

$menuHeight = array ();

ob_start ();

try
{
	?>
	<div id="menuBox" style="display: block; height: 100%; left: -260px;">
		<div class="menuContainer">
			<?= implode ('', makeMenu ($menuHeight)) ?>
		</div>
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
