<script language="javascript" type="text/javascript">
'global.Multiply'.namespace ();

global.Multiply.array = new Array ();

global.Multiply.choose = function (name, field, value, label, start)
{
	for (var i = 0 ; i < global.Multiply.array.length ; i++)
		if (global.Multiply.array [i] == field + '_' + value)
			return false;
	
	var column, image, hidden, row = document.createElement ('tr');
	
	row.id = 'row_' + field + '_' + global.Multiply.array.length;
	
	row.style.display = 'block';
	
	column = document.createElement ('td');
	
	image = document.createElement ('img');
	
	image.src = 'titan.php?target=loadFile&file=interface/icon/delete.gif';
	
	image.title = image.alt = '<?= __ ('Remove') ?>';
	
	image.border = 0;
	
	image.style.cursor = 'pointer';
	
	image.onclick = function () { global.Multiply.unchoose (field, value) };
	
	column.appendChild (image);
	
	row.appendChild (column);
	
	column = document.createElement ('td');
	
	column.innerHTML = label;
	
	hidden = document.createElement ('input');
	
	hidden.type = 'hidden';
	
	hidden.name = name + '[]';
	
	hidden.value = value;
	
	hidden.id = 'hidden_' + field + value;
	
	column.appendChild (hidden);
	
	row.appendChild (column);
	
	$(field + '_table').appendChild (row);
	
	global.Multiply.array [global.Multiply.array.length] = field + '_' + value;
}

global.Multiply.unchoose = function (field, value)
{
	var column, hidden;
	
	for (var i = 0 ; i < global.Multiply.array.length ; i++)
		if (global.Multiply.array [i] == field + '_' + value)
		{
			global.Multiply.array [i] = '';
			
			hidden = $('hidden_' + field + value);
			
			hidden.parentNode.removeChild (hidden);
			
			$('row_' + field + '_' + i).style.display = 'none';
		}
}
</script>