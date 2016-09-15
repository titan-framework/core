<?php
function validName ($str)
{
        $str = substr ($str, 0, 255);

        $trade = array( 'á'=>'a','à'=>'a','ã'=>'a',
                                        'ä'=>'a','â'=>'a',
                                        'Á'=>'A','À'=>'A','Ã'=>'A',
                                        'Ä'=>'A','Â'=>'A',
                                        'é'=>'e','è'=>'e',
                                        'ë'=>'e','ê'=>'e',
                                        'É'=>'E','È'=>'E',
                                        'Ë'=>'E','Ê'=>'E',
                                        'í'=>'i','ì'=>'i',
                                        'ï'=>'i','î'=>'i',
                                        'Í'=>'I','Ì'=>'I',
                                        'Ï'=>'I','Î'=>'I',
                                        'ó'=>'o','ò'=>'o','õ'=>'o',
                                        'ö'=>'o','ô'=>'o',
                                        'Ó'=>'O','Ò'=>'O','Õ'=>'O',
                                        'Ö'=>'O','Ô'=>'O',
                                        'ú'=>'u','ù'=>'u',
                                        'ü'=>'u','û'=>'u',
                                        'Ú'=>'U','Ù'=>'U',
                                        'Ü'=>'U','Û'=>'U',
                                        '$'=>'','@'=>'','!'=>'',
                                        '#'=>'','%'=>'','_'=>'',
                                        '^'=>'','&'=>'','*'=>'',
                                        '('=>'',')'=>'',' '=>'',
                                        '-'=>'','+'=>'','='=>'',
                                        '\\'=>'','|'=>'',
                                        '`'=>'','~'=>'','/'=>'',
                                        '\"'=>'','\''=>'',
                                        '<'=>'','>'=>'','?'=>'',
                                        ','=>'','ç'=>'c','Ç'=>'C');

        $str = strtr ($str, $trade);

        return $str;
}

class Ajax
{
        public function nameValidate ($section)
        {
                $section = validName ($section);

                $name = $_SESSION['UNIX_NAME'];

                if (file_exists ('instance/'. $name .'/section/'. $section))
                {
                        $count = 1;

                        do
                        {
                                $aux = $section .'.'. str_pad ($count++, 3, '0', STR_PAD_LEFT);
                        } while (file_exists ('instance/'. $name .'/section/'. $aux));

                        $section = $aux;
                }

                return $section;
        }

        public function saveForm ($name, $form)
        {
                // array_walk ($form, 'utfDecode');

                $_SESSION['SECTION_PROPS_'. $name] = $form;

                return TRUE;
        }

        public function clear ($name)
        {
                if (isset ($_SESSION['SECTION_PROPS_'. $name]))
                        unset ($_SESSION['SECTION_PROPS_'. $name]);

                return TRUE;
        }

        public function xoadGetMeta()
        {
                XOAD_Client::mapMethods ($this, array ('saveForm', 'clear', 'nameValidate'));

                XOAD_Client::publicMethods ($this, array ('saveForm', 'clear', 'nameValidate'));

                XOAD_Client::privateMethods ($this, array ());
        }
}

$instance = Instance::singleton ();

if (!isset ($_SESSION['UNIX_NAME']))
        throw new Exception ('Houve perda de variáveis!');

$itemId = $_SESSION['UNIX_NAME'];

if (!isset ($_GET['name']))
        throw new Exception ('Hove perda de variáveis!');

$name = trim (str_replace (array ('..', '/', '\\'), '', $_GET['name']));

if ($name == '')
        throw new Exception ('Atenção! Ato ilícito detectado. Acesso negado ;)');

if (!isset ($_SESSION['PACKS_'. $itemId]))
        throw new Exception ('Houve perda de variáveis!');

$packages = $_SESSION['PACKS_'. $itemId];

$pack = $packages [$name];

//die (print_r ($pack));

$aux = array ();

if (array_key_exists ('property', $pack))
        $aux [$name] = $pack ['property'];

if (array_key_exists ('depends', $pack))
{
        $depends = explode (',', $pack ['depends']);

        foreach ($depends as $trash => $package)
                if (isset ($packages [$package]['property']))
                        $aux [$package] = $packages [$package]['property'];
}

