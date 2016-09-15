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
 * Link dialog window.
-->
<?php
$instance = Instance::singleton ();

$archive = Archive::singleton ();

$fieldId = '_fck_file_id_';
$fieldName = '_fck_file_name_';
?>
<html>
	<head>
		<title>Link Properties</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-2" />
		<meta name="robots" content="noindex, nofollow" />
		<script src="titan.php?target=loadFile&amp;file=extra/fckEditor/editor/dialog/common/fck_dialog_common.js" type="text/javascript"></script>
		<script src="titan.php?target=loadFile&amp;file=extra/fckEditor/editor/dialog/fck_link/fck_link.js" type="text/javascript"></script>
		<script language="javascript" type="text/javascript" src="titan.php?target=loadFile&file=js/prototype.js"></script>
		<script language="javascript" type="text/javascript">
		String.prototype.namespace = function (separator)
		{
			this.split (separator || '.').inject (window, function (parent, child) {
				return parent [child] = parent [child] || { };
			})
		}
		</script>
		<script language="javascript" type="text/javascript" src="titan.php?target=packer&amp;files=general,common,actb_fck"></script>
		<?= XOAD_Utilities::header('titan.php?target=loadFile&amp;file=xoad') ."\n" ?>
		<script language="javascript" type="text/javascript">
		var tAjax = <?= XOAD_Client::register(new Xoad) ?>;
		
		function loadFile (fileId, fieldId)
		{
			document.getElementById ('txtUrl').value = parent.InstanceUrl () + 'titan.php?target=openFile&fileId=' + fileId;
			
			document.getElementById ('cmbLinkProtocol').value = '';
			
			document.getElementById(fieldId + '_upload').style.display = 'none';
		}
		
		function uploadFile (fieldId)
		{
			var div = document.getElementById (fieldId + '_upload');
			
			if (div.style.display == '')
				div.style.display = 'none';
			else
				div.style.display = '';
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
		
		function UpdatePreview ()
		{}
		
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
	<body>
		<div id="divInfo" style="width: 600px; height: 200px;">
			<span fcklang="DlgLnkType">Link Type</span><br />
			<select id="cmbLinkType" onChange="SetLinkType(this.value);">
				<option value="url" fcklang="DlgLnkTypeURL" selected="selected">URL</option>
				<option value="anchor" fcklang="DlgLnkTypeAnchor">Anchor in this page</option>
				<option value="email" fcklang="DlgLnkTypeEMail">E-Mail</option>
			</select>
			<br />
			<br />
			<div id="divLinkTypeUrl">
				<table cellspacing="0" cellpadding="0" width="100%" border="0" dir="ltr">
					<tr>
						<td colspan="3">
							<span fcklang="DlgImgServer">Server File</span><br />
							<style type="text/css">#img_<?= $fieldId ?>:hover { cursor: pointer; }</style>
							<input type="text" style="width: 250px;" id="<?= $fieldId ?>" title="Buscar arquivo" />
							<img src="titan.php?target=loadFile&amp;file=interface/icon/save.gif" border="0" id="img_<?= $fieldId ?>" style="vertical-align: bottom;" title="Enviar arquivo" onClick="JavaScript: uploadFile ('<?= $fieldId ?>');" />
							<div id="<?= $fieldId ?>_upload" style="display: none; position: relative; width: 300px; height: 106px; border: #CCCCCC 1px solid; margin-top: 2px;">
								<iframe class="iframeFile" src="titan.php?target=upload&fieldId=<?= $fieldId ?>" border="0"></iframe>
							</div>
						</td>
					</tr>
					<tr height="10px"><td colspan="3"></td></tr>
					<tr>
						<td nowrap="nowrap">
							<span fcklang="DlgLnkProto">Protocol</span><br />
							<select id="cmbLinkProtocol">
								<option value="http://" selected="selected">http://</option>
								<option value="https://">https://</option>
								<option value="ftp://">ftp://</option>
								<option value="news://">news://</option>
								<option value="" fcklang="DlgLnkProtoOther">&lt;other&gt;</option>
							</select>
						</td>
						<td nowrap="nowrap">&nbsp;</td>
						<td nowrap="nowrap" width="100%">
							<span fcklang="DlgLnkURL">URL</span><br />
							<input id="txtUrl" style="WIDTH: 100%" type="text" onKeyUp="OnUrlChange();" onChange="OnUrlChange();" />
						</td>
					</tr>
				</table>
				<br />
				<div id="divBrowseServer">
				<input type="button" value="Browse Server" fcklang="DlgBtnBrowseServer" onClick="BrowseServer();" style="display: none;" />
				</div>
			</div>
			<div id="divLinkTypeAnchor" style="DISPLAY: none" align="center">
				<div id="divSelAnchor" style="DISPLAY: none">
					<table cellspacing="0" cellpadding="0" border="0" width="70%">
						<tr>
							<td colspan="3">
								<span fcklang="DlgLnkAnchorSel">Select an Anchor</span>
							</td>
						</tr>
						<tr>
							<td width="50%">
								<span fcklang="DlgLnkAnchorByName">By Anchor Name</span><br />
								<select id="cmbAnchorName" onChange="GetE('cmbAnchorId').value='';" style="WIDTH: 100%">
									<option value="" selected="selected"></option>
								</select>
							</td>
							<td>&nbsp;&nbsp;&nbsp;</td>
							<td width="50%">
								<span fcklang="DlgLnkAnchorById">By Element Id</span><br />
								<select id="cmbAnchorId" onChange="GetE('cmbAnchorName').value='';" style="WIDTH: 100%">
									<option value="" selected="selected"></option>
								</select>
							</td>
						</tr>
					</table>
				</div>
				<div id="divNoAnchor" style="DISPLAY: none">
					<span fcklang="DlgLnkNoAnchors">&lt;No anchors available in the document&gt;</span>
				</div>
			</div>
			<div id="divLinkTypeEMail" style="DISPLAY: none">
				<span fcklang="DlgLnkEMail">E-Mail Address</span><br />
				<input id="txtEMailAddress" style="WIDTH: 100%" type="text" /><br />
				<span fcklang="DlgLnkEMailSubject">Message Subject</span><br />
				<input id="txtEMailSubject" style="WIDTH: 100%" type="text" /><br />
				<span fcklang="DlgLnkEMailBody">Message Body</span><br />
				<textarea id="txtEMailBody" style="WIDTH: 100%" rows="3" cols="20"></textarea>
			</div>
		</div>
		<div id="divUpload" style="DISPLAY: none">
			<form id="frmUpload" method="post" target="UploadWindow" enctype="multipart/form-data" action="" onSubmit="return CheckUpload();">
				<span fcklang="DlgLnkUpload">Upload</span><br />
				<input id="txtUploadFile" style="WIDTH: 100%" type="file" size="40" name="NewFile" /><br />
				<br />
				<input id="btnUpload" type="submit" value="Send it to the Server" fcklang="DlgLnkBtnUpload" />
				<iframe name="UploadWindow" style="DISPLAY: none" src="javascript:void(0)"></iframe>
			</form>
		</div>
		<div id="divTarget" style="DISPLAY: none">
			<table cellspacing="0" cellpadding="0" width="100%" border="0">
				<tr>
					<td nowrap="nowrap">
						<span fcklang="DlgLnkTarget">Target</span><br />
						<select id="cmbTarget" onChange="SetTarget(this.value);">
							<option value="" fcklang="DlgGenNotSet" selected="selected">&lt;not set&gt;</option>
							<option value="frame" fcklang="DlgLnkTargetFrame">&lt;frame&gt;</option>
							<option value="popup" fcklang="DlgLnkTargetPopup">&lt;popup window&gt;</option>
							<option value="_blank" fcklang="DlgLnkTargetBlank">New Window (_blank)</option>
							<option value="_top" fcklang="DlgLnkTargetTop">Topmost Window (_top)</option>
							<option value="_self" fcklang="DlgLnkTargetSelf">Same Window (_self)</option>
							<option value="_parent" fcklang="DlgLnkTargetParent">Parent Window (_parent)</option>
						</select>
					</td>
					<td>&nbsp;</td>
					<td id="tdTargetFrame" nowrap="nowrap" width="100%">
						<span fcklang="DlgLnkTargetFrameName">Target Frame Name</span><br />
						<input id="txtTargetFrame" style="WIDTH: 100%" type="text" onKeyUp="OnTargetNameChange();"
							onchange="OnTargetNameChange();" />
					</td>
					<td id="tdPopupName" style="DISPLAY: none" nowrap="nowrap" width="100%">
						<span fcklang="DlgLnkPopWinName">Popup Window Name</span><br />
						<input id="txtPopupName" style="WIDTH: 100%" type="text" />
					</td>
				</tr>
			</table>
			<br />
			<table id="tablePopupFeatures" style="DISPLAY: none" cellspacing="0" cellpadding="0" align="center"
				border="0">
				<tr>
					<td>
						<span fcklang="DlgLnkPopWinFeat">Popup Window Features</span><br />
						<table cellspacing="0" cellpadding="0" border="0">
							<tr>
								<td valign="top" nowrap="nowrap" width="50%">
									<input id="chkPopupResizable" name="chkFeature" value="resizable" type="checkbox" /><label for="chkPopupResizable" fcklang="DlgLnkPopResize">Resizable</label><br />
									<input id="chkPopupLocationBar" name="chkFeature" value="location" type="checkbox" /><label for="chkPopupLocationBar" fcklang="DlgLnkPopLocation">Location
										Bar</label><br />
									<input id="chkPopupManuBar" name="chkFeature" value="menubar" type="checkbox" /><label for="chkPopupManuBar" fcklang="DlgLnkPopMenu">Menu
										Bar</label><br />
									<input id="chkPopupScrollBars" name="chkFeature" value="scrollbars" type="checkbox" /><label for="chkPopupScrollBars" fcklang="DlgLnkPopScroll">Scroll
										Bars</label>
								</td>
								<td></td>
								<td valign="top" nowrap="nowrap" width="50%">
									<input id="chkPopupStatusBar" name="chkFeature" value="status" type="checkbox" /><label for="chkPopupStatusBar" fcklang="DlgLnkPopStatus">Status
										Bar</label><br />
									<input id="chkPopupToolbar" name="chkFeature" value="toolbar" type="checkbox" /><label for="chkPopupToolbar" fcklang="DlgLnkPopToolbar">Toolbar</label><br />
									<input id="chkPopupFullScreen" name="chkFeature" value="fullscreen" type="checkbox" /><label for="chkPopupFullScreen" fcklang="DlgLnkPopFullScrn">Full
										Screen (IE)</label><br />
									<input id="chkPopupDependent" name="chkFeature" value="dependent" type="checkbox" /><label for="chkPopupDependent" fcklang="DlgLnkPopDependent">Dependent
										(Netscape)</label>
								</td>
							</tr>
							<tr>
								<td valign="top" nowrap="nowrap" width="50%">&nbsp;</td>
								<td></td>
								<td valign="top" nowrap="nowrap" width="50%"></td>
							</tr>
							<tr>
								<td valign="top">
									<table cellspacing="0" cellpadding="0" border="0">
										<tr>
											<td nowrap="nowrap"><span fcklang="DlgLnkPopWidth">Width</span></td>
											<td>&nbsp;<input id="txtPopupWidth" type="text" maxlength="4" size="4" /></td>
										</tr>
										<tr>
											<td nowrap="nowrap"><span fcklang="DlgLnkPopHeight">Height</span></td>
											<td>&nbsp;<input id="txtPopupHeight" type="text" maxlength="4" size="4" /></td>
										</tr>
									</table>
								</td>
								<td>&nbsp;&nbsp;</td>
								<td valign="top">
									<table cellspacing="0" cellpadding="0" border="0">
										<tr>
											<td nowrap="nowrap"><span fcklang="DlgLnkPopLeft">Left Position</span></td>
											<td>&nbsp;<input id="txtPopupLeft" type="text" maxlength="4" size="4" /></td>
										</tr>
										<tr>
											<td nowrap="nowrap"><span fcklang="DlgLnkPopTop">Top Position</span></td>
											<td>&nbsp;<input id="txtPopupTop" type="text" maxlength="4" size="4" /></td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</div>
		<div id="divAttribs" style="DISPLAY: none">
			<table cellspacing="0" cellpadding="0" width="100%" align="center" border="0">
				<tr>
					<td valign="top" width="50%">
						<span fcklang="DlgGenId">Id</span><br />
						<input id="txtAttId" style="WIDTH: 100%" type="text" />
					</td>
					<td width="1"></td>
					<td valign="top">
						<table cellspacing="0" cellpadding="0" width="100%" align="center" border="0">
							<tr>
								<td width="60%">
									<span fcklang="DlgGenLangDir">Language Direction</span><br />
									<select id="cmbAttLangDir" style="WIDTH: 100%">
										<option value="" fcklang="DlgGenNotSet" selected>&lt;not set&gt;</option>
										<option value="ltr" fcklang="DlgGenLangDirLtr">Left to Right (LTR)</option>
										<option value="rtl" fcklang="DlgGenLangDirRtl">Right to Left (RTL)</option>
									</select>
								</td>
								<td width="1%">&nbsp;&nbsp;&nbsp;</td>
								<td nowrap="nowrap"><span fcklang="DlgGenAccessKey">Access Key</span><br />
									<input id="txtAttAccessKey" style="WIDTH: 100%" type="text" maxlength="1" size="1" />
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td valign="top" width="50%">
						<span fcklang="DlgGenName">Name</span><br />
						<input id="txtAttName" style="WIDTH: 100%" type="text" />
					</td>
					<td width="1"></td>
					<td valign="top">
						<table cellspacing="0" cellpadding="0" width="100%" align="center" border="0">
							<tr>
								<td width="60%">
									<span fcklang="DlgGenLangCode">Language Code</span><br />
									<input id="txtAttLangCode" style="WIDTH: 100%" type="text" />
								</td>
								<td width="1%">&nbsp;&nbsp;&nbsp;</td>
								<td nowrap="nowrap">
									<span fcklang="DlgGenTabIndex">Tab Index</span><br />
									<input id="txtAttTabIndex" style="WIDTH: 100%" type="text" maxlength="5" size="5" />
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td valign="top" width="50%">&nbsp;</td>
					<td width="1"></td>
					<td valign="top"></td>
				</tr>
				<tr>
					<td valign="top" width="50%">
						<span fcklang="DlgGenTitle">Advisory Title</span><br />
						<input id="txtAttTitle" style="WIDTH: 100%" type="text" />
					</td>
					<td width="1">&nbsp;&nbsp;&nbsp;</td>
					<td valign="top">
						<span fcklang="DlgGenContType">Advisory Content Type</span><br />
						<input id="txtAttContentType" style="WIDTH: 100%" type="text" />
					</td>
				</tr>
				<tr>
					<td valign="top">
						<span fcklang="DlgGenClass">Stylesheet Classes</span><br />
						<input id="txtAttClasses" style="WIDTH: 100%" type="text" />
					</td>
					<td></td>
					<td valign="top">
						<span fcklang="DlgGenLinkCharset">Linked Resource Charset</span><br />
						<input id="txtAttCharSet" style="WIDTH: 100%" type="text" />
					</td>
				</tr>
			</table>
			<table cellspacing="0" cellpadding="0" width="100%" align="center" border="0">
				<tr>
					<td>
						<span fcklang="DlgGenStyle">Style</span><br />
						<input id="txtAttStyle" style="WIDTH: 100%" type="text" />
					</td>
				</tr>
			</table>
		</div>
		<script language="javascript" type="text/javascript">
		var suggest_<?= $fieldId ?> = actb (document.getElementById ('<?= $fieldId ?>'), document.getElementById ('txtUrl'), document.getElementById ('<?= $fieldId ?>_upload'), '');
		addUploadFilter ('<?= $fieldId ?>', '');
		</script>
	</body>
</html>
