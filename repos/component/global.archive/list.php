<style type="text/css">
.fieldEdit
{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	font-weight: bold;
	border: #FFFFFF 1px solid;
	width: 400px;
	padding: 3px;
	background-color: #FFFFFF;
}
.bottonFile
{
	vertical-align: bottom; 
	border: #FFFFFF 1px solid; 
	background-color: #FFFFFF; 
	width: 80px;
	color: #000000;
}
</style>
<div id="idList">
	<form id="deleteFilesForm">
	<table id="tableFiles">
		<tr class="cTableHeader">
			<td></td>
			<td>Nome</td>
			<td style="text-align: center;">Tamanho (KBytes)</td>
			<td style="text-align: center;">Enviado em</td>
			<td style="text-align: center;">Downloads</td>
			<td></td>
		</tr>
		<?
		while ($obj = $sth->fetch (PDO::FETCH_OBJ))
		{
			?>
			<tr id="row_<?= $obj->_id ?>_1" class="cTableItem">
				<td style="text-align: center;"><input type="checkbox" name="delete_<?= $obj->_id ?>" onchange="JavaScript: addDeleteFile (<?= $obj->_id ?>);" /></td>
				<td><a href="#" onclick="JavaScript: showFile (<?= $obj->_id ?>); return false;"><?= $obj->_name ?></a></td>
				<td style="text-align: center;"><a href="#" onclick="JavaScript: showFile (<?= $obj->_id ?>); return false;"><?= number_format ($obj->_size / 1024, 0, ',', '.') ?></a></td>
				<td style="text-align: center;"><a href="#" onclick="JavaScript: showFile (<?= $obj->_id ?>); return false;"><?= $obj->_create_date ?></a></td>
				<td style="text-align: center;"><a href="#" onclick="JavaScript: showFile (<?= $obj->_id ?>); return false;"><?= isset ($obj->_counter) ? $obj->_counter : '0' ?></a></td>
				<td style="text-align: right;">
					<a href="#" onclick="JavaScript: showFile (<?= $obj->_id ?>); return false;" title="Visualizar Dados"><img src="titan.php?target=loadFile&file=interface/icon/view.gif" border="0" /></a>
				</td>
			</tr>
			<tr id="row_<?= $obj->_id ?>_2" class="cSeparatorHalf"><td></td></tr>
			<tr style="display: none;" id="row_<?= $obj->_id ?>">
				<td colspan="6">
					<table style="border: #990000 1px solid; margin: 3px;" width="100%">
						<tr>
							<td style="vertical-align: middle; text-align: center; width: 110px;" align="center">
								<a href="titan.php?target=openFile&fileId=<?= $obj->_id ?>" target="_blank"><img id="image_<?= $obj->_id ?>" src="titan.php?target=loadFile&file=interface/icon/upload.gif" style="float: left; margin-right: 4px;" align="middle" border="0" /></a>
							</td>
							<td>
								<label>
									<input id="field_<?= $obj->_id ?>_name" type="text" value="<?= $obj->_name ?>" class="fieldEdit" onfocus="JavaScript: editField (<?= $obj->_id ?>, 'name');" onblur="JavaScript: noEditField (<?= $obj->_id ?>, 'name');" title="Clique para editar" />
									<input id="button_<?= $obj->_id ?>_name" type="button" value="" class="button bottonFile" onclick="JavaScript: saveField (<?= $obj->_id ?>, 'name');" />
								</label> <br />
								<label style="margin-left: 3px;"><?= number_format ($obj->_size, 0, ',', '.') ?> Bytes</label> <br />
								<label style="margin-left: 3px;"><?= $obj->_mimetype ?></label> <br /><br />
								<label style="color: #000000; margin-left: 0px;">
									<textarea id="field_<?= $obj->_id ?>_description" type="text" class="fieldEdit" style="font-weight: normal; float: left;" onfocus="JavaScript: editField (<?= $obj->_id ?>, 'description');" onblur="JavaScript: noEditField (<?= $obj->_id ?>, 'description');" title="Clique para editar"><?= trim ($obj->_description) == '' ? 'Sem descrição' : $obj->_description ?></textarea>
									<input id="button_<?= $obj->_id ?>_description" type="button" value="" class="button bottonFile" style="float: left; margin: 1px 0px 0px 3px;" onclick="JavaScript: saveFieldDesc (<?= $obj->_id ?>, 'description');" />
								</label>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr id="row_<?= $obj->_id ?>_3" class="cSeparatorHalf"><td></td></tr>
			<?
		}
		?>
	</table>
	<table style="border-width: 0px;">
		<tr class="cSeparator"><td></td></tr>
		<tr>
			<td style="text-align: right;">
				<?
				$size = dirSize (Archive::singleton ()->getDataPath ());
				
				if ($size !== FALSE)
					echo 'Tamanho total do repositório: <label style="font-weight: bold; color: #990000;">'. number_format ($size / 1000, 0, ',', '.') .' KBytes</label>';
				else
					echo '<label style="color: #990000;">Não há um diretório válido de arquivos</label>';
				?>
			</td>
		</tr>
	</table>
	</form>
</div>