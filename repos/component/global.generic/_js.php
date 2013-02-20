<script language="javascript" type="text/javascript" src="titan.php?target=packer&files=fly-to-basket,lightbox"></script>
<script language="javascript" type="text/javascript">
function saveRelation ()
{
	size = document.forms[0].elements['selectFor[]'].length;

	for(i = 0 ; i < size ; i++)
		document.forms[0].elements['selectFor[]'].options[i].selected = true;

	document.forms[0].submit();
}

function changeSelect (source, target, select)
{
	selectedIndex = document.forms[0].elements[select].selectedIndex;

	if (!selectedIndex)
	{
		alert ('<?= __ ('Select a group which the user will be linked.') ?>')
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

function showGallery (itemId)
{
	var strPhotos = ajax.getPhotos (itemId);

	eval ('var photos = new Array (' + strPhotos + ');');

	var obj = new Array ();

	for (var i = 0 ; i < photos.length ; i++)
	{
		obj[i] = document.createElement ('a');
		obj[i].rel = 'lightbox[gallery_' + itemId + ']';
		obj[i].href = 'titan.php?target=script&toSection=<?= Business::singleton ()->getSection (Section::TCURRENT)->getName () ?>&file=resize&photoId=' + photos [i];
		document.body.appendChild (obj[i]);
	}

	myLightbox.start (obj[0]);

	for (var i = 0 ; i < obj.length ; i++)
	{
		document.body.removeChild (obj[i]);
		obj[i] = null;
	}
}

function selectAll ()
{
	var check = false, i;
	
	if ($('_SELECT_ALL_').checked)
		check = true;
	
	counter = 0;
	while (obj = $('check_' + counter++))
		obj.checked = check;
}

function exportCsv ()
{
	var assigns = '', useSearch = '0';
	
	counter = 0;
	while (obj = $('check_' + counter++))
		if (obj.checked)
			assigns = assigns + obj.name + ',';
	
	if (assigns == '')
	{
		message ('Selecione pelo menos um campo para ser exportado!', 300, 120, true);
		
		return false;
	}
	
	if ($('_SEARCH_').checked)
		useSearch = '1'
	
	openPrintPopup ('titan.php?target=script&toSection=<?= Business::singleton ()->getSection (Section::TCURRENT)->getName () ?>&file=exportCsv&auth=1&search=' + useSearch + '&assigns=' + assigns);
}
</script>