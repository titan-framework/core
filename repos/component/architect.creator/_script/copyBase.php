<?
if (!isset ($_GET['name']))
	die ('Nome da aplicação inválido!');

$src = $section->getCompPath () .'_base/'. $_GET['base'];
$dst = 'instance'. DIRECTORY_SEPARATOR . $_GET['name'];
?>
<style type="text/css">
div
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	color: #000000;
}
</style>
<div>
	<?
	$count = copyDir ($src, $dst, TRUE);
	
	echo '<br /><b>'. $count .' arquivos copiados.<b>';
	?>
</div>
<script language="javascript" type="text/javascript">
parent.confirmCopy ('<?= $_GET['name'] ?>');
</script>