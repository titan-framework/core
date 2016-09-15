<style type="text/css">
#idMenuArchitect
{
	margin: 0 auto;
	width: 557px;
}
#idForm .item
{
	margin: 20px auto;
	padding: 5px;
	color: #000000;
	font-size: 12px;
	background-color: #F4F4F4;
	width: 600px;
}
#idForm .item .number
{
	color: #006600;
	font-size: 25px;
	font-weight: bold;
	float: left;
	margin-right: 15px;
	height: 100px;
}
#idForm .item img
{
	float: right;
	margin: 10px;
}
#idForm .item a
{
	font-size: 12px;
}
</style>
<div id="idMenuArchitect">
	<?php swf (Business::singleton ()->getSection (Section::TCURRENT)->getComponentPath () .'_image/menu.swf', 557, 65) ?>
</div>
<div id="idForm">
	<div class="item">
		<div class="number">1&ordm;</div>
		<a href="titan.php?target=script&toSection=<?= $section->getName () ?>&file=getInstance&name=<?= $itemId ?>&auth=1" target="_blank"><img src="titan.php?target=loadFile&file=interface/image/update.png" border="0" /></a>
		<b>Download da Instância:</b> <a href="titan.php?target=script&toSection=<?= $section->getName () ?>&file=getInstance&name=<?= $itemId ?>&auth=1" target="_blank">Clique aqui</a> para efetuar download dos arquivos desta instância. 
		<label style="color: #990000">Você deverá modificar o arquivo [configure/titan.xml] modificando os valores dos atributos [core-path] e [repository-path] de forma que eles apontem para o 
		<b>CORE</b> e para o <b>REPOSITÓRIO</b> do <b>Titan Framework</b> no servidor em que ficará a aplicação!</label> <br />
	</div>
</div>