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

import java.io.BufferedReader;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.util.LinkedList;
import java.util.List;

import android.content.ContentValues;
import android.content.res.AssetManager;
import android.database.Cursor;
import android.database.SQLException;
import android.database.sqlite.SQLiteDatabase;
import android.util.Log;

import <?= $app ?>.contract.<?= $model ?>Contract;
import <?= $app ?>.converter.<?= $model ?>Converter;
import <?= $app ?>.exception.TechnicalException;
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
	
	public int load (AssetManager assets)
	{
		int lines = 0;
		
		try
		{
			if (!this.empty ())
				return 0;
			
			InputStream is = assets.open ("<?= $table ?>.sql");
			
			BufferedReader br = new BufferedReader (new InputStreamReader (is));
			
			String line;
			
			while ((line = br.readLine ()) != null)
			{
				Log.i (getClass ().getName (), line);
				
				db.execSQL (line);
				
				lines++;
			}
			
			br.close ();
			
			is.close ();
		}
		catch (Exception e)
		{
			throw new TechnicalException ("Impossível carregar os dados iniciais!", e);
		}
		
		return lines;
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
	
	public <?= $model ?> get (<?= $useCode ? 'String code' : 'long id' ?>)
	{
		Cursor cursor = db.query (<?= $model ?>Contract.TABLE, <?= $model ?>Contract.columns (), <?= $model ?>Contract.<?= strtoupper ($fields [$primary]->json) ?> + " = ?", new String [] { <?= $useCode ? 'code' : 'String.valueOf (id)' ?> }, null, null, null, "1");

		cursor.moveToNext ();

		<?= $model ?> p = <?= $model ?>Converter.from (cursor);
		
		cursor.close ();
		
		return p;
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
			db.update (<?= $model ?>Contract.TABLE, v, <?= $model ?>Contract.<?= strtoupper ($fields [$primary]->json) ?> + " = ?", new String [] { String.valueOf (item.get<?= ucwords ($fields [$primary]->class) ?> ()) });
		}
		catch (SQLException e)
		{
			throw new TechnicalException ("Impossível atualizar os dados!", e);
		}
	}
	
	public void insertOrUpdate (List<<?= $model ?>> list)
	{
		for (<?= $model ?> item : list)
			insertOrUpdate (item);
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
			db.update (<?= $model ?>Contract.TABLE, v, <?= $model ?>Contract.<?= strtoupper ($fields [$primary]->json) ?> + " = ?", new String [] { String.valueOf (item.get<?= ucwords ($fields [$primary]->class) ?> ()) });
		}
	}
	
	public void delete (<?= $useCode ? 'String code' : 'long id' ?>)
	{
		db.delete (<?= $model ?>Contract.TABLE, <?= $model ?>Contract.<?= strtoupper ($fields [$primary]->json) ?> + " = ?", new String [] { <?= $useCode ? 'code' : 'String.valueOf (id)' ?> });
	}
	
	public void delete (<?= $model ?> item)
	{
		db.delete (<?= $model ?>Contract.TABLE, <?= $model ?>Contract.<?= strtoupper ($fields [$primary]->json) ?> + " = ?", new String [] { String.valueOf (item.get<?= ucwords ($fields [$primary]->class) ?> ()) });
	}
	
	public void deleteNonActive (String active)
	{		
		db.delete (<?= $model ?>Contract.TABLE, <?= $model ?>Contract.<?= strtoupper ($fields [$primary]->json) ?> + " NOT IN (" + active.replaceAll ("[^0-9,.]", "") + ")", null);
	}
	
	public boolean empty ()
	{
		Cursor cursor = db.query (<?= $model ?>Contract.TABLE, new String [] { <?= $model ?>Contract.<?= strtoupper ($fields [$primary]->json) ?> }, null, null, null, null, null);
		
		return cursor.getCount () == 0 ? true : false;
	}
}
<?
return ob_get_clean ();
?>