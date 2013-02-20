<script language="javascript" type="text/javascript">
'global.Boolean'.namespace ();

global.Boolean.alter = function (id)
{
	$(id).value = ($(id).value == 1) ? 0 : 1;
}
</script>