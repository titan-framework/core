<script type="text/javascript" src="titan.php?target=tResource&type=Select&file=chosen.proto.min.js"></script>
<script language="javascript" type="text/javascript">
'global.Select'.namespace ();

global.Select.showSearch = function (fieldId)
{
	var iframe = $('_SEARCH_AT_SELECT_TYPE_' + fieldId);

	if (iframe.style.display == 'block')
		iframe.style.display = 'none';
	else
		iframe.style.display = 'block';
}

global.Select.choose = function (fieldId, itemId, text)
{
	var field = $(fieldId);
	var label = $('_LABEL_AT_SELECT_TYPE_' + fieldId);
	var div = $('_SEARCH_AT_SELECT_TYPE_' + fieldId);
	var del = $('_CLEAR_AT_SELECT_TYPE_' + fieldId);

	field.value = itemId;
	label.value = text;
	div.style.display = 'none';

	del.src = 'titan.php?target=loadFile&file=interface/icon/delete.gif';
	del.onclick = function () { global.Select.clear (fieldId); };
	del.className = 'icon';
}

global.Select.clear = function (fieldId)
{
	$(fieldId).value = 0;
	$('_LABEL_AT_SELECT_TYPE_' + fieldId).value = '';

	var del = $('_CLEAR_AT_SELECT_TYPE_' + fieldId);
	del.src = 'titan.php?target=loadFile&file=interface/icon/grey/delete.gif';
	del.onclick = function () { return false; };
	del.className = '';
}

document.observe ('dom:loaded', function (evt)
{
	var config = {
		'.chosen': {
			disable_search_threshold: 10,
			no_results_text: "<?= __ ('Nothing found!') ?>",
			placeholder_text_single: "<?= __ ('Select...') ?>",
			allow_single_deselect: true,
			search_contains: true,
			width: "506px"
		}
	}

	var results = [];

	for (var selector in config) {
		var elements = $$(selector);
		for (var i = 0; i < elements.length; i++) {
			results.push(new Chosen(elements[i],config[selector]));
		}
	}

	return results;
});
</script>
