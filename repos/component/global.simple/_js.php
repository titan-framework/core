<script language="javascript" type="text/javascript">
function viewTags ()
{
	var size = getWindowSize ();
	
	var h = size.height - 70;
	var w = size.width - 20;
	
	if (w > 800)
		w = 800;
	
	var source = '<div style="margin: 0px; border: #CCC 1px solid; overflow: auto; width: ' + (w - 20) + 'px; height: ' + (h - 50) + 'px"><?= isset ($tagBuffer) ? $tagBuffer : '' ?></div>';
	
	Modalbox.show (source, { title: '<?= __ ('Library of Keywords') ?>', width: w, height: h });
}
</script>