<div id="idSearchParams" >
	<form id="searchParams" action="<?= $_SERVER['PHP_SELF'] .'?target=body&toSection='. $section->getName () .'&toAction='. $action->getName () ?>" method="post">
		<input type="hidden" name="search" value="<?= Search::TCLEAR ?>" />
	</form>
	<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
		<tr>
			<td class="cTitle"><?=__ ('Search Criteria Selected')?></td>
			<td class="cClear">
				<a href="#" onclick="JavaScript: document.getElementById ('searchParams').submit ();"><?=__ ('Clean Criteria')?></a>
				&nbsp;|&nbsp;
				<a href="#" onclick="JavaScript: showSearch ();"><?=__ ('Change Criteria')?></a>
			</td>
		</tr>
	</table>
	<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0" style="border: #36817C 1px solid; border-top-width: 3px;">
		<tr height="5px"><td></td></tr>
		<?php
		$backColor = 'FFFFFF';
		while ($field = $search->getField ())
		{
			if ($field->isEmpty ())
				continue;

			$backColor = $backColor == 'FFFFFF' ? 'F4F4F4' : 'FFFFFF';
			?>
			<tr height="18px" style="background-color: #<?= $backColor ?>;">
				<td width="20%" nowrap style="text-align: right;"><b><?= $field->getLabel () ?>:</b></td>
				<td <?= $search->isBlocked ($field) ? 'style="color: #990000;"' : '' ?>><?= Form::toHtml ($field) ?></td>
			</tr>
			<tr height="2px"><td></td></tr>
			<?php
		}

		if(!$fieldDate->isEmpty() || !$fieldDateEnd->isEmpty())
		{
			$backColor = $backColor == 'FFFFFF' ? 'F4F4F4' : 'FFFFFF';

			?>
			<tr height="18px" style="background-color: #<?= $backColor ?>;">
				<td width="20%" nowrap style="text-align: right;"><b>Data da Atividade:</b></td>
				<td> <?=  Form::toHtml ($fieldDate)." à ".Form::toHtml($fieldDateEnd) ?></td>
			</tr>
			<tr height="2px"><td></td></tr>
			<?php
		}

	?>

		<tr height="5px"><td></td></tr>
	</table>
</div>
<div id="idSearch" style="display: none;">
	<form action="<?= $_SERVER['PHP_SELF'] .'?target=body&toSection='. $section->getName () .'&toAction='. $action->getName () ?>" method="post">
		<input type="hidden" name="search" value="<?= Search::TSEARCH ?>" />
		<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
			<tr>
				<td colspan="3" class="cTitle"><?=__ ('Search Itens')?></td>
			</tr>
		</table>
		<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0" style="border: #36817C 1px solid; border-top-width: 3px;">
			<tr height="5px"><td></td></tr>
			<?php
			$backColor = 'FFFFFF';
			while ($field = $search->getField ())
			{
				$backColor = $backColor == 'FFFFFF' ? 'F4F4F4' : 'FFFFFF';
				?>
				<tr height="18px" style="background-color: #<?= $backColor ?>;">
					<td width="20%" nowrap style="text-align: right;"><b><?= $field->getLabel () ?>:</b></td>
					<td><?= $search->isBlocked ($field) ? Form::toHtml ($field) : Search::toForm ($field) ?></td>
					<td width="20px" style="vertical-align: top;"><?= Form::toHelp ($field); ?></td>
				</tr>
				<tr height="2px"><td></td></tr>
				<?php
			}
			?>
            <?php $backColor = $backColor == 'FFFFFF' ? 'F4F4F4' : 'FFFFFF'; 	?>

            <tr height="18px" style="background-color: #<?= $backColor ?>;">
				<td width="20%" nowrap style="text-align: right;"><b> <?=__ ('Date')?>:</b></td>
				<td><?=__ ('from [1] to [2]', Search::toForm ($fieldDate), Search::toForm ($fieldDateEnd))?></td>
				<td width="20px" style="vertical-align: top;"></td>
			</tr>
			<tr height="2px"><td></td></tr>

			<tr>
				<td></td>
				<td colspan="2">
					<input type="submit" class="button" value="<?=__ ('Search')?>" />
					<input type="button" class="button" value="<?=__ ('Cancel')?>" onclick="JavaScript: showSearch ();" />
				</td>
			</tr>
			<tr height="5px"><td></td></tr>
		</table>
	</form>
