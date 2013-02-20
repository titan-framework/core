<script language="javascript" type="text/javascript">
'global.Cascade'.namespace ();

global.Cascade.ajax = <?= class_exists ('xCascade', FALSE) ? XOAD_Client::register (new xCascade) : 'null' ?>;

global.Cascade.choose = function (fieldId, id, field, table, primary, father, view)
{
	eval ('var values = global.Cascade.values_' + fieldId + ';');
	
	var clear = false, key = null;
	
	for (var i = 0; i < values.length; i++)
	{
		if (clear && $(fieldId + '_' + values [i]) != undefined)
			$('_DIV_' + fieldId).removeChild ($(fieldId + '_' + values [i]));
		else if (values [i] == id)
		{
			clear = true;
			
			key = i + 1;
		}
	}
	
	if (key !== null)
		eval ('global.Cascade.values_' + fieldId + '.splice (' + key + ', ' + (values.length - key) + ');')
	
	var value = field[field.selectedIndex].value;
	
	$('_HIDDEN_' + fieldId).value = value;
	
	if (value == id)
		return false;
	
	str = global.Cascade.ajax.load (table, primary, father, view, value);
	
	eval (str);
	
	if (!ids || !ids.length)
		return false;
	
	eval ('global.Cascade.values_' + fieldId + '[global.Cascade.values_' + fieldId + '.length] = ' + value + ';');
	
	var select = document.createElement ('select');
	
	select.className = 'field';
	select.style.width = '499px';
	select.style.marginBottom = '3px';
	select.name = fieldId + '_' + value;
	select.id = fieldId + '_' + value;
	select.onchange = function () { global.Cascade.choose (fieldId, value, select, table, primary, father, view); };
	
	var opt = document.createElement ('option');
	
	opt.value = id;
	opt.text = 'Selecione...';
	
	try
	{
		select.add (opt, null);
	}
	catch (e)
	{
		select.add (opt, select.selectedIndex);
	}
	
	for (var i = 0; i < ids.length; i++)
	{
		var opt = document.createElement ('option');
		
		opt.value = ids [i];
		opt.text = lbs [i];
		
		try
		{
			select.add (opt, null);
		}
		catch (e)
		{
			select.add (opt, select.selectedIndex);
		}
	}
	
	$('_DIV_' + fieldId).appendChild (select);
	$('_DIV_' + fieldId).style.height = 'auto';
}
</script>