$properties = array ();

$unique = array ();

$form = array ();

$ajax = new Ajax;

foreach ($aux as $package => $array)
{
        $form ['_UNIX_NAME_'. str_replace ('.', '_', $package)] = $ajax->nameValidate ($package);

        foreach ($array as $trash => $value)
        {
                if (!array_key_exists ('name', $value) || trim ($value ['name']) == '' || in_array ($value ['name'], $unique) || $value ['name'] == '_UNIX_NAME_')
                        continue;

                $unique [] = $value ['name'];

                if (!array_key_exists ('label', $value))
                        $value ['label'] = '[undefined]';

                $properties [$package][$value ['name']] = $value;

                $form [$value ['name']] = $value ['default'];
        }
}

if (!sizeof ($properties))
{
        $properties [$name] = array ();
        $form ['_UNIX_NAME_'. str_replace ('.', '_', $name)] = $ajax->nameValidate ($name);
}


$_SESSION['SECTION_PROPS_'. $name] = $form;

//die (print_r ($properties));

function utfDecode (&$value, $key)
{
        $value = utf8_decode ($value);
}

define ('XOAD_AUTOHANDLE', true);

require_once $instance->getCorePath () .'xoad/xoad.php';

XOAD_Server::allowClasses ('Ajax');

if (XOAD_Server::runServer ())
        exit ();

require $instance->getCorePath () .'system/control.php';

