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

package <?= $app ?>.converter;

import java.util.Date;
import java.util.LinkedList;
import java.util.List;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import android.content.ContentValues;
import android.database.Cursor;

import <?= $app ?>.exception.TechnicalException;
import <?= $app ?>.contract.<?= $model ?>Contract;
import <?= $app ?>.model.<?= $model ?>;

public class <?= $model ?>Converter 
{
	private AlertConverter ()
	{}
	
	public static List<<?= $model ?>> fromJsonString (String json)
	{
		try
		{
			JSONArray array = new JSONArray (json);
			
			List<<?= $model ?>> list = new LinkedList<<?= $model ?>> ();
			
			for (int i = 0; i < array.length (); i++)
				list.add (from (array.getJSONObject (i)));

			return list;
		}
		catch (JSONException e)
		{
			throw new TechnicalException ("Falha ao tentar abrir a lista de itens (JSON)!", e);
		}
	}

	public static <?= $model ?> from (JSONObject json)
	{
		try
		{
			<?= $model ?> item = new <?= $model ?> ();
			
<?
foreach ($fields as $trash => $obj)
	switch ($obj->type)
	{
		case 'Boolean':
			echo "			item.set". ucwords ($obj->class) ." (Boolean.valueOf (json.getString (". $model ."Contract.". strtoupper ($obj->json) .")));\n";
			break;
		
		case 'Date':
			echo "			item.set". ucwords ($obj->class) ." (new Date (json.getLong (". $model ."Contract.". strtoupper ($obj->json) .") * 1000));\n";
			break;
		
		case 'Double':
			echo "			String ". $obj->class ." = json.getString (". $model ."Contract.". strtoupper ($obj->json) .");\n";
			echo "			item.set". ucwords ($obj->class) ." (". $obj->class .".equals (\"\") ? 0d : Double.valueOf (". $obj->class ."));\n";
			break;
		
		default:
			echo "			item.set". ucwords ($obj->class) ." (json.get". $obj->type ." (". $model ."Contract.". strtoupper ($obj->json) ."));\n";
	}
?>
			
			return item;
		}
		catch (JSONException e)
		{
			throw new TechnicalException ("Falha ao tentar converter JSON!", e);
		}
	}

	public static <?= $model ?> from (Cursor cursor)
	{
		<?= $model ?> item = new <?= $model ?> ();

<?
foreach ($fields as $trash => $obj)
	switch ($obj->type)
	{
		case 'Boolean':
			echo "			item.set". ucwords ($obj->class) ." (cursor.getInt (cursor.getColumnIndexOrThrow (". $model ."Contract.". strtoupper ($obj->json) .")) == 1 ? true : false);\n";
			break;
		
		case 'Date':
			echo "			item.set". ucwords ($obj->class) ." (new Date (cursor.getLong (cursor.getColumnIndexOrThrow (". $model ."Contract.". strtoupper ($obj->json) .")) * 1000));\n";
			break;
		
		default:
			echo "			item.set". ucwords ($obj->class) ." (cursor.get". $obj->type ." (cursor.getColumnIndexOrThrow (". $model ."Contract.". strtoupper ($obj->json) .")));\n";
	}
?>
		
		return item;
	}
	
	public static ContentValues toContentValue (<?= $model ?> item)
	{
		ContentValues value = new ContentValues ();

<?
foreach ($fields as $trash => $obj)
	switch ($obj->type)
	{
		case 'Boolean':
			echo "			value.put (". $model ."Contract.". strtoupper ($obj->json) .", item.get". ucwords ($obj->class) ." () ? 1 : 0);\n";
			break;
		
		case 'Date':
			echo "			value.put (". $model ."Contract.". strtoupper ($obj->json) .", item.get". ucwords ($obj->class) ." ().getTime () / 1000);\n";
			break;
		
		default:
			echo "			value.put (". $model ."Contract.". strtoupper ($obj->json) .", alert.get". ucwords ($obj->class) ." ());\n";
	}
?>
		
		return value;
	}
}
<?
return ob_get_clean ();
?>