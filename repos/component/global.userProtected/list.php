<?php
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
			<?php
			$backColor = 'FFFFFF';
			while ($field = $search->getField ())
			{
				if ($field->isEmpty ())
					continue;
	
				$backColor = $backColor == 'FFFFFF' ? 'F4F4F4' : 'FFFFFF';
				?>
				<tr height="18px" style="background-color: #<?= $backColor ?>;">
					<td width="20%" nowrap style="text-align: right;"><b><?= $field->getLabel () ?>:</b></td>
					<td <?= $search->isBlocked ($field) ? 'style="color: #990000;"' : '' ?>><?= Form::toHtml ($field) ?></td>
				</tr>
				<tr height="2px"><td></td></tr>
				<?php
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
				<tr height="5px"><td></td></tr>
				<?php
				$backColor = 'FFFFFF';
				while ($field = $search->getField ())
				{
					$backColor = $backColor == 'FFFFFF' ? 'F4F4F4' : 'FFFFFF';
					?>
					<tr height="18px" style="background-color: #<?= $backColor ?>;">
						<td width="20%" nowrap style="text-align: right;"><b><?= $field->getLabel () ?>:</b></td>
						<td><?= $search->isBlocked ($field) || $field->isReadOnly () ? Form::toHtml ($field) : Search::toForm ($field) ?></td>
						<td width="20px" style="vertical-align: top;"><?= Form::toHelp ($field); ?></td>
					</tr>
					<tr height="2px"><td></td></tr>
					<?php
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
	<?php
}
?>
<div id="idList">
	<table>
		<tr>
			<?php
			$columns = sizeof ($view->getFields ()) + 1;
			
			while ($field = $view->getField ())
				echo '<td class="cTableHeader">'. View::toLabel ($field) .'</td>';
			?>
			<td class="cTableHeader"></td>
		</tr>
		<tr height="5px"><td colspan="<?= $columns ?>"></td></tr>
		<?php
		while ($view->getItem ())
		{
			?>
			<tr id="_ITEM_<?= $view->getId () ?>" class="cTableItem">
				<?php
				while ($field = $view->getLink ())
					echo '<td>'. $field .'</td>';
				?>
				<td style="text-align: right;" nowrap="nowrap">
					<?php while ($icon = $view->getIcon ()) echo $icon .'&nbsp;'; ?>
					<a href="#" onclick="JavaScript: showActivate (<?= $view->getId () ?>); return false;" title="Ativar/Desativar Usuário"><img src="titan.php?target=loadFile&file=interface/icon/special.gif" border="0" /></a>
					<?php
					if (User::singleton ()->hasPermission ('_CHANGE_PASSWORD_'))
					{
						?>
						&nbsp;<a href="#" onclick="JavaScript: showChangePasswd (<?= $view->getId () ?>); return false;" title="Alterar a Senha do Usuário"><img src="titan.php?target=loadFile&file=interface/icon/permission.gif" border="0" /></a>
						<?php
					}
					?>
				</td>
			</tr>
			<tr id="_ROW_<?= $view->getId () ?>" style="display: none; background-color: #FFF;">
				<td colspan="<?= $columns ?>" id="_CONTENT_<?= $view->getId () ?>" class="inPlace"></td>
			</tr>
			<tr class="cSeparatorHalf"><td colspan="<?= $columns ?>"></td></tr>
			<tr class="cTableItem" style="display: none; height: 40px; background-color: #FFFFFF;" id="_USER_ROW_<?= $view->getId () ?>">
				<td colspan="<?= $columns ?>">
					<label id="_USER_CONTENT_<?= $view->getId () ?>"></label>
				</td>
			</tr>
			<tr class="cSeparatorHalf"><td colspan="<?= $columns ?>"></td></tr>
			<?php
		}
		?>
	</table>
</div>
<div id="idResult"><b><?= $view->getTotal () ?></b> Itens Encontrados</div>
<div id="idPage">
	<ul><?= $view->pageMenu () ?></ul>
</div>