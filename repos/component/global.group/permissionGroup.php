<style type="text/css">
.cArrow:hover
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
						Permissões de Acesso às Seções do Sistema
					</td>
				</tr>
				<tr height="10"><td></td></tr>
				<tr>
					<td>
						<table align="center" border="0" width="100%" cellpadding="5" cellspacing="0">
							<?
							$backSecColor = 'FFFFFF';
							foreach ($arrayMain as $key => $arrayAux)
							{
								$backSecColor = $backSecColor == 'FFFFFF' ? 'F4F4F4' : 'FFFFFF';
								?>
								<tr height="20" style="background-color: #<?= $backSecColor ?>;">
									<td width="20">
										<input type="checkbox" id="main_<?= $key ?>" name="ACCESS_SECTION_<?= $key ?>" <?= (in_array ('ACCESS_SECTION_'. $key, $arrayHas) || $business->getSection (Section::TDEFAULT)->getName () == $key) ? 'checked' : '' ?> onclick="JavaScript: enableSection ('<?= $key ?>');" <?= $business->getSection (Section::TDEFAULT)->getName () == $key ? 'disabled' : '' ?> />
										<? 
										if ($business->getSection (Section::TDEFAULT)->getName () == $key)
										{
											?>
											<input type="hidden" name="ACCESS_SECTION_<?= $key ?>" value="1" />
											<?
										}
										?>
									</td>
									<td><a href="#" id="link_<?= $key ?>" onclick="JavaScript: <?= in_array ('ACCESS_SECTION_'. $key, $arrayHas) || $business->getSection (Section::TDEFAULT)->getName () == $key ? 'showPermissionRow (\''. $key .'\')' : 'enableSection (\''. $key .'\', true)' ?>;" style="color: #575556;">Permissão de acesso à seção <b><?= $arrayAux ['SECTION'] ?></b></a></td>
									<td style="text-align: right;">
										<img id="arrow_<?= $key ?>" class="cArrow" style="display: <?= (in_array ('ACCESS_SECTION_'. $key, $arrayHas) || $business->getSection (Section::TDEFAULT)->getName () == $key) ? '' : 'none' ?>" src="<?= Skin::singleton ()->getIconsFolder () .'display.down.gif' ?>" border="0" onclick="JavaScript: showPermissionRow ('<?= $key ?>');" />
									</td>
								</tr>
								<?
								$keySection = $key;
								
								$jsKey = str_replace ('.', '_', $key);
								
								$arraySection = $arrayMain [$keySection];
								
								$breadcrumb = $arraySection ['SECTION'];
								$nameSection = array_pop (explode (' &raquo; ', $arraySection ['SECTION']));
								$defaultAction = $arraySection ['ACTION'];
								
								unset ($arraySection ['SECTION']);
								unset ($arraySection ['ACTION']);
								?>
								<tr id="rowForActions_<?= $keySection ?>" style="display: none;">
									<td colspan="3" style="border: #990000 1px solid;">
										<table align="center" border="0" width="100%" cellpadding="0" cellspacing="0">
											<tr>
												<td style="vertical-align: top; padding: 10px;">
													<table align="center" border="0" width="100%" cellpadding="0" cellspacing="0">
														<tr>
															<td style="font-weight: bold; border-bottom-color: #CCCCCC; border-bottom-style: solid; border-bottom-width: 1px;">
																Acesso às Ações da Seção <?= $nameSection ?>
															</td>
														</tr>
														<tr height="10"><td></td></tr>
														<tr>
															<td>
																<table align="center" border="0" width="100%" cellpadding="5" cellspacing="0">
																	<tr height="20" style="background-color: #FFFFFF;">
																		<td width="20"><input type="checkbox" name="SELECT_ALL_<?= $jsKey ?>" onclick="JavaScript: selectAll_<?= $jsKey ?> ();" /></td>
																		<td>Selecionar Todas</td>
																	</tr>
																	<?
																	$backColor = 'FFFFFF';
																	$cont = 0;
																	foreach ($arraySection as $key => $label)
																	{
																		$backColor = $backColor == 'FFFFFF' ? 'F4F4F4' : 'FFFFFF';
																		?>
																		<tr height="20" style="background-color: #<?= $backColor ?>;">
																			<td width="20"><input type="checkbox" id="checkboxActions<?= $keySection ?>.<?= $cont++ ?>" name="ACCESS_ACTION_<?= $keySection ?>_<?= $key ?>" <?= (in_array ('ACCESS_ACTION_'. $keySection .'_'. $key, $arrayHas) || $defaultAction == $key) ? 'checked' : '' ?> <?= $defaultAction == $key ? 'disabled' : '' ?> /></td>
																			<td><?= $label ?></td>
																		</tr>
																		<? 
																		if ($defaultAction == $key)
																		{
																			?>
																			<input type="hidden" name="ACCESS_ACTION_<?= $keySection ?>_<?= $key ?>" value="1" />
																			<?
																		}
																	}
																	?>
																</table>
																<script language="javascript" type="text/javascript">
																	function selectAll_<?= $jsKey ?> ()
																	{
																		var check = false, i;
																		
																		if (document.permission_form.SELECT_ALL_<?= $jsKey ?>.checked)
																			check = true;
																		
																		var key = '<?= $keySection ?>';
																		
																		counter = 0;
																		while (obj = document.getElementById ('checkboxActions' + key + '.' + counter++))
																			if (!obj.disabled)
																				obj.checked = check;
																	}
																</script>
															</td>
														</tr>
													</table>
												</td>
												<td width="10px"></td>
												<td style="vertical-align: top;">
													<table align="center" border="0" width="100%" cellpadding="0" cellspacing="0">
														<tr height="10"><td></td></tr>
														<tr>
															<td style="font-weight: bold; border-bottom-color: #CCCCCC; border-bottom-style: solid; border-bottom-width: 1px;">
																Permissões da Seção  <?= $nameSection ?>
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
																	if (array_key_exists ($keySection, $arrayPermission) && is_array ($arrayPermission [$keySection]))
																		foreach ($arrayPermission [$keySection] as $key => $label)
																		{
																			$backColor = $backColor == 'FFFFFF' ? 'F4F4F4' : 'FFFFFF';
																			?>
																			<tr height="20" style="background-color: #<?= $backColor ?>;">
																				<td width="20"><input type="checkbox" id="checkbox<?= $keySection ?>.<?= $cont++ ?>" name="PERMISSION_<?= $keySection ?>_<?= $key ?>" <?= (in_array ('PERMISSION_'. $keySection .'_'. $key, $arrayHas)) ? 'checked' : '' ?> /></td>
																				<td><?= $label ?></td>
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