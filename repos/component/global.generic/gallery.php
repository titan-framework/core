<style type="text/css">
#idGallery
{
	margin: 5px;
}
.iframeUpload
{
	overflow: hidden;
	width: 100%;
	height: 55px;
	border-width: 0;
}
.divImage
{
	float: left;
	width: 132px;
	height: 102px;
	margin: 3px;
	cursor: move;
}
.divImage .menu
{
	padding: 0px;
	display: inline;
	position: relative;
	margin: 0px 24px;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 16px;
	opacity: .5;
	filter: alpha(Opacity=50);
	-khtml-opacity: .5;
	list-style: none;
}
.divImage .menu li
{
	padding: 0px 1px;
	display: inline;
	list-style: none;
	cursor: pointer;
	margin: 0px;
	-moz-border-radius: 0px 0px 3px 3px;
	background: center no-repeat;
	background-color: #FFFFFF;
}
.divImage .menu li:hover
{
	background-color: #36817C;
}
.divImage .menu .iconView
{
	background-image: url(titan.php?target=resource&toSection=<?= Business::singleton ()->getSection (Section::TCURRENT)->getName () ?>&file=view.png);
}
.divImage .menu .iconEditData
{
	background-image: url(titan.php?target=resource&toSection=<?= Business::singleton ()->getSection (Section::TCURRENT)->getName () ?>&file=edit.png);
}
.divImage .menu .iconEdit
{
	background-image: url(titan.php?target=resource&toSection=<?= Business::singleton ()->getSection (Section::TCURRENT)->getName () ?>&file=photo.png);
}
.divImage .menu .iconRemove
{
	background-image: url(titan.php?target=resource&toSection=<?= Business::singleton ()->getSection (Section::TCURRENT)->getName () ?>&file=remove.png);
}
</style>
<div id="idSearch" style="display: none;">
	<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
		<tr>
			<td class="cTitle"><?= __ ('Send Photos') ?></td>
		</tr>
	</table>
	<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0" style="border: #36817C 1px solid; border-top-width: 3px;">
		<tr>
			<td>
				<iframe id="_PHOTO_UPLOAD_" class="iframeUpload" src="titan.php?target=blank"></iframe>
			</td>
		</tr>
	</table>
</div>
<div id="idGallery" style="text-align: center;">
	<?
	while ($obj = $sth->fetch (PDO::FETCH_OBJ))
	{
		?>
		<div id="image_<?= $obj->_media ?>" class="divImage" style="background: url(titan.php?target=script&toSection=<?= Business::singleton ()->getSection (Section::TCURRENT)->getName () ?>&file=thumb&photoId=<?= $obj->_media ?>) no-repeat;" ondblclick="JavaScript: viewGallery (<?= $obj->_media ?>);" title="<?= __ ('Move the photos to sort them or a double-click to view them.') ?>">
			<ul class="menu"><li title="<?= __ ('Show image in fullsize') ?>" onclick="JavaScript: viewRealPhoto (<?= $obj->_media ?>);" class="iconView">&nbsp;&nbsp;&nbsp;&nbsp;</li><li title="<?= __ ('Edit image data') ?>" onclick="JavaScript: editPhotoData (<?= $obj->_media ?>);" class="iconEditData">&nbsp;&nbsp;&nbsp;&nbsp;</li><li title="<?= __ ('Edit image') ?>" onclick="JavaScript: editPhoto (<?= $obj->_media ?>);" class="iconEdit">&nbsp;&nbsp;&nbsp;&nbsp;</li><li title="<?= __ ('Remove image') ?>" onclick="JavaScript: removePhoto (<?= $obj->_media ?>);" class="iconRemove">&nbsp;&nbsp;&nbsp;&nbsp;</li></ul>
		</div>
		<?
	}
	?>
</div>
<script type="text/javascript" language="javascript">
function saveImageSort ()
{
	showWait ();

	ajax.saveImageSort (Sortable.serialize('idGallery'), '<?= $table ?>', '<?= $itemId ?>', function () {
		hideWait ();
	});
}

Sortable.create("idGallery",{tag:'div',overlap:'horizontal',constraint: false});

