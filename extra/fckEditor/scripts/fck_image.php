<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<!--
 * FCKeditor - The text editor for Internet - http://www.fckeditor.net
 * Copyright (C) 2003-2007 Frederico Caldeira Knabben
 *
 * == BEGIN LICENSE ==
 *
 * Licensed under the terms of any of the following licenses at your
 * choice:
 *
 *  - GNU General Public License Version 2 or later (the "GPL")
 *    http://www.gnu.org/licenses/gpl.html
 *
 *  - GNU Lesser General Public License Version 2.1 or later (the "LGPL")
 *    http://www.gnu.org/licenses/lgpl.html
 *
 *  - Mozilla Public License Version 1.1 or later (the "MPL")
 *    http://www.mozilla.org/MPL/MPL-1.1.html
 *
 * == END LICENSE ==
 *
 * Image Properties dialog window.
-->
<?php
$instance = Instance::singleton ();

$archive = Archive::singleton ();

$fieldId = '_fck_image_id_';
$fieldName = '_fck_image_name_';
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Image Properties</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-2" />
	<meta name="robots" content="noindex, nofollow" />
	<script src="titan.php?target=loadFile&amp;file=extra/fckEditor/editor/dialog/common/fck_dialog_common.js" type="text/javascript"></script>
	<script src="titan.php?target=loadFile&amp;file=extra/fckEditor/editor/dialog/fck_image/fck_image.js" type="text/javascript"></script>
	<script language="javascript" type="text/javascript" src="titan.php?target=loadFile&file=js/prototype.js"></script>
	<script language="javascript" type="text/javascript">
	String.prototype.namespace = function (separator)
	{
		this.split (separator || '.').inject (window, function (parent, child) {
			return parent [child] = parent [child] || { };
		})
	}
	</script>
	<script language="javascript" type="text/javascript" src="titan.php?target=packer&files=general,common,actb_fck"></script>
	<?= XOAD_Utilities::header('titan.php?target=loadFile&amp;file=xoad') ."\n" ?>
	<script language="javascript" type="text/javascript">
	var tAjax = <?= XOAD_Client::register(new Xoad) ?>;

	function loadFile (fileId, fieldId)
	{
		document.getElementById ('txtUrl').value = parent.InstanceUrl () + 'titan.php?target=openFile&fileId=' + fileId;

		UpdatePreview();

		document.getElementById(fieldId + '_upload').style.display = 'none';
	}

	function uploadFile (fieldId)
	{
		var iframe = document.getElementById(fieldId + '_upload');

		if (iframe.style.display == '')
			iframe.style.display = 'none';
		else
			iframe.style.display = '';
	}

	var uploadFilterKey = new Array ();
	var uploadFilterValue = new Array ();
	var uploadFilterCount = 0;

	function getUploadFilter (fieldId)
	{
		for (var i = 0 ; i < uploadFilterCount ; i++)
			if (uploadFilterKey [i] == fieldId)
				return uploadFilterValue [i];

		return '';
	}

	function addUploadFilter (fieldId, mimes)
	{
		uploadFilterKey [uploadFilterCount] = fieldId;
		uploadFilterValue [uploadFilterCount] = mimes;
		uploadFilterCount++;
	}

	'global.File'.namespace ();

	global.File.load = function (fileId, fieldId)
	{
		loadFile (fileId, fieldId);
	}

	global.File.getFilter = function (fieldId)
	{
		return getUploadFilter (fieldId);
	}
	</script>
	<style type="text/css">
	<?php include Instance::singleton ()->getCorePath () .'extra/fckEditor/editor/dialog/common/fck_dialog_common.css' ?>
	<?php include Instance::singleton ()->getCorePath () .'extra/fckEditor/editor/skins/default/fck_dialog.css' ?>
	.iframeFile
	{
		overflow: hidden;
		width: 300px;
		height: 106px;
		border-width: 0;
	}
	</style>
