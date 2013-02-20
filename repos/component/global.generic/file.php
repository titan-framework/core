<style type="text/css">
#mainContainer
{
	margin: 0 auto;
	width: 728px;
	text-align:left;
	background-color:#FFF;
}
#leftColumn
{
	width: 373px;
	float:left;
	margin-top: 7px;
	padding-right:5px;
}

#rightColumn
{
	float: right;
	width: 345px;
	height: 250px;
}
#shopping_cart
{
	margin:3px;
	padding:3px;
}
.clear
{
	clear:both;
}

.product_container
{
	width: 330px;
	float: left;
	margin-top: 3px;
	padding: 2px;
}
</style>
<div id="idForm">
	<div id="mainContainer">
		<div id="leftColumn">
			<div style="margin-right: 20px;">
				<div class="product_container">
					<?
					$array = array ('type'	=> 'File',
									'id' 	=> '_ARQUIVO_');

					$field = Type::factory ('_file', $array);

					$fieldId = 'field_'. $field->getAssign ();

					echo Form::toForm ($field);
					?>
				</div>
				<div style="float: right; margin-top: 5px;">
					<input type="button" class="button" value="<?= __ ('Add') ?> &raquo;" onclick="addToBasket ('<?= $fieldId ?>', <?= $itemId ?>);" />
				</div>
			</div>
			<div class="clear"></div>
		</div>
		<div id="rightColumn">
			<fieldset>
				<legend style="padding: 5px; margin-left: 0px;"><?= __ ('Archives Linked') ?></legend>
				<div id="tiedFiles" style="width: 325px; height: 280px; overflow: auto;"></div>
			</fieldset>
		</div>
		<div class="clear"></div>
	</div>
</div>
<script language="javascript" type="text/javascript">
ajax.loadFiles (<?= $itemId ?>);
</script>