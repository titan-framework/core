<script language="javascript" type="text/javascript">
function changeSelect (source, target, select)
{
	selectedIndex = document.forms[0].elements[select].selectedIndex;
	
	if (!selectedIndex)
	{
		alert ('Selecione um grupo ao qual o usuário será vinculado.')
		return false;
	}
	
	sizeSource = document.forms[0].elements[source].length;
	
	i   = document.forms[0].elements[source].selectedIndex;
	opt = document.forms[0].elements[source].options[i];
	
	for(j = i ; j < sizeSource - 1 ; j++)
	{
		document.forms[0].elements[source].options[j].selected=false;
		optAux = document.forms[0].elements[source].options[j + 1];
		document.forms[0].elements[source].options[j] =	new Option(optAux.text, optAux.value, false, false);
	}
	
	if(j == i)
		document.forms[0].elements[source].options[j] = null;
	else
		document.forms[0].elements[source].options[j - 1] = null;


	sizeTarget = document.forms[0].elements[target].length;

	document.forms[0].elements[target].options[sizeTarget] = new Option(opt.text, opt.value, false, false);
}

function enableSection (key, fromLink)
{
	var actions = $('rowForActions_' + key);
	var img = $('arrow_' + key);
	var lnk = $('link_' + key);
	var main = $('main_' + key);
	var counter, check;
	
	if (fromLink)
		main.checked = true;
	
	if (main.checked)
	{
		actions.style.display = '';
		img.style.display = '';
		img.src = '<?= Skin::singleton ()->getIconsFolder () .'display.up.gif' ?>';
		
		counter = 0;
		while (check = document.getElementById ('checkbox' + key + '.' + counter++))
			check.checked = true;
		
		counter = 0;
		while (check = document.getElementById ('checkboxActions' + key + '.' + counter++))
			check.checked = true;
		
		lnk.onclick = function () { showPermissionRow (key) };
	}
	else
	{
		actions.style.display = 'none';
		img.style.display = 'none';
		
		counter = 0;
		while (check = document.getElementById ('checkbox' + key + '.' + counter++))
			check.checked = false;
		
		counter = 0;
		while (check = document.getElementById ('checkboxActions' + key + '.' + counter++))
			check.checked = false;
		
		lnk.onclick = function () { enableSection (key, true) };
	}
}

function showPermissionRow (key)
{
	var actions = document.getElementById ('rowForActions_' + key);
	var img = document.getElementById ('arrow_' + key);
	
	if (actions.style.display != '')
	{
		actions.style.display = '';
		img.src = '<?= Skin::singleton ()->getIconsFolder () .'display.up.gif' ?>';
	}
	else
	{
		actions.style.display = 'none';
		img.src = '<?= Skin::singleton ()->getIconsFolder () .'display.down.gif' ?>';
	}
}
</script>