<?
if (isset ($search) && is_object ($search))
{
	?>
	<div id="idSearchParams" style="display:<?= $search->isEmpty () ? 'none' : '' ?>;">
		<form id="searchParams" action="<?= $_SERVER['PHP_SELF'] .'?target=body&toSection='. $section->getName () .'&toAction='. $action->getName () .'&itemId='. $itemId ?>" method="post">
			<input type="hidden" name="search" value="<?= Search::TCLEAR ?>" />
		</form>
		<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
			<tr>
				<td class="cTitle"><?=__ ('Search Criteria Selected') ?></td>
				<td class="cClear">
					<a href="#" onclick="JavaScript: document.getElementById ('searchParams').submit ();"><?= __ ('Clean Criteria') ?></a>
					&nbsp;|&nbsp;
					<a href="#" onclick="JavaScript: showSearch ();"><?= __ ('Change Criteria') ?></a>
				</td>
			</tr>
		</table>
		<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0" style="border: #36817C 1px solid; border-top-width: 3px;">
			<tr height="5px"><td></td></tr>
			<?
			$backColor = 'FFFFFF';
			while ($field = $search->getField ())
			{
				if ($field->isEmpty ())
					continue;
	
				$backColor = $backColor == 'FFFFFF' ? 'F4F4F4' : 'FFFFFF';
				?>
				<tr height="18px" style="background-color: #<?= $backColor ?>;">
					<td width="20%" nowrap style="text-align: right;"><b><?= $field->getLabel () ?>:</b></td>
					<td width="80%" <?= $search->isBlocked ($field) ? 'style="color: #900;"' : '' ?>><?= Form::toHtml ($field) ?></td>
				</tr>
				<tr height="2px"><td colspan="2"></td></tr>
				<?
			}
			?>
			<tr height="5px"><td></td></tr>
		</table>
	</div>
	<div id="idSearch" style="display: none;">
		<form action="<?= $_SERVER['PHP_SELF'] .'?target=body&toSection='. $section->getName () .'&toAction='. $action->getName () .'&itemId='. $itemId ?>" method="post">
			<input type="hidden" name="search" value="<?= Search::TSEARCH ?>" />
			<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
				<tr>
					<td colspan="3" class="cTitle"><?=__ ('Search Itens') ?></td>
				</tr>
			</table>
			<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0" style="border: #36817C 1px solid; border-top-width: 3px;">
				<tr height="5px"><td colspan="3"></td></tr>
				<?
				$backColor = 'FFFFFF';
				while ($field = $search->getField ())
				{
					$backColor = $backColor == 'FFFFFF' ? 'F4F4F4' : 'FFFFFF';
					?>
					<tr style="background-color: #<?= $backColor ?>; min-height: 18px;">
						<td width="20%" nowrap="nowrap" style="text-align: right;"><b><?= $field->getLabel () ?>:</b></td>
						<td width="75%"><?= $search->isBlocked ($field) || $field->isReadOnly () ? Form::toHtml ($field) : Search::toForm ($field) ?></td>
						<td width="5%" style="vertical-align: top;"><?= Form::toHelp ($field); ?></td>
					</tr>
					<tr height="2px"><td colspan="3"></td></tr>
					<?
				}
				?>
				<tr>
					<td></td>
					<td colspan="2">
						<input type="submit" class="button" value="<?= __ ('Search') ?>" />
						<input type="button" class="button" value="<?= __ ('Cancel') ?>" onclick="JavaScript: showSearch ();" />
					</td>
				</tr>
				<tr height="5px"><td></td></tr>
			</table>
		</form>
	</div>
	<?
}
?>
<div id="idList">
	<?
	$description = Business::singleton ()->getAction (Action::TCURRENT)->getDescription ();
	if (trim ($description) != '')
		echo '<div class="description">'. $description .'</div>';
	
	$warning = Business::singleton ()->getAction (Action::TCURRENT)->getWarning ();
	if (trim ($warning) != '')
		echo '<div class="warning"><b style="color: #900;">'. __ ('Attention!') .'</b> '. $warning .'</div>';
	?>
	<table>
		<tr>
			<?
			$columns = sizeof ($view->getFields ()) + 1;
			
			while ($field = $view->getField ())
				echo '<td class="cTableHeader">'. View::toLabel ($field) .'</td>';
			?>
			<td class="cTableHeader"></td>
		</tr>
		<tr height="5px"><td colspan="<?= $columns ?>"></td></tr>
		<?
		while ($view->getItem ())
		{
			$itemId = $view->getId ();
			?>
			<tr id="_ITEM_<?= $view->getId () ?>" class="cTableItem" style="display:;">
				<? while ($field = $view->getLink ()) echo '<td class="standard">'. $field .'</td>'; ?>
				<td class="icon" nowrap="nowrap">
					<? while ($icon = $view->getIcon ()) echo $icon .'&nbsp;'; ?>
				</td>
			</tr>
			<tr id="_ROW_<?= $view->getId () ?>" style="display: none; background-color: #FFF;">
				<td colspan="<?= $columns ?>" id="_CONTENT_<?= $view->getId () ?>" class="inPlace"></td>
			</tr>
			<tr id="_SP_<?= $view->getId () ?>" class="cSeparator" style="display:;"><td colspan="<?= $columns ?>"></td></tr>
			<?
		}
		?>
	</table>
</div>
<div id="idResult"><b><?= $view->getTotal () ?></b> <?= __ ('Items Found') ?></div>
<div id="idPage">
	<ul><?= $view->pageMenu () ?></ul>
</div>