$skin = Skin::singleton ();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
        <head>
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                <title> <?= $instance->getName () ?> </title>
                <style type="text/css">
                <?php include $skin->getCss ('message') ?>
                <?php include $skin->getCss ('main') ?>
                </style>
                <script language="javascript" type="text/javascript" src="titan.php?target=loadFile&file=js/boxover.js"></script>
                <?= XOAD_Utilities::header('titan.php?target=loadFile&file=xoad') ."\n" ?>
                <script language="javascript" type="text/javascript">
                var ajax = <?= XOAD_Client::register(new Ajax) ?>;

                function saveForm ()
                {
                        try
                        {
                                counter = 1;
                                while (unix = document.getElementById ('field_unix_name_' + counter++))
                                        unix.value = ajax.nameValidate (unix.value);

                                var form = xoad.html.exportForm ('form_config');

                                if (!ajax.saveForm ('<?= $name ?>', form))
                                        throw 'Não foi possível salvar as alterações!';

                                var fields = new Array ('<?= implode ("', '", array_keys ($form)) ?>');

                                for (i = 0 ; i < fields.length ; i++)
                                        eval ("document.form_config_name." + fields [i] + ".className = 'fieldNoEdit'");

                                var button = document.getElementById ('saveButton');
                                button.className = 'button buttonDisabled';
                                button.onclick = function () {};
                        }
                        catch (error)
                        {
                                alert (error);
                        }
                }

                function editField (field)
                {
                        document.getElementById (field).className = 'fieldEdit';

                        var button = document.getElementById ('saveButton');
                        button.className = 'button buttonEnabled';
                        button.onclick = function () { saveForm (); };
                }

                function cancel ()
                {
                        try
                        {
                                if (!ajax.clear ('<?= $name ?>'))
                                        throw 'Não foi possível cancelar!';

                                parent.modalMsg.close ();
                        }
                        catch (error)
                        {
                                alert (error);
                        }
                }

                function create ()
                {
                        if (!confirm ('Tem certeza que deseja prosseguir? Estas propriedades NÃO poderão mais ser modificadas. Assegure-se de que os valores estejam corretos e de que você SALVOU suas alterações.'))
                                return false;

                        parent.createSection ('<?= $name ?>');
                }
                </script>
        </head>
        <body>
                <style type="text/css">
                body
                {
                        background: #FFFFFF none;
                        margin: 0px;
                }
                .field
                {
                        height: 16px;
                }
                .buttonEnabled
                {
                        color: #330000;
                        border-color: #330000;
                }
                .buttonDisabled
                {
                        color: #CCCCCC; border-color: #CCCCCC;
                }
                .fieldEdit
                {
                        font-family: Verdana, Arial, Helvetica, sans-serif;
                        font-size: 10px;
                        color: #330000;
                        border: #330000 1px solid;
                        width: 200px;
                        padding: 3px;
                        background: none;
                }
                .fieldNoEdit
                {
                        font-family: Verdana, Arial, Helvetica, sans-serif;
                        font-size: 10px;
                        color: #555555;
                        border: #330000 0px solid;
                        width: 200px;
                        padding: 3px;
                        background: url(titan.php?target=loadFile&file=interface/icon/editable.gif) right no-repeat;
                }
                .fieldNoEdit:hover
                {
                        border-width: 1px;
                        background: none;
                        color: #330000;
                }
                </style>
                <div id="idRegister">
                        <form id="form_config" name="form_config_name" method="post">
                        <table align="center" border="0" width="100%" cellpadding="2" cellspacing="0">
                                <?php
                                $count = 0;
                                foreach ($properties as $package => $props)
                                {
                                        $count++;
                                        $jsPkg = str_replace ('.', '_', $package);
                                        ?>
                                        <tr height="10px"><td></td></tr>
                                        <tr height="24px" style="background-color: #FFFFFF;">
                                                <td colspan="3" style="border-bottom: #990000 1px solid; color: #CCCCCC;"><b style="color: #990000"><?= $packages [$package]['label'] ?></b> [<?= $package ?>]</td>
                                        </tr>
                                        <tr height="5px"><td></td></tr>
                                        <tr height="24px" style="background-color: #FFFFFF;">
                                                <td width="20%" nowrap style="text-align: right;"><b>Nome Unix:</b></td>
                                                <td><input type="text" class="fieldNoEdit" name="_UNIX_NAME_<?= $jsPkg ?>" id="field_unix_name_<?= $count ?>" value="<?= $ajax->nameValidate ($package) ?>" onfocus="JavaScript: editField ('field_unix_name_<?= $count ?>');" /></td>
                                                <td width="20px" style="vertical-align: top;">
                                                        <img src="<?= Skin::singleton ()->getIconsFolder () ?>help.gif" border="0" style="vertical-align: middle;" title="header=[Nome Unix] body=[Nome que será atribuido ao diretório de arquivos e a outros locais críticos. Não utilize acentos, caracteres especiais ou espaços.] cssheader=[divHelpHeader] cssbody=[divHelpBody] fade=[on] offsetx=[-310]" />
                                                </td>
                                        </tr>
                                        <tr height="2px"><td></td></tr>
                                        <?php
                                        $color = 'FFFFFF';
                                        foreach ($props as $key => $array)
                                        {
                                                $color = $color == 'F4F4F4' ? 'FFFFFF' : 'F4F4F4';
                                                ?>
                                                <tr height="24px" style="background-color: #<?= $color ?>;">
                                                        <td width="20%" nowrap style="text-align: right;"><b><?= $array ['label'] ?>:</b></td>
                                                        <td><input type="text" class="fieldNoEdit" name="<?= $key ?>" id="field_<?= $key ?>" value="<?= $array ['default'] ?>" onfocus="JavaScript: editField ('field_<?= $key ?>');" /></td>
                                                        <td width="20px" style="vertical-align: top;">
                                                                <?= array_key_exists ('help', $array) && trim ($array ['help']) != '' ?  '<img src="'. Skin::singleton ()->getIconsFolder () .'help.gif" border="0" style="vertical-align: middle;" title="header=['. $array ['label'] .'] body=['. $array ['help'] .'] cssheader=[divHelpHeader] cssbody=[divHelpBody] fade=[on] offsetx=[-310]" />' : '&nbsp;'; ?>
                                                        </td>
                                                </tr>
                                                <tr height="2px"><td></td></tr>
                                                <?php
                                        }
                                }
                                ?>
                                <tr height="5px"><td></td></tr>
                                <tr>
                                        <td></td>
                                        <td colspan="2">
                                                <input type="button" class="button buttonDisabled" id="saveButton" value="Salvar" />
                                                <input type="button" class="button" value="Cancelar" onclick="JavaScript: cancel ();" />
                                                <input type="button" class="button" value="Criar Seção &raquo;" onclick="JavaScript: create ();" />
                                        </td>
                                </tr>
                        </table>
                        </form>
                </div>
                <div id="idBody" style="display: none;"></div>
        </body>
</html>
