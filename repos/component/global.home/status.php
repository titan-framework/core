<style type="text/css">
#idStatus
{
	width: auto;
	margin: 10px 8px 20px 8px;
}
#idStatus .cTitle
{
	font-weight: bold;
	color: #FFFFFF;
	background-color: #575556;
	padding: 5px;
}
#idStatus .cSeparator
{
	height: 10px;
	border: 0;
}
#idStatus .cSeparatorHalf
{
	height: 5px;
	border: 0;
}
#idStatus .cTableItem
{
	background-color: #F4F4F4;
}
#idStatus .cTableItem:hover
{
	background-color: #EEEEEE;
}
.imgUpdate:hover
{
	cursor: pointer;
}
</style>
<script language="javascript" type="text/javascript">
function update ()
{
	showWait ();
	
	document.getElementById ('buttonUpdate').style.display = 'none';
	document.getElementById ('buttonFakeUpdate').style.display = '';
	document.getElementById ('buttonCancel').style.display = 'none';
	document.getElementById ('buttonFakeCancel').style.display = '';
	
	var msg = ajax.makeUpdate ();
	
	document.getElementById ('labelUpdate').innerHTML = msg;
	
	document.getElementById ('buttonFakeCancel').style.display = 'none';
	document.getElementById ('buttonClose').style.display = '';
	
	hideWait ();
}

function upClose ()
{
	document.location = '<?= $_SERVER['PHP_SELF'] .'?'. $_SERVER['QUERY_STRING'] ?>';
}
</script>
<?php
try
{
	$updateError = FALSE;
	
	$requireUpdate = update ();
}
catch (Exception $e)
{
	$updateError = $e->getMessage ();
}

$forError = array (	'short_open_tag' => array ('1', 'Todo o código do Titan foi implementado usando short tags. Você DEVE ativar esta diretiva.'));

$forAlert = array (	'display_errors' => array ('0', 'Esta configuração, se estiver ativa, poderá mostrar erros de forma pouco elegante ao usuário.'),
					'display_startup_errors' => array ('0', 'Esta configuração, se estiver ativa, poderá mostrar erros de forma pouco elegante ao usuário.'),
					'safe_mode' => array ('0', 'Desativar esta configuração diminui a segurança do servidor, mas com ela ativa o Titan Lite Architect não poderá efetuar a atualização do Core e outras tarefas que exigem interação com o sistema operacional.'));

$forSecurity = array (	'register_globals'	=> array ('0', 'Se esta configuração estiver ativa poderá permitir a sobrecarga de valores de variáveis e fazer com que usuários maliciosos obtenham acesso irrestrito ao sistema.'));

$forExtension = array (	'gd' => array (TRUE, 'Esta extensão permite que o sistema redimensione e converta imagens.', 'http://br.php.net/gd'),
						'hash' => array (TRUE, 'Sem esta extensão o sistema não poderá armazenar senhas e gerar hashs de controle.', 'http://br.php.net/hash'),
						'json' => array (TRUE, 'Necessário para o uso de AJAX pelo framework.', 'http://br.php.net/json'),
						'mime_magic' => array (FALSE, 'Instalar esta extensão aumenta a segurança e controle de arquivos no sistema.', 'http://br.php.net/mime_magic'),
						'PDO' => array (TRUE, 'Esta extensão é fundamental para permitir o acesso ao Banco de Dados pelo sistema.', 'http://br.php.net/pdo'),
						'pdo_pgsql' => array (TRUE, 'Esta extensão é fundamental para permitir o acesso ao Banco de Dados pelo sistema.', 'http://br.php.net/pdo_pgsql'),
						'session' => array (TRUE, 'Sem esta extenção o sistema não pode manter o usuário logado nem fazer cache de arquivos parseados.', 'http://br.php.net/session'),
						'xsl' => array (FALSE, 'Para utilizar templates (skins) nas suas instâncias é necessário instalar esta extensão.', 'http://br.php.net/xsl'));

$dirAlerts = array ();
$dirErrors = array ();
$dirSecuritys = array ();

foreach ($forError as $key => $array)
	if (strtoupper (trim (ini_get ($key))) != $array [0])
		$dirErrors [] = $key;

