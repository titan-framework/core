<?
$db = Database::singleton ();

$sql = "SELECT n.*, u._name,
		EXTRACT (EPOCH FROM n._change) AS _change,
		CASE WHEN n._change IS NOT NULL THEN n._change ELSE '1960/1/1'::TIMESTAMPTZ END AS _order
		FROM _note n
		JOIN _user u ON u._id = n._user
		WHERE n._code IN ('". implode ("', '", $field->getValue ()) ."') AND n._deleted = B'0'
		ORDER BY _order DESC, n._id DESC";

$sth = $db->prepare ($sql);

$sth->execute ();

ob_start ();
?>
<div id="idList" style="margin: 0px;">
	<table style="background-color: #FFF;">
		<tr>
			<td class="cTableHeader" width="5%"></td>
			<td class="cTableHeader" width="15%"><?= __ ('Title') ?></td>
			<td class="cTableHeader" width="45%"><?= __ ('Note') ?></td>
			<td class="cTableHeader" width="15%"><?= __ ('Last Change') ?></td>
			<td class="cTableHeader" width="15%"><?= __ ('Author') ?></td>
			<td class="cTableHeader" width="5%"></td>
		</tr>
		<tr height="5px"><td colspan="6"></td></tr>
		<?
		while ($obj = $sth->fetch (PDO::FETCH_OBJ))
			if (is_null ($obj->_change) || is_null ($obj->_devise) || is_null ($obj->_author))
			{
				?>
				<tr class="cTableItem">
					<td><input type="checkbox" name="<?= $fieldName ?>[]" value="<?= $obj->_code ?>" checked="checked" /></td>
					<td colspan="3" style="background: url(titan.php?target=tResource&type=Note&file=sandglass.png) no-repeat 6px; padding-left: 28px; line-height: 22px;"><?= __ ('The note of code <b>[1]</b> was sent, but not yet ready to be displayed.', $obj->_code) ?></td>
					<td><?= $obj->_name ?></td>
					<td></td>
				</tr>
				<tr class="cSeparator"><td colspan="6"></td></tr>
				<?
			}
			else
			{
				?>
				<tr class="cTableItem">
					<td><input type="checkbox" name="<?= $fieldName ?>[]" value="<?= $obj->_code ?>" checked="checked" /></td>
					<td><?= $obj->_title ?></td>
					<td><?= $obj->_note ?></td>
					<td><?= strftime ('%x %X', $obj->_change) ?></td>
					<td><?= $obj->_name ?></td>
					<td class="icon" nowrap="nowrap">
						<img src="titan.php?target=tResource&type=Note&file=earth.png" class="icon" border="0" title="<?= __ ('Tracking') ?>" alt="<?= __ ('Tracking') ?>" onclick="JavaScript: global.Note.earth ('<?= $obj->_id ?>');" />&nbsp;
					</td>
				</tr>
				<tr class="cSeparator"><td colspan="6"></td></tr>
				<?
			}
		?>
	</table>
</div>
<?
return ob_get_clean ();
?>