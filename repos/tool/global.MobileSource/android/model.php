<?php
ob_start ();
?>
/**
 * Copyright © 2013 Titan Framework. All Rights Reserved.
 *
 * Developed by Laboratory for Precision Livestock, Environment and Software Engineering (PLEASE Lab)
 * of Embrapa Beef Cattle at Campo Grande - MS - Brazil.
 * 
 * @see http://please.cnpgc.embrapa.br
 * 
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @author Jairo Ricardes Rodrigues Filho <jairocgr@gmail.com>
 * 
 * @version <?= date ('y.m') ?>-1-alpha
 */

package <?= $app ?>.model;

import java.util.Date;

public class <?= $model ?> 
{
<?php
foreach ($fields as $trash => $obj)
	echo "	private ". $obj->type ." ". $obj->class .";\n";

foreach ($fields as $trash => $obj)
	if (get_class ($obj->object) == 'Enum')
	{
		?>
	
	public static final Map<String, String> <?= $obj->class ?>Map;
	static
	{
		<?= $obj->class ?>Map = new HashMap<String, String> ();
		
<?php	
		$items = $obj->object->getMapping ();
		
		foreach ($items as $value => $label)
			echo "		". $obj->class ."Map.put (\"". $value ."\", \"". $label ."\");\n";
		?>
	}
<?php
	}

foreach ($fields as $trash => $obj)
{
	?>
	
	public <?= $obj->type ?> get<?= ucwords ($obj->class) ?> ()
	{
		return <?= $obj->class ?>;
	}
<?php
	if (get_class ($obj->object) == 'Enum')
	{
		?>
	
	public String get<?= ucwords ($obj->class) ?>Label ()
	{
		if (<?= $obj->class ?>Map.containsKey (<?= $obj->class ?>))
			return <?= $obj->class ?>Map.get (<?= $obj->class ?>);
		
		return <?= $obj->class ?>;
	}
<?php
	}
	?>
	
	public void set<?= ucwords ($obj->class) ?> (<?= $obj->type ?> <?= $obj->class ?>)
	{
		this.<?= $obj->class ?> = <?= $obj->class ?>;
	}
<?php
}
?>
}
<?php
return ob_get_clean ();
?>