foreach ($forAlert as $key => $array)
	if (strtoupper (trim (ini_get ($key))) != $array [0])
		$dirAlerts [] = $key;

foreach ($forSecurity as $key => $array)
	if (strtoupper (trim (ini_get ($key))) != $array [0])
		$dirSecurity [] = $key;

$extAlerts = array ();
$extErrors = array ();

$extensions = get_loaded_extensions ();
foreach ($forExtension as $key => $array)
	if (!in_array ($key, $extensions))
		if ($array [0])
			$extErrors [] = $key;
		else
			$extAlerts [] = $key;

if ($updateError !== FALSE)
{
	?>
	<div id="idList">
		<table style="border: #990000 1px solid;">
			<tr>
				<td style="width: 70px; text-align: center;">
					<img src="titan.php?target=loadFile&file=interface/image/error.png" border="0" />
				</td>
				<td style="vertical-align: middle;">
					<label style="color: #990000;">Atenção!</label> <?= $updateError ?>
				</td>
			</tr>
		</table>
	</div>
	<?php
}
elseif (sizeof ($requireUpdate))
{
	?>
	<div id="idList">
		<table style="border: #E4B01A 1px solid;">
			<tr>
				<td style="width: 70px; text-align: center;">
					<img src="titan.php?target=loadFile&file=interface/image/warning.png" border="0" />
				</td>
				<td style="vertical-align: middle;">
					Existem atualizações a serem efetuadas no <b>Framework Titan</b>. <a href="#" onclick="JavaScript: message ('titan.php?target=script&toSection=<?= $section->getName () ?>&file=update', 500, 300);">Clique aqui</a> para baixa-las e instala-las agora mesmo!
				</td>
				<td style="width: 70px; text-align: center;">
					<img src="titan.php?target=loadFile&file=interface/image/update.png" border="0" class="imgUpdate" title="Baixar e instalar atualizações." onclick="JavaScript: message ('titan.php?target=script&toSection=<?= $section->getName () ?>&file=update', 500, 300);" />
				</td>
			</tr>
		</table>
	</div>
	<?php
}
else
{
	?>
	<div id="idList">
		<table style="border: #009900 1px solid;">
			<tr>
				<td style="width: 70px; text-align: center;">
					<img src="titan.php?target=loadFile&file=interface/image/success.png" border="0" />
				</td>
				<td style="vertical-align: middle;">
					O <b>Framework Titan</b> está atualizado com a útima versão do repositório.
				</td>
			</tr>
		</table>
	</div>
	<?php
}

if (sizeof ($dirErrors))
{
	?>
	<div id="idList">
		<table style="border: #990000 1px solid;">
			<tr>
				<td style="width: 70px; text-align: center;">
					<img src="titan.php?target=loadFile&file=interface/image/error.png" border="0" />
				</td>
				<td style="vertical-align: middle;">
					<label style="color: #990000;">Atenção!</label> Algumas diretivas possuem valores que devem ser modificados no
					arquivo [php.ini] para uso do <b>Titan Lite Architect</b>.
				</td>
			</tr>
		</table>
	</div>
	<?php
}
elseif (sizeof ($dirAlerts) || sizeof ($dirSecuritys))
{
	?>
	<div id="idList">
		<table style="border: #E4B01A 1px solid;">
			<tr>
				<td style="width: 70px; text-align: center;">
					<img src="titan.php?target=loadFile&file=interface/image/warning.png" border="0" />
				</td>
				<td style="vertical-align: middle;">
					Algumas diretivas possuem valores que diferem dos aconselhados 
					para uso do <b>Titan Lite Architect</b>. Você pode, opcionalmente, modificá-las no arquivo [php.ini].
				</td>
			</tr>
		</table>
	</div>
	<?php
}
else
{
	?>
	<div id="idList">
		<table style="border: #009900 1px solid;">
			<tr>
				<td style="width: 70px; text-align: center;">
					<img src="titan.php?target=loadFile&file=interface/image/success.png" border="0" />
				</td>
				<td style="vertical-align: middle;">
					Todas as diretivas relacionadas ao bom funcionamento do  <b>Titan Lite Architect</b>
					estão setadas com valores corretos no arquivo [php.ini].
				</td>
			</tr>
		</table>
	</div>
	<?php
}

