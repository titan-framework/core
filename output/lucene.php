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
var luceneContentIds = new Array ();

function loadLucene (id, button)
{
	var row = $('_ROW_' + id);
	
	var label = $('_CONTENT_' + id);
	
	var assign = '_DIV_' + id + '_';
	
	for (var i = 0 ; i < luceneContentIds.length ; i++)
		$(luceneContentIds [i]).style.display = 'none';
	
	luceneContentIds [i] = assign;
	
	var div = document.createElement ('div');
	div.id = assign;
	div.className = 'inPlace';
	label.appendChild (div);
	
	div.innerHTML = '<iframe src="titan.php?target=luceneContent&id=' + id + '"></iframe>';
	
	row.style.display = '';
	
	button.onclick = function () { loadedLucene (row, div); };
	
	row.style.display = '';
		
	tAjax.showMessages ();
}

function loadedLucene (row, div)
{
	if (div.style.display == '')
	{
		div.style.display = 'none';
		row.style.display = 'none';
	}
	else
	{
		for (var i = 0 ; i < luceneContentIds.length ; i++)
			$(luceneContentIds [i]).style.display = 'none';
		
		row.style.display = '';
		div.style.display = '';
	}
}
</script>
<div id="idList">
	<table>
		<tr>
			<td class="cTableHeader">Resultados</td>
			<td class="cTableHeader">Local</td>
			<td class="cTableHeader"></td>
		</tr>
		<tr height="5px"><td></td></tr>
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
			<tr id="_SP1_<?= $hit->id ?>" class="cSeparatorHalf" style="display:;"><td></td></tr>
			<tr id="_ROW_<?= $hit->id ?>" style="display: none; background-color: #FFFFFF;">
				<td colspan="4"><label id="_CONTENT_<?= $hit->id ?>" class="content"></label></td>
			</tr>
			<tr id="_SP2_<?= $hit->id ?>" class="cSeparatorHalf" style="display:;"><td></td></tr>
			<?php
		}
		?>
	</table>
</div>
<div id="idResult"><b><?= sizeof ($hits) ?></b> <?= __ ('items found on') ?> <b><?= $index->count () ?></b> <?= __ ('documents') ?></div>
<div id="idPage"></div>