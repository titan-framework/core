<script language="javascript" type="text/javascript">
'global.Slug'.namespace ();

global.Slug.ajax = <?= class_exists ('xSlug', FALSE) ? XOAD_Client::register (new xSlug) : 'null' ?>;

global.Slug.load = function (field, table, column, base)
{
	if (field.value != '')
		return false;
	
	field.value = global.Slug.ajax.generateSlug ($('field_' + base).value, table, column);
}
</script>