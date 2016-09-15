<?php
while ($view->getItem ())
{
	?>
	<item>
		<title><?= Form::toHtml ($view->getField ('_TITLE_')) ?></title>
		<description><?= Form::toHtml ($view->getField ('_TITLE_')) ?></description>
		<link><?= $view->getLink (TRUE) ?></link>
		<guid isPermaLink="false"><?= randomHash (12) ?></guid>
	</item>
	<?php
}
?>