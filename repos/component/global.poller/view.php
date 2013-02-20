<? include Template::import ('global.view') ?>
<div>
	<table align="center" border="0" width="450" cellpadding="0" cellspacing="0">
		<tr>
			<td style="border-bottom: #CCCCCC 1px solid;">
				<b>Resultados:</b>
			</td>
		</tr>
		<tr height="10"><td></td></tr>
		<tr>
			<td>
				<table align="left" border="0" width="1%" cellpadding="5" cellspacing="0">
					<?
					switch ($form->getField ('_GRAPHIC_')->getValue ())
					{
						case '_HORIZONTAL_':
							define ('WIDTH', 200);
							define ('HEIGHT', 10);
							
							foreach ($answer as $key => $array)
							{
								$percentage = $total ? ($array ['_VOTES_'] / $total) * 100 : 0;
								?>
								<tr>
									<td style="font-weight: bold; color: #<?= colors ($key) ?>" nowrap="nowrap">
										<?= $array ['_LABEL_'] ?>
									</td>
									<td>&nbsp;</td>
									<td>
										<img src="titan.php?target=script&toSection=<?= $section->getName () ?>&file=graphicPoint&cor=<?= colors ($key) ?>" height="<?= HEIGHT ?>" width="<?= round (WIDTH * ($percentage / 100) + 2.0) ?>"></td>
									</td>
									<td>&nbsp;</td>
									<td style="font-weight: bold; color: #<?= colors ($key) ?>" align="right">
										<?= number_format ($percentage, 2, ',', '.') ?>%
									</td>
									<td>&nbsp;</td>
									<td style="font-weight: bold; color: #<?= colors ($key) ?>" align="right">
										<?= $array ['_VOTES_'] ?>
									</td>
								</tr>
								<?
							}
							?>
							<tr>
								<td colspan="7">
									<b>Total: <?= $total ?></b>
								</td>
							</tr>
							<?
							break;
							
						case '_VERTICAL_':
							define ('WIDTH', 15);
							define ('HEIGHT', 150);
							$flag = FALSE;
							
							foreach ($answer as $key => $array)
							{
								$percentage = $total ? ($array ['_VOTES_'] / $total) * 100 : 0;			
								?>
								<tr>
									<td style="font-weight: bold; color: #<?= colors ($key) ?>" nowrap="nowrap">
										<?= $array ['_LABEL_'] ?>
									</td>
									<td>&nbsp;</td>
									<td style="font-weight: bold; color: #<?= colors ($key) ?>" align="right">
										<?= number_format ($percentage, 2, ',', '.') ?>%
									</td>
									<td>&nbsp;</td>
									<td style="font-weight: bold; color: #<?= colors ($key) ?>" align="right">
										<?= $array ['_VOTES_'] ?>
									</td>
									<?
									if (!$flag)
									{
										$flag = TRUE;
										$contAnswer = sizeof ($answer);
										?>
										<td rowspan="<?= $contAnswer ?>">
											<table border="0" height="100%" width="100%">
												<tr valign="bottom" height="100%" width="100%">
													<?
													for ($i = 1 ; $i <= $contAnswer ; $i++)
													{
														$percentage = $total ? ($answer [$i]['_VOTES_'] / $total) * 100 : 0;
														echo '<td align="center"><img src="titan.php?target=script&toSection='. $section->getName () .'&file=graphicPoint&cor='. colors ($i) .'" height="'. round (HEIGHT * ($percentage / 100) + 2.0) .'" width="'. WIDTH .'"></td>';
													}
													?>
												</tr>
											</table>
										</td>
										<?
									}
									?>
								</tr>
								<?
							}
							?>
							<tr>
								<td colspan="7">
									<b>Total: <?= $total ?></b>
								</td>
							</tr>
							<?
							break;
							
						case '_PERCENTAGE_':
							foreach ($answer as $key => $array)
							{
								$percentage = $total ? ($array ['_VOTES_'] / $total) * 100 : 0;
								?>
								<tr>
									<td style="font-weight: bold; color: #<?= colors ($key) ?>" nowrap="nowrap">
										<?= $array ['_LABEL_'] ?>
									</td>
									<td>&nbsp;</td>
									<td style="font-weight: bold; color: #<?= colors ($key) ?>" align="right">
										<?= number_format ($percentage, 2, ',', '.') ?>%
									</td>
									<td>&nbsp;</td>
									<td style="font-weight: bold; color: #<?= colors ($key) ?>" align="right">
										<?= $array ['_VOTES_'] ?>
									</td>
								</tr>
								<?
							}
							?>
							<tr>
								<td colspan="7">
									<b>Total: <?= $total ?></b>
								</td>
							</tr>
							<?
							break;
						
						case '_PIZZA_':
							define ('WIDTH', 10);
							define ('HEIGHT', 200);
							$flag = FALSE;
							
							foreach ($answer as $key => $array)
							{
								$percentage = $total ? ($array ['_VOTES_'] / $total) * 100 : 0;
								?>
								<tr>
									<td style="font-weight: bold; color: #<?= colors ($key) ?>" nowrap="nowrap">
										<?= $array ['_LABEL_'] ?>
									</td>
									<td>&nbsp;</td>
									<td style="font-weight: bold; color: #<?= colors ($key) ?>" align="right">
										<?= number_format ($percentage, 2, ',', '.') ?>%
									</td>
									<td>&nbsp;</td>
									<td style="font-weight: bold; color: #<?= colors ($key) ?>" align="right">
										<?= $array ['_VOTES_'] ?>
									</td>
									<?
									if (!$flag)
									{
										$flag = TRUE;
										$contAnswer = sizeof ($answer);
										?>
										<td rowspan="<?= $contAnswer ?>">
											<?
											$pedacos = '';
											for ($i = 1 ; $i <= $contAnswer ; $i++)
												$pedacos .= '&partes['. $i .']=' . ($total ? ($answer [$i]['_VOTES_'] / $total) * 100 : 0);
											?>
											<img src="titan.php?target=script&toSection=<?= $section->getName () ?>&file=graphicPizza&largura=200&altura=200<?= $pedacos ?>" border="0" />
										</td>
										<?
									}
									?>
								</tr>
								<?
							}
							?>
							<tr>
								<td colspan="7">
									<b>Total: <?= $total ?></b>
								</td>
							</tr>
							<?
							break;
					}
					?>
				</table>
			</td>
		</tr>
	</table>
</div>