if (sizeof ($extErrors))
{
	?>
	<div id="idList">
		<table style="border: #990000 1px solid;">
			<tr>
				<td style="width: 70px; text-align: center;">
					<img src="titan.php?target=loadFile&file=interface/image/error.png" border="0" />
				</td>
				<td style="vertical-align: middle;">
					<label style="color: #990000;">Atenção!</label> Alguma(s) extenção(ões) fundamental(is) para o funcionamento do 
					<b>Titan Lite Architect</b> não está(ão) ativa(s). Você deve instala-la(s) para utilização do <i>framework</i>.
				</td>
			</tr>
		</table>
	</div>
	<?php
}
elseif (sizeof ($extAlerts))
{
	?>
	<div id="idList">
		<table style="border: #E4B01A 1px solid;">
			<tr>
				<td style="width: 70px; text-align: center;">
					<img src="titan.php?target=loadFile&file=interface/image/warning.png" border="0" />
				</td>
				<td style="vertical-align: middle;">
					Alguma(s) extenção(ões) opcional(is) para o bom funcionamento do 
					<b>Titan Lite Architect</b> não está(ão) ativa(s).
				</td>
			</tr>
		</table>
	</div>
	<?php
}
else
{
	?>
	<div id="idList">
		<table style="border: #009900 1px solid;">
			<tr>
				<td style="width: 70px; text-align: center;">
					<img src="titan.php?target=loadFile&file=interface/image/success.png" border="0" />
				</td>
				<td style="vertical-align: middle;">
					Todas as extensões relacionadas ao bom funcionamento do  <b>Titan Lite Architect</b>
					estão instaladas e ativas.
				</td>
			</tr>
		</table>
	</div>
	<?php
}

if ($updateError === FALSE)
{
	?>
	<div id="idStatus">
		<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
			<tr>
				<td colspan="3" class="cTitle">Atualizações</td>
			</tr>
		</table>
		<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0" style="border: #36817C 1px solid; border-top-width: 3px;">
			<tr class="cSeparator">
				<td style="width: 30px;"></td>
				<td style="width: 200px;"></td>
				<td style="width: 200px;"></td>
				<td></td>
			</tr>
			<?php
			$modules = array (	'INSTANCE' 	=> array ('Instância do <b>Titan Lite</b>', 'Esta atualização traz correções e novas funcionalidades para o <b>Titan Lite</b>.'),
								'BASE'		=> array ('Repositório de arquivos-base', 'Esta atualização traz novas aplicações-exemplos para serem utilizadas como base na criação de instâncias no Gerador de Instâncias do <b>Titan Lite Architect</b>.'),
								'CORE'		=> array ('Core do <b>Titan Lite</b>', 'Esta atualização traz importantes correções e novas funcionalidades para o <b>Core do Titan</b>, afetando todas as aplicações já instanciadas e as que ainda serão criadas.'),
								'COMPONENT'	=> array ('Repositório de Componentes', 'Esta atualização traz correções para <i>Componentes</i> existentes e novos <i>Componentes</i> para serem utilizados no desenvolvimento de novas aplicações do <b>Titan Lite</b>.'),
								'SKIN'		=> array ('Repositório de Skins (Temas)', 'Esta atualização traz correções para <i>Skins</i> existentes e novos <i>Skins</i> para serem utilizados no desenvolvimento de novas aplicações do <b>Titan Lite</b>.'),
								'TYPE'		=> array ('Repositório de Tipos', 'Esta atualização traz correções para <i>Tipos</i> existentes e novos <i>Tipos</i> para serem utilizados no desenvolvimento de novas aplicações do <b>Titan Lite</b>.'),
								'TEMPLATE'	=> array ('Repositório de Templates', 'Esta atualização traz correções para <i>Templates</i> existentes e novos <i>Templates</i> para serem utilizados no desenvolvimento de novas aplicações do <b>Titan Lite</b>.'));
			
			foreach ($modules as $key => $module)
				if (array_key_exists ($key, $requireUpdate))
				{
					?>
					<tr class="cTableItem">
						<td style="text-align: center;"><img src="titan.php?target=loadFile&file=interface/icon/alert.gif" border="0" /></td>
						<td style="font-weight: bold;"><?= $module [0] ?></td>
						<td nowrap="nowrap" style="color: #990000;">Há novas atualizações para serem instaladas.</td>
						<td style="text-align: right;"><img src="titan.php?target=loadFile&file=interface/icon/help.gif" border="0" title="header=[<?= $module [0] ?>] body=[<?= $module [1] ?>] cssheader=[divHelpHeader] cssbody=[divHelpBody] fade=[on] offsetx=[-310]" /></td>
					</tr>
					<tr class="cSeparator"><td></td></tr>
					<?php
				}
				else
				{
					?>
					<tr class="cTableItem">
						<td style="text-align: center;"><img src="titan.php?target=loadFile&file=interface/icon/ok.gif" border="0" /></td>
						<td style="font-weight: bold;"><?= $module [0] ?></td>
						<td style="color: #009900;" nowrap="nowrap">Este módulo esta atualizado com a última versão do repositório.</td>
						<td style="text-align: right;"><img src="titan.php?target=loadFile&file=interface/icon/help.gif" border="0" title="header=[<?= $module [0] ?>] body=[<?= $module [1] ?>] cssheader=[divHelpHeader] cssbody=[divHelpBody] fade=[on] offsetx=[-310]" /></td>
					</tr>
					<tr class="cSeparator"><td></td></tr>
					<?php
				}
			?>
		</table>
	</div>
	<?php
}
?>

