<style type="text/css">
.cArrow
{
	cursor: pointer;
}
</style>
<table align="center" border="0" width="98%" cellpadding="0" cellspacing="0">
	<tr height="10"><td></td></tr>
	<form id="form_<?= $form->getAssign () ?>" name="permission_form" action="<?= $_SERVER['PHP_SELF'] .'?target=commit&toSection='. $section->getName () . '&toAction='. $action->getName () .'&itemId='. $itemId ?>" method="post">
	<input type="hidden" name="commitId" value="<?= $itemId ?>" />
	<tr>
		<td colspan="3">
			<table align="center" border="0" width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td style="font-weight: bold; border-bottom-color: #CCCCCC; border-bottom-style: solid; border-bottom-width: 1px;">
						Alertas das Seções do Sistema
					</td>
				</tr>
				<tr height="10"><td></td></tr>
				<tr>
					<td>
						<table align="center" border="0" width="100%" cellpadding="5" cellspacing="0">
							<?
							$backSecColor = 'FFFFFF';
							foreach ($arrayMain as $key => $arraySection)
							{
								$backSecColor = $backSecColor == 'FFFFFF' ? 'F4F4F4' : 'FFFFFF';
								
								$nameSection = $arraySection ['_TITAN_SECTION_'];
								
								unset ($arraySection ['_TITAN_SECTION_']);
								
								$keySection = $key;
								
								$jsKey = str_replace ('.', '_', $key);
								?>
								<tr height="20" style="background-color: #<?= $backSecColor ?>;">
									<td style="text-align: center; width: 30px;">
										<? if (sizeof ($arraySection)) { ?>
											<img id="arrow_<?= $key ?>" class="cArrow" src="<?= Skin::singleton ()->getIconsFolder () .'display.down.gif' ?>" border="0" onclick="JavaScript: showPermissionRow ('<?= $key ?>');" title="Clique para configurar." />
										<? } else { ?>
											<img id="arrow_<?= $key ?>" class="cArrow" style="cursor: auto;" src="<?= Skin::singleton ()->getIconsFolder () .'grey/display.gif' ?>" border="0" title="Não há avisos disponíveis." />
										<? } ?>
									</td>
									<td>Alertas da seção <b><?= $nameSection ?></b></td>
								</tr>
								<tr id="rowForActions_<?= $keySection ?>" style="display: none;">
									<td colspan="2" style="border: #990000 1px solid;">
										<table align="center" border="0" width="100%" cellpadding="0" cellspacing="0">
											<tr>
												<td style="vertical-align: top;">
													<table align="center" border="0" width="100%" cellpadding="0" cellspacing="0">
														<tr height="10"><td></td></tr>
														<tr>
															<td style="font-weight: bold; border-bottom-color: #CCCCCC; border-bottom-style: solid; border-bottom-width: 1px;">
																Alertas da Seção  <?= $nameSection ?>
															</td>
														</tr>
														<tr height="10"><td></td></tr>
														<tr>
															<td>
																<table align="center" border="0" width="100%" cellpadding="5" cellspacing="0">
																	<tr height="20" style="background-color: #F4F4F4;">
																		<td width="20"><input type="checkbox" name="SELECT_ALL_PERM_<?= $jsKey ?>" onclick="JavaScript: selectAllPerm_<?= $jsKey ?> ();" /></td>
																		<td>Selecionar Todas</td>
																	</tr>
																	<?
																	$backColor = 'F4F4F4';
																	$cont = 0;
																	foreach ($arraySection as $key => $mail)
																	{
																		$backColor = $backColor == 'FFFFFF' ? 'F4F4F4' : 'FFFFFF';
																		?>
																		<tr height="20" style="background-color: #<?= $backColor ?>;">
																			<td width="20"><input type="checkbox" id="checkbox<?= $keySection ?>.<?= $cont++ ?>" name="<?= $key ?>" <?= (in_array ($key, $arrayHas)) ? 'checked' : '' ?> /></td>
																			<td><?= $mail ['label'] ?></td>
																		</tr>
																		<?
																	}
																	?>
																</table>
																<script language="javascript" type="text/javascript">
																	function selectAllPerm_<?= $jsKey ?> ()
																	{
																		var check = false, i;
																		
																		if (document.permission_form.SELECT_ALL_PERM_<?= $jsKey ?>.checked)
																			check = true;
																		
																		var key = '<?= $keySection ?>';
																		
																		counter = 0;
																		while (obj = document.getElementById ('checkbox' + key + '.' + counter++))
																			obj.checked = check;
																	}
																</script>
															</td>
														</tr>
													</table>
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<? 
							}
							?>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	</form>
	<tr height="10"><td></td></tr>
</table>