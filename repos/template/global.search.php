<style type="text/css">
#labelSearch
{
	padding: 5px 5px 5px 25px;
	width: 275px;
	margin: 2px;
	color: #000;
	background: url(titan.php?target=loadFile&file=interface/icon/search.gif) left no-repeat;
}
#imageSearch
{
	position: absolute;
	top: 3px;
	right: 3px;
}
#labelSearch:hover
{
	cursor: pointer;
	text-decoration: underline;
}
</style>
<div id="labelSearch" onclick="JavaScript: showSelectSearch ();"><?= __ ('Click here to search the items.') ?></div>
<img id="imageSearch" src="titan.php?target=loadFile&file=interface/icon/cancel.gif" border="0" class="icon" onclick="JavaScript: parent.global.Select.showSearch ('<?= $fieldId ?>');" />
<div id="idSearch" style="clear: both; display: none;">
	<form action="<?= $_SERVER['PHP_SELF'] .'?'. $_SERVER['QUERY_STRING'] ?>" method="post">
	<input type="hidden" name="search" value="1" />
	<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
		<tr>
			<td colspan="3" class="cTitle"><?= __ ('Search Itens') ?></td>
		</tr>
	</table>
	<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0" style="border: #36817C 1px solid; border-top-width: 3px;">
		<tr height="5px"><td></td></tr>
		<?
		$backColor = 'FFF';
		while ($field = $search->getField ())
		{
			$backColor = $backColor == 'FFF' ? 'F4F4F4' : 'FFF';
			?>
			<tr height="18px" style="background-color: #<?= $backColor ?>;">
				<td width="20%" nowrap style="text-align: right;"><b><?= $field->getLabel () ?>:</b></td>
				<td><?= $search->isBlocked ($field) ? Form::toHtml ($field) : Search::toForm ($field) ?></td>
				<td width="20px" style="vertical-align: top;"><?= Form::toHelp ($field); ?></td>
			</tr>
			<tr height="2px"><td></td></tr>
			<?
		}
		?>
		<tr>
			<td></td>
			<td colspan="2">
				<input type="submit" class="button" value="<?= __ ('Search') ?>" />
				<input type="button" class="button" value="<?= __ ('Cancel') ?>" onclick="JavaScript: showSelectSearch ();" />
			</td>
		</tr>
		<tr height="5px"><td></td></tr>
	</table>
	</form>
</div>
<div id="idList">
	<table>
		<tr>
			<?
			$columns = sizeof ($view->getFields ()) + 1;
			
			while ($field = $view->getField ())
				echo '<td class="cTableHeader">'. View::toLabel ($field, FALSE) .'</td>';
			?>
			<td class="cTableHeader"></td>
		</tr>
		<tr height="5px"><td colspan="<?= $columns ?>"></td></tr>
		<?
		while ($view->getItem ())
		{
			?>
			<tr class="cTableItem">
				<? while ($field = $view->getField ()) echo '<td><a href="" onclick="JavaScript: parent.global.Select.choose (\''. $fieldId .'\', \''. $view->getId () .'\', \''. Form::toText ($view->getField ('_TITLE_')) .'\');">'. View::toList ($field) .'</a></td>'; ?>
				<td style="text-align: right;" nowrap="nowrap">
					<a href="" onclick="JavaScript: parent.global.Select.choose ('<?= $fieldId ?>', '<?= $view->getId () ?>', '<?= Form::toText ($view->getField ('_TITLE_')) ?>');"><img src="titan.php?target=loadFile&file=interface/icon/arrow.right.gif" border="0" /></a>
				</td>
			</tr>
			<tr class="cSeparator"><td colspan="<?= $columns ?>"></td></tr>
			<?
		}
		?>
	</table>
</div>
<div id="idResult"><b><?= $view->getTotal () ?></b> <?= __ ('Items Found') ?></div>