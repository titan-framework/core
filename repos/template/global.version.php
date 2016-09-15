<?php
if (!$flag)
{
	?>
	<style type="text/css">
	.noVersioned
	{
		width: auto;
		height: 80px;
		border: #990000 1px solid;
		background: url(titan.php?target=loadFile&file=interface/image/error.png) left no-repeat;
		background-position: 5px 5px;
		margin: 0px 8px;
	}
	.noVersioned .versionedText
	{
		float: left;
		margin: 25px 10px 25px 100px;
		font-size: 12px;
		color: #990000;
		font-weight: bold;
	}
	.noVersioned .versionedText label
	{
		color: #000000;
		font-size: 10px;
	}
	.versionButton
	{
		float: right;
		width: 110px;
		height: 16px;
		background: url(titan.php?target=loadFile&file=interface/image/arrow.green.png) right no-repeat;
		background-position: 75px 3px;
		margin: 10px;
		cursor: pointer;
		color: #000000;
		font-size: 14px;
		font-weight: bold;
		border: #009900 1px solid;
		padding: 20px 10px;
		color: #009900;
	}
	.versionButton:hover
	{
		background-color: #CCEBCC;
	}
	</style>
	<script language="javascript" type="text/javascript">
	function makeVersionable ()
	{
		showWait ();
		
		document.getElementById ('_BUTTON_').style.display = 'none';
		
		if (tAjax.makeVersionable ('<?= $view->getVersionedTable () ?>', '<?= $view->getVersionedPrimary () ?>'))
			tAjax.delay (function () {
				document.location = '<?= $_SERVER['PHP_SELF'] .'?'. $_SERVER['QUERY_STRING'] ?>';
			});
		
		
		tAjax.showMessages ();
		
		tAjax.delay (function () {
			document.getElementById ('_BUTTON_').style.display = '';
			
			hideWait ();
		});
	}
	</script>
	<div class="noVersioned">
		<div class="versionedText">
			Esta seção não está sobre controle de versão! Deseja ativar agora?<br />
			<label>O sistema criará automaticamente os artefatos de controle (tabelas, funções e gatilhos).</label>
		</div>
		<div id="_BUTTON_" class="versionButton" style="display:;" onclick="JavaScript: makeVersionable ();" title="Colocar sob Controle de Versões">Ativar</div>
	</div>
	<?php
	return;
}
?>
<script language="javascript" type="text/javascript">
function viewRevision (version, icon)
{
	showWait ();
	
	var row = document.getElementById ('_ROW_' + version);
	
	document.getElementById ('_REVISION_' + version).innerHTML = '<img src="titan.php?target=loadFile&amp;file=interface/icon/upload.gif" border="0" /> <label>Aguarde! Carregando...</label>'
	
	if (tAjax.loadRevision ('<?= $itemId ?>', version, function () { 
		tAjax.showMessages ();
		
		hideWait (); 
	}))
	{
		icon.onclick = function () { showRevision (version); };
		
		document.getElementById ('_REVISION_' + version).style.border = '#900 1px solid';
		document.getElementById ('_REVISION_' + version).style.padding = '5px';
		
		row.style.display = '';
	}
}
function showRevision (version)
{
	var row = document.getElementById ('_ROW_' + version);
	
	if (row.style.display == '')
		row.style.display = 'none';
	else
		row.style.display = '';
}
function revertRevision (version)
{
	showWait ();
	
	tAjax.revertRevision ('<?= $itemId ?>', version, function () {
		tAjax.showMessages ();
		
		hideWait ();
	});
}
</script>
<div id="idSearchParams" style="display:<?= $search->isEmpty () ? 'none' : '' ?>;">
	<form id="searchParams" action="<?= $_SERVER['PHP_SELF'] .'?target=body&toSection='. $section->getName () .'&toAction='. $action->getName () .'&itemId='. $itemId ?>" method="post">
		<input type="hidden" name="search" value="<?= Search::TCLEAR ?>" />
	</form>
	<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
		<tr>
			<td class="cTitle">Critérios de Busca Selecionados</td>
			<td class="cClear">
				<a href="#" onclick="JavaScript: document.getElementById ('searchParams').submit ();">Limpar Critérios</a>
				&nbsp;|&nbsp;
				<a href="#" onclick="JavaScript: showSearch ();">Mudar Critérios</a>
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
				<td colspan="3" class="cTitle">Buscar Itens</td>
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
					<td><?= $search->isBlocked ($field) ? Form::toHtml ($field) : Search::toForm ($field) ?></td>
					<td width="20px" style="vertical-align: top;"><?= Form::toHelp ($field); ?></td>
				</tr>
				<tr height="2px"><td></td></tr>
				<?php
			}
			?>
			<tr>
				<td></td>
				<td colspan="2">
					<input type="submit" class="button" value="Buscar" />
					<input type="button" class="button" value="Cancelar" onclick="JavaScript: showSearch ();" />
				</td>
			</tr>
			<tr height="5px"><td></td></tr>
		</table>
	</form>
</div>
<div id="idList">
	<?php
	$description = Business::singleton ()->getAction (Action::TCURRENT)->getDescription ();
	if (trim ($description) != '')
		echo '<div class="description">'. $description .'</div>';
	
	$warning = Business::singleton ()->getAction (Action::TCURRENT)->getWarning ();
	if (trim ($warning) != '')
		echo '<div class="warning"><b style="color: #900;">'. __ ('Attention!') .'</b> '. $warning .'</div>';
	?>
	<table>
		<tr>
			<?php
			$columns = sizeof ($view->getFields ()) + 1;
			
			while ($field = $view->getField ())
				echo '<td class="cTableHeader">'. View::toLabel ($field) .'</td>';
			?>
			<td class="cTableHeader"></td>
		</tr>
		<tr height="5px"><td></td></tr>
		<?php
		while ($view->getItem ())
		{
			?>
			<tr class="cTableItem">
				<?php
				$count = 1;
				while ($field = $view->getField ())
				{
					echo '<td>'. Form::toHtml ($field) .'</td>';
					$count++;
				}
				?>
				<td style="text-align: right;" nowrap="nowrap">
					<?php while ($icon = $view->getIcon ()) echo $icon .'&nbsp;'; ?>
				</td>
			</tr>
			<tr class="cSeparatorHalf"><td colspan="<?= $columns ?>"></td></tr>
			<tr id="_ROW_<?= $view->getId () ?>" style="display: none;">
				<td style="padding: 5px;" colspan="<?= $count ?>"><div id="_REVISION_<?= $view->getId () ?>"></div></td>
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