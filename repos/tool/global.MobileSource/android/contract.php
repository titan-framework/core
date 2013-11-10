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

package <?= $app ?>.contract;

public class <?= $model ?>Contract 
{
	public static String TABLE = "<?= $table ?>";
	
<?
foreach ($fields as $trash => $obj)
	echo "	public static String ". strtoupper ($obj->json) ." = \"". $obj->json ."\";\n";
?>
	
	public static String ddl ()
	{
		String ddl = "CREATE TABLE " + TABLE + " (" +
<?
$size = sizeof ($fields);
$count = 1;
foreach ($fields as $trash => $obj)
	echo "				". strtoupper ($obj->json) ." + \" ". $obj->db . ($count++ < $size ? ", \" +\n" : "\" +\n");
?>
			");";
		
		return ddl;
	}
	
	public static String [] columns ()
	{
<?
$columns = array ();

foreach ($fields as $trash => $obj)
	$columns [] = strtoupper ($obj->json);
?>
		return new String [] { <?= implode (', ', $columns) ?> };
	}
}
<?
return ob_get_clean ();
?>