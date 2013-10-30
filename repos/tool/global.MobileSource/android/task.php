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

package <?= $app ?>.task;

import android.app.ProgressDialog;
import android.content.SharedPreferences;
import android.os.AsyncTask;

import <?= $app ?>.<?= $appName ?>;
import <?= $app ?>.<?= $model ?>ListActivity;
import <?= $app ?>.converter.<?= $model ?>Converter;
import <?= $app ?>.dao.<?= $model ?>DAO;
import <?= $app ?>.model.<?= $model ?>;
import <?= $app ?>.ws.<?= $model ?>WebService;
import <?= $app ?>.util.Preferences;
import <?= $app ?>.util.ScreenHelper;

public class <?= $model ?>Task extends AsyncTask<Void, Void, Boolean>
{
	private ProgressDialog progress;
	
	private <?= $model ?>ListActivity activity;
	
	private Exception exception;
	
	public <?= $model ?>Task (<?= $model ?>ListActivity a)
	{
		activity = a;
	}

	@Override
	protected void onPreExecute ()
	{
		ScreenHelper.lock (activity);
		
		progress = ProgressDialog.show (activity, "Sincronizando", "Sincronizando itens! Se esta for a primeira vez que você faz esta sincronização, poderá demorar alguns segundos.", true, false);
	}

	@Override
	protected Boolean doInBackground (Void... params)
	{
		try
		{
			String active = <?= $model ?>WebService.active ();
			
			<?= $model ?>DAO.singleton ().delete (active);
			
			SharedPreferences preferences = Preferences.singleton ();
			
			long time = preferences.getLong ("lastSyncFor<?= $model ?>", 0);
			
			<?= $model ?>WebService ws = new <?= $model ?>WebService ();
			
			String json = ws.list (time);
			
			List<<?= $model ?>> list = <?= $model ?>Converter.fromJsonString (json);
			
			if (list.size () == 0)
				return true;
			
			<?= $model ?>DAO.singleton ().insertOrUpdate (list);
			
			SharedPreferences.Editor editor = preferences.edit ();
			
			editor.putLong ("lastSyncFor<?= $model ?>", ws.getServerTime ());

			editor.commit ();

			return true;
		}
		catch (Exception e)
		{
			exception = e;
		}
		
		return false;
	}

	@Override
	protected void onPostExecute (Boolean success)
	{
		progress.dismiss ();
		
		ScreenHelper.unlock (activity);
		
		if (!success && exception != null)
			activity.fail (exception.getMessage ());
		
		((<?= $appName ?>) activity.getApplication ()).checkSync (activity.getClass ().getName ());
		
		activity.refresh ();
	}
}
<?
return ob_get_clean ();
?>