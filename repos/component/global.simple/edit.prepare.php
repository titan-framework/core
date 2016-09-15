<?php
$section = Business::singleton ()->getSection (Section::TCURRENT);

$tagFile = $section->getDirective ('_TAG_');

if (!is_null ($tagFile) && trim ($tagFile) != '' && file_exists ('section/'. $section->getName () .'/'. $tagFile))
{
	$array = parse_ini_file ('section/'. $section->getName () .'/'. $tagFile, TRUE);
	
	if (sizeof ($array))
	{
		$tagBuffer  = '<table cellpadding="5" cellspacing="0" border="0" width="100%">';
		$tagBuffer .= '<tr style="background-color: #575556;"><td style="color: #FFF; font-weight: bold; border-bottom: #36817C 2px solid;">'. __ ('Symbol') .'</td><td style="color: #FFF; font-weight: bold; border-bottom: #36817C 2px solid;">'. __ ('Replaced by') .'</td></tr>';
		
		$count = 0;
		
		foreach ($array as $key => $value)
		{
			if (is_array ($value))
			{
				$tagBuffer .= '<tr style="background-color: #C4D69F; font-weight: bold;"><td colspan="2" style="color: #000;">'. $key .'</td></tr>';
				
				foreach ($value as $tag => $label)
					$tagBuffer .= '<tr style="background-color: #'. ($count++ % 2 ? 'FFF' : 'EFEFDE') .'; font-weight: bold;"><td>'. $tag .'</td><td>'. translate ($label) .'</td></tr>';
			}
			else
				$tagBuffer .= '<tr style="background-color: #'. ($count++ % 2 ? 'FFF' : 'EFEFDE') .'; font-weight: bold;"><td>'. $key .'</td><td>'. translate ($value) .'</td></tr>';
		}
		
		$tagBuffer .= '</table>';
	}
}

$form =& FormSimple::singleton ('edit.xml', 'all.xml');

$id = $section->getDirective ('_ID_') ? $section->getDirective ('_ID_') : 'SIMPLE_'. $section->getName ();

if (!$form->load ($id))
	throw new Exception ('Não foi possível carregar os dados do item!');
?>