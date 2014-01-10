<?
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
<?
foreach ($fields as $trash => $obj)
	echo "	private ". $obj->type ." ". $obj->class .";\n";

foreach ($fields as $trash => $obj)
{
	?>
	
	public <?= $obj->type ?> get<?= ucwords ($obj->class) ?> ()
	{
		return <?= $obj->class ?>;
	}
	
	public void set<?= ucwords ($obj->class) ?> (<?= $obj->type ?> <?= $obj->class ?>)
	{
		this.<?= $obj->class ?> = <?= $obj->class ?>;
	}
<?
}
?>
}
<?
return ob_get_clean ();
?>