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
<div style="display: none"><div id="_TITAN_NOTE_MAP_<?= $fieldId ?>" style="margin: 0px; border: #CCC 2px solid; width: 0px; height: 0px;"></div></div>
<div id="idList" style="margin: 0px;">
	<table style="background-color: #FFF;">
		<tr>
			<td class="cTableHeader" width="15%"><?= __ ('Title') ?></td>
			<td class="cTableHeader" width="50%"><?= __ ('Note') ?></td>
			<td class="cTableHeader" width="15%"><?= __ ('Last Change') ?></td>
			<td class="cTableHeader" width="15%"><?= __ ('Author') ?></td>
			<td class="cTableHeader" width="5%"></td>
		</tr>
		<tr height="5px"><td colspan="5"></td></tr>
		<?
		while ($obj = $sth->fetch (PDO::FETCH_OBJ))
			if (is_null ($obj->_change) || is_null ($obj->_devise) || is_null ($obj->_author))
			{
				?>
				<tr class="cTableItem">
					<td colspan="3" style="background: url(titan.php?target=tResource&type=Note&file=sandglass.png) no-repeat 6px; padding-left: 28px; line-height: 22px;"><?= __ ('The note of code <b>[1]</b> was sent, but not yet ready to be displayed.', $obj->_code) ?></td>
					<td><?= $obj->_name ?></td>
					<td></td>
				</tr>
				<tr class="cSeparator"><td colspan="5"></td></tr>
				<?
			}
			else
			{
				$a = "JavaScript: global.Note.earth ('". $obj->_id ."', '". $fieldId ."', '". $obj->_latitude ."', '". $obj->_longitude ."', '". $obj->_title ."', '". strftime ('%x %X', $obj->_change) ."', '". $obj->_name ."', '". str_replace (array ("'", "\n", "\r"), array ('"', ' ', ''), trim ($obj->_note)) ."');";
				?>
				<tr class="cTableItem">
					<td><a href="#" onclick="<?= $a ?>"><?= $obj->_title ?><a></td>
					<td><a href="#" onclick="<?= $a ?>"><?= $obj->_note ?></a></td>
					<td><a href="#" onclick="<?= $a ?>"><?= strftime ('%x %X', $obj->_change) ?></a></td>
					<td><a href="#" onclick="<?= $a ?>"><?= $obj->_name ?></a></td>
					<td class="icon" nowrap="nowrap">
						<a href="#" onclick="<?= $a ?>"><img src="titan.php?target=tResource&type=Note&file=earth.png" class="icon" border="0" title="<?= __ ('Tracking') ?>" alt="<?= __ ('Tracking') ?>" /></a>&nbsp;
					</td>
				</tr>
				<tr class="cSeparator"><td colspan="5"></td></tr>
				<?
			}
		?>
	</table>
</div>
<?
return ob_get_clean ();
?>