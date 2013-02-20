<? include Template::import ('global.create') ?>
<div id="idForm">
	<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
		<tr height="18px">
			<td width="20%"></td>
			<td>
				<img style="border: #666 2px solid;" src="titan.php?target=captcha&sid=<?= md5 (uniqid (time ())) ?>" id="_TITAN_CAPTCHA_IMAGE_" alt="Titan Framework Secure Captcha" title="Titan Framework Secure Captcha" />
				<img style="cursor: pointer;" src="titan.php?target=loadFile&file=interface/image/reload.png" onclick="JavaScript: $('_TITAN_CAPTCHA_IMAGE_').src = 'titan.php?target=captcha&sid=' + Math.random(); return false;" alt="Reload" title="Reload" />
			</td>
		</tr>
		<tr height="18px">
			<td width="20%"></td>
			<td>
				<?= __ ('To proceed, enter the characters of image into the field below') ?>:
			</td>
		</tr>
		<tr height="18px">
			<td width="20%"></td>
			<td>
				<input type="text" class="captcha" name="_TITAN_CAPTCHA_" id="_TITAN_CAPTCHA_FIELD_" maxlength="5" />
			</td>
		</tr>
		<tr height="10px"><td></td></tr>
		<tr height="18px">
			<td width="20%"></td>
			<td>
				<input type="button" class="proceedRegister" value="<?= __ ('Proceed Register') ?> &raquo;" onclick="JavaScript: saveRegister ('<?= $form->getFile () ?>', 'form_<?= $form->getAssign () ?>', this);" />
				<input type="reset" class="cancelRegister" value="<?= __ ('Cancel') ?>" onclick="JavaScript: $('form_<?= $form->getAssign () ?>').reset (); $('_TITAN_CAPTCHA_FIELD_').value = '';" />
			</td>
		</tr>
		<tr height="2px"><td></td></tr>
	</table>
</div>