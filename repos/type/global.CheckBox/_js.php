<script language="javascript" type="text/javascript">
'global.CheckBox'.namespace ();

global.CheckBox.selectAll = function (name)
{
	$$("input:checkbox[name='" + name + "[]']").each (function (element) {
		element.checked = true;
	});
}

global.CheckBox.selectNone = function (name)
{
	$$("input:checkbox[name='" + name + "[]']").each (function (element) {
		element.checked = false;
	});
}
</script>