<div id="idStatus">
	<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
		<tr>
			<td colspan="3" class="cTitle">Diretivas</td>
		</tr>
	</table>
	<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0" style="border: #36817C 1px solid; border-top-width: 3px;">
		<tr class="cSeparator">
			<td style="width: 30px;"></td>
			<td style="width: 200px;"></td>
			<td style="width: 200px;"></td>
			<td></td>
		</tr>
		<?php
		foreach ($forError as $key => $array)
			if (in_array ($key, $dirErrors))
			{
				?>
				<tr class="cTableItem">
					<td style="text-align: center;"><img src="titan.php?target=loadFile&file=interface/icon/cancel.gif" border="0" /></td>
					<td style="font-weight: bold;"><?= $key ?></td>
					<td style="color: #990000;"><?= $array [0] == '0' ? 'ON' : 'OFF' ?></td>
					<td style="text-align: right;"><img src="titan.php?target=loadFile&file=interface/icon/help.gif" border="0" title="header=[<?= $key ?>] body=[<?= $array [1] ?>] cssheader=[divHelpHeader] cssbody=[divHelpBody] fade=[on] offsetx=[-310]" /></td>
				</tr>
				<tr class="cSeparator"><td></td></tr>
				<?php
			}
			else
			{
				?>
				<tr class="cTableItem">
					<td style="text-align: center;"><img src="titan.php?target=loadFile&file=interface/icon/ok.gif" border="0" /></td>
					<td style="font-weight: bold;"><?= $key ?></td>
					<td style="color: #009900;"><?= $array [0] == '1' ? 'ON' : 'OFF' ?></td>
					<td style="text-align: right;"></td>
				</tr>
				<tr class="cSeparator"><td></td></tr>
				<?php
			}
		
		foreach ($forAlert as $key => $array)
			if (in_array ($key, $dirAlerts))
			{
				?>
				<tr class="cTableItem">
					<td style="text-align: center;"><img src="titan.php?target=loadFile&file=interface/icon/alert.gif" border="0" /></td>
					<td style="font-weight: bold;"><?= $key ?></td>
					<td style="color: #990000;"><?= $array [0] == '0' ? 'ON' : 'OFF' ?></td>
					<td style="text-align: right;"><img src="titan.php?target=loadFile&file=interface/icon/help.gif" border="0" title="header=[<?= $key ?>] body=[<?= $array [1] ?>] cssheader=[divHelpHeader] cssbody=[divHelpBody] fade=[on] offsetx=[-310]" /></td>
				</tr>
				<tr class="cSeparator"><td></td></tr>
				<?php
			}
			else
			{
				?>
				<tr class="cTableItem">
					<td style="text-align: center;"><img src="titan.php?target=loadFile&file=interface/icon/ok.gif" border="0" /></td>
					<td style="font-weight: bold;"><?= $key ?></td>
					<td style="color: #009900;"><?= $array [0] == '1' ? 'ON' : 'OFF' ?></td>
					<td style="text-align: right;"></td>
				</tr>
				<tr class="cSeparator"><td></td></tr>
				<?php
			}
		
		foreach ($forSecurity as $key => $array)
			if (in_array ($key, $dirSecuritys))
			{
				?>
				<tr class="cTableItem">
					<td style="text-align: center;"><img src="titan.php?target=loadFile&file=interface/icon/alert.gif" border="0" /></td>
					<td style="font-weight: bold;"><?= $key ?></td>
					<td style="color: #990000;"><?= $array [0] == '0' ? 'ON' : 'OFF' ?></td>
					<td style="text-align: right;"><img src="titan.php?target=loadFile&file=interface/icon/help.gif" border="0" title="header=[<?= $key ?>] body=[<?= $array [1] ?>] cssheader=[divHelpHeader] cssbody=[divHelpBody] fade=[on] offsetx=[-310]" /></td>
				</tr>
				<tr class="cSeparator"><td></td></tr>
				<?php
			}
			else
			{
				?>
				<tr class="cTableItem">
					<td style="text-align: center;"><img src="titan.php?target=loadFile&file=interface/icon/ok.gif" border="0" /></td>
					<td style="font-weight: bold;"><?= $key ?></td>
					<td style="color: #009900;"><?= $array [0] == '1' ? 'ON' : 'OFF' ?></td>
					<td style="text-align: right;"></td>
				</tr>
				<tr class="cSeparator"><td></td></tr>
				<?php
			}
		?>
	</table>
