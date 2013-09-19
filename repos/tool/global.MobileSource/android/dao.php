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

package <?= $app ?>.dao;

import java.util.LinkedList;
import java.util.List;

import android.content.ContentValues;
import android.database.Cursor;
import android.database.SQLException;
import android.database.sqlite.SQLiteDatabase;

import <?= $app ?>.contract.<?= $model ?>Contract;
import <?= $app ?>.converter.<?= $model ?>Converter;
import <?= $app ?>.model.<?= $model ?>;
import <?= $app ?>.util.Database;

public class <?= $model ?>DAO 
{
	private static <?= $model ?>DAO dao; 
	
	private SQLiteDatabase db;
	
	private <?= $model ?>DAO ()
	{
		db = Database.singleton ().getWritableDatabase ();
	}
	
	public static <?= $model ?>DAO singleton ()
	{
		if (dao == null)
			dao = new <?= $model ?>DAO ();
		
		return dao;
	}
	
	public List<<?= $model ?>> list ()
	{
		Cursor cursor = db.query (<?= $model ?>Contract.TABLE, <?= $model ?>Contract.columns (), null, null, null, null, null);
		
		List<<?= $model ?>> list = new LinkedList<<?= $model ?>> ();
		
		while (cursor.moveToNext ())
			list.add (<?= $model ?>Converter.from (cursor));
		
		cursor.close ();
		
		return list;
	}
	
	public void truncate ()
	{
		db.delete (<?= $model ?>Contract.TABLE, null, null);
	}
	
	public void insert (List<<?= $model ?>> list)
	{
		for (<?= $model ?> item : list)
			insert (item);
	}
	
	public void insert (<?= $model ?> item)
	{
		ContentValues v = <?= $model ?>Converter.toContentValue (item);
		
		try
		{
			db.insertOrThrow (<?= $model ?>Contract.TABLE, null, v);
		}
		catch (SQLException e)
		{
			throw new TechnicalException ("Impossível gravar os dados!", e);
		}
	}
	
	public void update (List<<?= $model ?>> list)
	{
		for (<?= $model ?> item : list)
			update (item);
	}
	
	public void update (<?= $model ?> item)
	{
		ContentValues v = <?= $model ?>Converter.toContentValue (item);
		
		try
		{
			db.update (<?= $model ?>Contract.TABLE, v, <?= $model ?>Contract.<?= strtoupper ($fields [0]->json) ?> + " = ?", new String [] { String.valueOf (item.get<?= ucwords ($fields [0]->class) ?> ()) });
		}
		catch (SQLException e)
		{
			throw new TechnicalException ("Impossível atualizar os dados!", e);
		}
	}
	
	public void insertOrUpdate (List<<?= $model ?>> list)
	{
		for (<?= $model ?> item : list)
			insertOrUpate (item);
	}
	
	public void insertOrUpdate (<?= $model ?> item)
	{
		ContentValues v = <?= $model ?>Converter.toContentValue (item);
		
		try
		{
			db.insertOrThrow (<?= $model ?>Contract.TABLE, null, v);
		}
		catch (SQLException e)
		{
			db.update (<?= $model ?>Contract.TABLE, v, <?= $model ?>Contract.<?= strtoupper ($fields [0]->json) ?> + " = ?", new String [] { String.valueOf (item.get<?= ucwords ($fields [0]->class) ?> ()) });
		}
	}
	
	public void delete (Long id)
	{
		db.delete (<?= $model ?>Contract.TABLE, <?= $model ?>Contract.<?= strtoupper ($fields [0]->json) ?> + " = ?", new String [] { String.valueOf (id) });
	}
	
	public void delete (<?= $model ?> item)
	{
		db.delete (<?= $model ?>Contract.TABLE, <?= $model ?>Contract.<?= strtoupper ($fields [0]->json) ?> + " = ?", new String [] { String.valueOf (item.get<?= ucwords ($fields [0]->class) ?> ()) });
	}
}
<?
return ob_get_clean ();
?>