</head>
<body scroll="no" style="overflow: auto;">
	<div id="divInfo" style="width: 600px; height: 300px;">
		<table cellspacing="1" cellpadding="1" border="0" width="100%" height="100%">
			<tr>
				<td>
					<span fcklang="DlgImgServer">Server Image</span><br />
					<style type="text/css">#img_<?= $fieldId ?>:hover { cursor: pointer; }</style>
					<input type="text" style="width: 250px;" id="<?= $fieldId ?>" title="Buscar arquivo" />
					<img src="titan.php?target=loadFile&amp;file=interface/icon/save.gif" border="0" id="img_<?= $fieldId ?>" style="vertical-align: bottom;" title="Enviar arquivo" onClick="JavaScript: uploadFile ('<?= $fieldId ?>');" />
					<div id="<?= $fieldId ?>_upload" style="display: none; position: relative; width: 300px; height: 106px; border: #990000 1px solid; margin-top: 2px;">
						<iframe class="iframeFile" src="titan.php?target=upload&fieldId=<?= $fieldId ?>" border="0"></iframe>
					</div>
				</td>
			</tr>
			<tr>
				<td>
					<span fcklang="DlgImgURL">URL</span>
					<input id="txtUrl" style="width: 100%" type="text" onBlur="UpdatePreview();" />
				</td>
			</tr>
			<tr>
				<td>
					<span fcklang="DlgImgAlt">Short Description</span><br />
					<input id="txtAlt" style="width: 100%" type="text" /><br />
				</td>
			</tr>
			<tr height="100%">
				<td valign="top">
					<table cellspacing="0" cellpadding="0" width="100%" border="0" height="100%">
						<tr>
							<td valign="top">
								<br />
								<table cellspacing="0" cellpadding="0" border="0">
									<tr>
										<td nowrap="nowrap">
											<span fcklang="DlgImgWidth">Width</span>&nbsp;</td>
										<td>
											<input type="text" size="3" id="txtWidth" onKeyUp="OnSizeChanged('Width',this.value);" /></td>
										<td rowspan="2">
											<div id="btnLockSizes" class="BtnLocked" onMouseOver="this.className = (bLockRatio ? 'BtnLocked' : 'BtnUnlocked' ) + ' BtnOver';"
												onmouseout="this.className = (bLockRatio ? 'BtnLocked' : 'BtnUnlocked' );" title="Lock Sizes"
												onclick="SwitchLock(this);">
											</div>
										</td>
										<td rowspan="2">
											<div id="btnResetSize" class="BtnReset" onMouseOver="this.className='BtnReset BtnOver';"
												onmouseout="this.className='BtnReset';" title="Reset Size" onClick="ResetSizes();">
											</div>
										</td>
									</tr>
									<tr>
										<td nowrap="nowrap">
											<span fcklang="DlgImgHeight">Height</span>&nbsp;</td>
										<td>
											<input type="text" size="3" id="txtHeight" onKeyUp="OnSizeChanged('Height',this.value);" /></td>
									</tr>
								</table>
								<br />
								<table cellspacing="0" cellpadding="0" border="0">
									<tr>
										<td nowrap="nowrap">
											<span fcklang="DlgImgBorder">Border</span>&nbsp;</td>
										<td>
											<input type="text" size="2" value="" id="txtBorder" onKeyUp="UpdatePreview();" /></td>
									</tr>
									<tr>
										<td nowrap="nowrap">
											<span fcklang="DlgImgHSpace">HSpace</span>&nbsp;</td>
										<td>
											<input type="text" size="2" id="txtHSpace" onKeyUp="UpdatePreview();" /></td>
									</tr>
									<tr>
										<td nowrap="nowrap">
											<span fcklang="DlgImgVSpace">VSpace</span>&nbsp;</td>
										<td>
											<input type="text" size="2" id="txtVSpace" onKeyUp="UpdatePreview();" /></td>
									</tr>
									<tr>
										<td nowrap="nowrap">
											<span fcklang="DlgImgAlign">Align</span>&nbsp;</td>
										<td>
											<select id="cmbAlign" onChange="UpdatePreview();">
												<option value="" selected="selected"></option>
												<option fcklang="DlgImgAlignLeft" value="left">Left</option>
												<option fcklang="DlgImgAlignAbsBottom" value="absBottom">Abs Bottom</option>
												<option fcklang="DlgImgAlignAbsMiddle" value="absMiddle">Abs Middle</option>
												<option fcklang="DlgImgAlignBaseline" value="baseline">Baseline</option>
												<option fcklang="DlgImgAlignBottom" value="bottom">Bottom</option>
												<option fcklang="DlgImgAlignMiddle" value="middle">Middle</option>
												<option fcklang="DlgImgAlignRight" value="right">Right</option>
												<option fcklang="DlgImgAlignTextTop" value="textTop">Text Top</option>
												<option fcklang="DlgImgAlignTop" value="top">Top</option>
											</select>
										</td>
									</tr>
								</table>
							</td>
							<td>
								&nbsp;&nbsp;&nbsp;</td>
							<td width="100%" valign="top">
								<table cellpadding="0" cellspacing="0" width="100%" style="table-layout: fixed">
									<tr>
										<td>
											<span fcklang="DlgImgPreview">Preview</span></td>
									</tr>
									<tr>
										<td valign="top">
											<iframe class="ImagePreviewArea" src="titan.php?target=tScript&type=Fck&file=dialog&dialog=fck_image_preview&auth=1" frameborder="0" marginheight="0" marginwidth="0"></iframe>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</div>
	<div id="divUpload" style="display: none">
		<form id="frmUpload" method="post" target="UploadWindow" enctype="multipart/form-data"
			action="" onSubmit="return CheckUpload();">
			<span fcklang="DlgLnkUpload">Upload</span><br />
			<input id="txtUploadFile" style="width: 100%" type="file" size="40" name="NewFile" /><br />
			<br />
			<input id="btnUpload" type="submit" value="Send it to the Server" fcklang="DlgLnkBtnUpload" />
			<iframe name="UploadWindow" style="display: none" src="javascript:void(0)"></iframe>
		</form>
	</div>
	<div id="divLink" style="display: none">
		<table cellspacing="1" cellpadding="1" border="0" width="100%">
			<tr>
				<td>
					<div>
						<span fcklang="DlgLnkURL">URL</span><br />
						<input id="txtLnkUrl" style="width: 100%" type="text" onBlur="UpdatePreview();" />
					</div>
					<div id="divLnkBrowseServer" align="right">
						<input type="button" value="Browse Server" fcklang="DlgBtnBrowseServer" onClick="LnkBrowseServer();" />
					</div>
					<div>
						<span fcklang="DlgLnkTarget">Target</span><br />
						<select id="cmbLnkTarget">
							<option value="" fcklang="DlgGenNotSet" selected="selected">&lt;not set&gt;</option>
							<option value="_blank" fcklang="DlgLnkTargetBlank">New Window (_blank)</option>
							<option value="_top" fcklang="DlgLnkTargetTop">Topmost Window (_top)</option>
							<option value="_self" fcklang="DlgLnkTargetSelf">Same Window (_self)</option>
							<option value="_parent" fcklang="DlgLnkTargetParent">Parent Window (_parent)</option>
						</select>
					</div>
				</td>
			</tr>
		</table>
	</div>
	<div id="divAdvanced" style="display: none">
		<table cellspacing="0" cellpadding="0" width="100%" align="center" border="0">
			<tr>
				<td valign="top" width="50%">
					<span fcklang="DlgGenId">Id</span><br />
					<input id="txtAttId" style="width: 100%" type="text" />
				</td>
				<td width="1">
					&nbsp;&nbsp;</td>
				<td valign="top">
					<table cellspacing="0" cellpadding="0" width="100%" align="center" border="0">
						<tr>
							<td width="60%">
								<span fcklang="DlgGenLangDir">Language Direction</span><br />
								<select id="cmbAttLangDir" style="width: 100%">
									<option value="" fcklang="DlgGenNotSet" selected="selected">&lt;not set&gt;</option>
									<option value="ltr" fcklang="DlgGenLangDirLtr">Left to Right (LTR)</option>
									<option value="rtl" fcklang="DlgGenLangDirRtl">Right to Left (RTL)</option>
								</select>
							</td>
							<td width="1%">
								&nbsp;&nbsp;</td>
							<td nowrap="nowrap">
								<span fcklang="DlgGenLangCode">Language Code</span><br />
								<input id="txtAttLangCode" style="width: 100%" type="text" />&nbsp;
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan="3">&nbsp;
					</td>
			</tr>
			<tr>
				<td colspan="3">
					<span fcklang="DlgGenLongDescr">Long Description URL</span><br />
					<input id="txtLongDesc" style="width: 100%" type="text" />
				</td>
			</tr>
			<tr>
				<td colspan="3">&nbsp;
					</td>
			</tr>
			<tr>
				<td valign="top">
					<span fcklang="DlgGenClass">Stylesheet Classes</span><br />
					<input id="txtAttClasses" style="width: 100%" type="text" />
				</td>
				<td>
				</td>
				<td valign="top">
					&nbsp;<span fcklang="DlgGenTitle">Advisory Title</span><br />
					<input id="txtAttTitle" style="width: 100%" type="text" />
				</td>
			</tr>
		</table>
		<span fcklang="DlgGenStyle">Style</span><br />
		<input id="txtAttStyle" style="width: 100%" type="text" />
	</div>
	<script language="javascript" type="text/javascript">
	var suggest_<?= $fieldId ?> = actb (document.getElementById ('<?= $fieldId ?>'), document.getElementById ('txtUrl'), document.getElementById ('<?= $fieldId ?>_upload'), '<?= $archive->getFilter (Archive::IMAGE) ?>');
	addUploadFilter ('<?= $fieldId ?>', '<?= $archive->getFilter (Archive::IMAGE) ?>');
	</script>
</body>
</html>