function showUpload ()
{
	var div = $('idSearch');

	if (div.style.display == '')
		div.style.display = 'none';
	else
	{
		$('_PHOTO_UPLOAD_').src = 'titan.php?target=script&toSection=<?= Business::singleton ()->getSection (Section::TCURRENT)->getName () ?>&file=upload&itemId=<?= $itemId ?>&table=<?= $table ?>&auth=1';

		div.style.display = '';
	}
}

function createThumb (id)
{
	var image = document.createElement ('div');

	$('idGallery').appendChild (image);

	image.id = 'image_' + id;
	image.style.background = 'url(titan.php?target=script&toSection=<?= Business::singleton ()->getSection (Section::TCURRENT)->getName () ?>&file=thumb&photoId=' + id + ') no-repeat';
	image.className = 'divImage';
	image.title = '<?= __ ('Move the photos to sort them or a double-click to view them.') ?>';
	image.ondblclick = function () { viewGallery (id); };

	var menu = document.createElement ('ul');

	image.appendChild (menu);

	menu.className = 'menu';

	var item1 = document.createElement ('li');

	menu.appendChild (item1);

	item1.title = '<?= __ ('Show image in fullsize') ?>';
	item1.onclick = function () { viewRealPhoto (id); };
	item1.className = 'iconView';
	item1.innerHTML = '&nbsp;&nbsp;&nbsp;&nbsp;';

	var item2 = document.createElement ('li');

	menu.appendChild (item2);

	item2.title = '<?= __ ('Edit image data') ?>';
	item2.onclick = function () { editPhotoData (id); };
	item2.className = 'iconEditData';
	item2.innerHTML = '&nbsp;&nbsp;&nbsp;&nbsp;';

	var item3 = document.createElement ('li');

	menu.appendChild (item3);

	item3.title = '<?= __ ('Edit image') ?>';
	item3.onclick = function () { editPhoto (id); };
	item3.className = 'iconEdit';
	item3.innerHTML = '&nbsp;&nbsp;&nbsp;&nbsp;';

	var item4 = document.createElement ('li');

	menu.appendChild (item4);

	item4.title = '<?= __ ('Remove image') ?>';
	item4.onclick = function () { removePhoto (id); };
	item4.className = 'iconRemove';
	item4.innerHTML = '&nbsp;&nbsp;&nbsp;&nbsp;';
}

function viewRealPhoto (id)
{
	openPopUp ('titan.php?target=script&toSection=<?= Business::singleton ()->getSection (Section::TCURRENT)->getName () ?>&file=openPhoto&photoId=' + id, 'photo_' + id);
}

function editPhotoData (id)
{
	alert ('<?= __ ('Under development!') ?> [' + id + ']');
}

function editPhoto (id)
{
	alert ('<?= __ ('Under development!') ?> [' + id + ']');
}

function removePhoto (id)
{
	showWait ();

	if (ajax.removePhoto (id, '<?= $itemId ?>', function () {
		hideWait ();
	}))
	{
		var thumbs = $('idGallery');

		thumbs.removeChild ($('image_' + id));

		Sortable.create("idGallery",{tag:'div',overlap:'horizontal',constraint: false});
	}
}

function viewGallery (id)
{
	var strPhotos = ajax.getPhotos (<?= $itemId ?>);

	eval ('var photos = new Array (' + strPhotos + ');');

	var obj = new Array ();

	var aux = null;

	for (var i = 0 ; i < photos.length ; i++)
	{
		obj[i] = document.createElement ('a');
		obj[i].rel = 'lightbox[gallery_<?= $itemId ?>]';
		obj[i].href = 'titan.php?target=script&toSection=<?= Business::singleton ()->getSection (Section::TCURRENT)->getName () ?>&file=resize&photoId=' + photos [i];
		document.body.appendChild (obj[i]);

		if (photos [i] == id)
			aux = obj[i];
	}

	if (!aux && obj[0])
		aux = obj[0];

	myLightbox.start (aux);

	for (var i = 0 ; i < obj.length ; i++)
	{
		document.body.removeChild (obj[i]);
		obj[i] = null;
	}
}
</script>