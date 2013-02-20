<div id="idForm" style="text-align: center;">
<form id="form_<?= $form->getAssign () ?>" enctype="multipart/form-data" action="titan.php?target=commit&toSection=<?= $section->getName () ?>&toAction=<?= $action->getName () ?>" method="post">
	<input id="fileField" type="file" name="file_1" />
</form>
</div>
<fieldset class="uploadList">
	<legend><?= __ ("Files to send (<i>upload</i>)")?></legend>
	<div id="fileList" style="text-align: center;"></div>
</fieldset>
<script language="javascript" type="text/javascript">
	var multi_selector = new MultiSelector (document.getElementById ('fileList'), 8);
	multi_selector.addElement (document.getElementById ('fileField'));
</script>