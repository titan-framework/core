<script language="javascript" type="text/javascript">
function runOnLoad ()
{
	showWait ();

	initDragableBoxesScript ();

	tAjax.delay (function () { hideWait (); });
}
</script>
<div id="idSearch" style="display: none;">
	<form id="formAddFeed" action="" method="post">
	<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
		<tr>
			<td colspan="3" class="cTitle"><?= __ ('Insert New Feed RSS') ?></td>
		</tr>
	</table>
	<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0" style="border: #36817C 1px solid; border-top-width: 3px;">
		<tr height="5px"><td></td></tr>
		<tr height="18px" style="background-color: #FFFFFF;">
			<td width="20%" nowrap style="text-align: right;"><b><?= __ ('URL of Feed RSS') ?>:</b></td>
			<td><input type="text" class="field" name="rssUrl" value="" maxlength="512" /></td>
		</tr>
		<tr height="2px"><td></td></tr>
		<tr height="18px" style="background-color: #F4F4F4;">
			<td width="20%" nowrap style="text-align: right;"><b><?= __ ('Quantity') ?>:</b></td>
			<td><input type="text" class="field" name="items" value="5" onkeypress="JavaScript: return formatInteger (this, event);" onkeyup="JavaScript: formatInteger (this,false);" /> <?= __ ('items') ?></td>
		</tr>
		<tr height="2px"><td></td></tr>
		<tr height="18px" style="background-color: #FFFFFF;">
			<td width="20%" nowrap style="text-align: right;"><b><?= __ ('Update on each') ?>:</b></td>
			<td><input type="text" class="field" name="reloadInterval" value="30" onkeypress="JavaScript: return formatInteger (this, event);" onkeyup="JavaScript: formatInteger (this,false);" /> <?= __ ('minutes') ?></td>
		</tr>
		<tr height="2px"><td></td></tr>
		<tr height="18px" style="background-color: #F4F4F4;">
			<td width="20%" nowrap style="text-align: right;"><b><?= __ ('Height (0 = auto)') ?>:</b></td>
			<td><input type="text" class="field" name="height" value="0" onkeypress="JavaScript: return formatInteger (this, event);" onkeyup="JavaScript: formatInteger (this,false);" /> pixels</td>
		</tr>
		<tr height="2px"><td></td></tr>
		<tr>
			<td></td>
			<td colspan="2">
				<input type="button" class="button" value="<?= __ ('Create') ?>" onclick="JavaScript: createFeed (this.form);" />
				<input type="button" class="button" value="<?= __ ('Cancel') ?>" onclick="JavaScript: showSearch ();" />
			</td>
		</tr>
		<tr height="5px"><td></td></tr>
	</table>
	</form>
</div>
<div id="floatingBoxParentContainer"></div>
<div id="debug"></div>