</div>

<div id="idListCalendar">
<div class="Calendar">
<div class="NavMenu"  >

<center>
<div id="header">
<ul>
	<li><a href="titan.php?target=body&toSection=<?=$section->getName();?>&toAction=<?= $action->getName () ?>&dateShow=1-12-<?=$yearShow-1?>">&nbsp;&laquo;&nbsp;<?=$yearShow-1;?></a></li>
	<?php foreach($monthArray as $key=>$month) { ?>
    <li <?=$monthShow==$key?" id='current' ":""?>><a href="titan.php?target=body&toAction=calendar&toSection=<?=$section->getName();?>&dateShow=1-<?=$key?>-<?=$yearShow?>"><?=$month?></a></li>
	<?php } ?>
    <li><a href="titan.php?target=body&toSection=<?=$section->getName();?>&toAction=<?= $action->getName () ?>&dateShow=1-1-<?=$yearShow+1?>"><?=$yearShow+1;?>&nbsp;&raquo;&nbsp;</a></li>
</ul>
</div>
</center>

</div>

<div style="clear:both;"></div>


<div class="Canvas" >
<table class=calendar  cellspacing="0" >
<caption class="MonthCaption"  style="background-image:url('')"> <?=$monthArrayExt[$monthShow]?> <?=$yearShow?></caption>
<th  width=14% class="Weekdays"><?=__ ('Sunday')?></th>
<th  width=14% class="Weekdays"><?=__ ('Monday')?></th>
<th  width=14% class="Weekdays"><?=__ ('Tuesday')?></th>
<th  width=14% class="Weekdays"><?=__ ('Wednesday')?></th>
<th  width=14% class="Weekdays"><?=__ ('Thursday')?></th>
<th  width=14% class="Weekdays"><?=__ ('Friday')?></th>
<th  width=14% class="Weekdays"><?=__ ('Saturday')?></th>

<tr class="events">
<?php $days=0; ?>

<?php for($i=0;$i<$dayOfWeekFirstDay;$i++){?>
<td valign="top"  class="events" style="background-color:#E7F1DA;" >
</td>
<?php $days++; } ?>

<?php for($i=0;$i<$maxDays[$monthShow];$i++)
{ if($days==7) { ?>
</tr><tr>
<?php $days=0; } ?>

<td valign="top"  class="<?php if (($i+1) == $dayShow) echo "Holidays"; else echo "events"; ?>"  >
<div class="Date" ><?=$i+1?></div>
<?php
 $dateKey = ($i+1)."-".((int)$monthShow)."-".$yearShow;
 $iCount=0;
 if(array_key_exists($dateKey,$itemsCalendar))
 {
	foreach($itemsCalendar[$dateKey] as $itemCalendar)
	{
		echo "&bull;&nbsp;<a href='titan.php?target=body&toSection=".$section->getName()."&toAction=".$defaultAction."&itemId=".$itemCalendar['_ID_']."&dateShow=".$dateShow."'>".$itemCalendar['_TITLE_'];
		if(++$iCount == 3) break;
		echo "</a><br><br>";
	}
 }

 ?>


</td>

<?php $days++; } ?>

<?php if($days<7) { ?>
<td valign="top"  colspan=<?=(7-$days)?> class="events" style="background-color:#E7F1DA;" ></td>
<?php } ?>

</tr>

</table>

</div>

</div>

</div>