</div>
<div id="idStatus">
	<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
		<tr>
			<td colspan="3" class="cTitle">Extensões</td>
		</tr>
	</table>
	<table align="center" border="0" width="100%" cellpadding="2" cellspacing="0" style="border: #36817C 1px solid; border-top-width: 3px;">
		<tr class="cSeparator">
			<td style="width: 30px;"></td>
			<td style="width: 200px;"></td>
			<td style="width: 200px;"></td>
			<td></td>
		</tr>
		<?php
		foreach ($forExtension as $key => $array)
			if (in_array ($key, $extErrors))
			{
				?>
				<tr class="cTableItem">
					<td style="text-align: center;"><img src="titan.php?target=loadFile&file=interface/icon/cancel.gif" border="0" /></td>
					<td style="font-weight: bold;"><?= $key ?></td>
					<td style="color: #990000;"><a href="<?= $array [2] ?>" target="_blank">Instalar extensão &raquo;</a></td>
					<td style="text-align: right;"><img src="titan.php?target=loadFile&file=interface/icon/help.gif" border="0" title="header=[<?= $key ?>] body=[<?= $array [1] ?>] cssheader=[divHelpHeader] cssbody=[divHelpBody] fade=[on] offsetx=[-310]" /></td>
				</tr>
				<tr class="cSeparator"><td></td></tr>
				<?php
			}
			elseif (in_array ($key, $extAlerts))
			{
				?>
				<tr class="cTableItem">
					<td style="text-align: center;"><img src="titan.php?target=loadFile&file=interface/icon/alert.gif" border="0" /></td>
					<td style="font-weight: bold;"><?= $key ?></td>
					<td style="color: #990000;"><a href="<?= $array [2] ?>" target="_blank">Instalar extensão &raquo;</a></td>
					<td style="text-align: right;"><img src="titan.php?target=loadFile&file=interface/icon/help.gif" border="0" title="header=[<?= $key ?>] body=[<?= $array [1] ?>] cssheader=[divHelpHeader] cssbody=[divHelpBody] fade=[on] offsetx=[-310]" /></td>
				</tr>
				<tr class="cSeparator"><td></td></tr>
				<?php
			}
			else
			{
				?>
				<tr class="cTableItem">
					<td style="text-align: center;"><img src="titan.php?target=loadFile&file=interface/icon/ok.gif" border="0" /></td>
					<td style="font-weight: bold;"><?= $key ?></td>
					<td style="color: #009900;"></td>
					<td style="text-align: right;"></td>
				</tr>
				<tr class="cSeparator"><td></td></tr>
				<?php
			}
		?>
	</table>
</div>