<?php
if (!Lucene::singleton ()->isActive ())
	throw new Exception (__ ('Global search is not active!'));

require_once 'Zend/Search/Lucene.php';

$instance = Instance::singleton ();

$index = Lucene::singleton ()->getIndex ();

$hits = $index->find ($query);

$oTerms = Zend_Search_Lucene_Search_QueryParser::parse ($query)->rewrite ($index)->getQueryTerms ();

$terms = array ();
foreach ($oTerms as $trash => $oTerm)
	if (!in_array ($oTerm->text, $terms))
		$terms [] = $oTerm->text;
?>
<script language="javascript" type="text/javascript">
function loadLucene (id, button)
{
	var iframe = document.createElement ('iframe');
	iframe.id = '_DIV_' + id + '_';
	iframe.className = 'inPlaceAction';
	iframe.style.height = '50px;';
	iframe.style.display = '';
	iframe.src = 'titan.php?target=luceneContent&id=' + id;
	iframe.onload = function () { iframe.style.backgroundColor = '#FFF'; };

	loadInPlace (id, iframe, button);
}
</script>
<div id="idList">
	<table>
		<tr>
			<td class="cTableHeader">Resultados</td>
			<td class="cTableHeader">Local</td>
			<td class="cTableHeader"></td>
		</tr>
		<tr height="5px"><td colspan="3"></td></tr>
		<?php
		foreach ($hits as $trash => $hit)
		{
			?>
			<tr id="_ITEM_<?= $hit->id ?>" class="cTableItem" style="display:;">
				<td><?= relevant ($hit->content, $terms) ?></td>
				<td><?= $hit->local ?></td>
				<td style="text-align: right;" nowrap="nowrap">
					<img src="titan.php?target=loadFile&file=interface/icon/view.gif" class="icon" border="0" title="<?= __ ('Synopsis') ?>"  onclick="JavaScript: loadLucene ('<?= $hit->id ?>', this);" />&nbsp;
					<img src="titan.php?target=loadFile&file=interface/icon/arrow.right.gif" class="icon" border="0" title="<?= __ ('Go') ?>"  onclick="JavaScript: document.location = '<?= $hit->url ?>';" />&nbsp;
				</td>
			</tr>
			<tr id="_ROW_<?= $hit->id ?>" style="display: none; background-color: #FFF;">
				<td colspan="3" id="_CONTENT_<?= $hit->id ?>" class="inPlace"></td>
			</tr>
			<tr id="_SP2_<?= $hit->id ?>" class="cSeparator" style="display:;"><td colspan="3"></td></tr>
			<?php
		}
		?>
	</table>
</div>
<div id="idResult"><b><?= sizeof ($hits) ?></b> <?= __ ('items found on') ?> <b><?= $index->count () ?></b> <?= __ ('documents') ?></div>
<div id="idPage"></div>
