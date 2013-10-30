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

package <?= $app ?>;

import android.content.ActivityNotFoundException;
import android.content.Intent;
import android.net.Uri;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;
import android.widget.TextView;
import android.widget.Toast;

import com.actionbarsherlock.view.Menu;
import com.actionbarsherlock.view.MenuItem;
import com.github.rtyley.android.sherlock.roboguice.activity.RoboSherlockFragmentActivity;
import roboguice.inject.ContentView;
import roboguice.inject.InjectView;

import <?= $app ?>.R;
import <?= $app ?>.dao.<?= $model ?>DAO;
import <?= $app ?>.model.<?= $model ?>;

import <?= $app ?>.util.RoboSherlockActivityAbstract;

@ContentView (R.layout.<?= $modelUnderScore ?>_view)
public class <?= $model ?>ViewActivity extends RoboSherlockFragmentActivity implements RoboSherlockActivityAbstract.DoNothing
{
<?
foreach ($fields as $key => $obj)
	if ($key)
	{
		echo "	@InjectView (R.id.". $modelUnderScore ."_view_". $obj->json ."_title) TextView ". $obj->class ."Title;\n";
		echo "	@InjectView (R.id.". $modelUnderScore ."_view_". $obj->json .") TextView ". $obj->class .";\n";
		echo "	\n";
	}
?>
	public static String ID = "ID";

	private <?= $model ?> <?= $object ?>;
	
	@Override
	public void onCreate (Bundle bundle)
	{
		super.onCreate (bundle);
		
		getSupportActionBar ().setHomeButtonEnabled (true);

		getSupportActionBar ().setDisplayHomeAsUpEnabled (true);

		<?= $object ?> = <?= $model ?>DAO.singleton ().get ((long) getIntent ().getLongExtra (ID, 0));

		getSupportActionBar ().setTitle ("Título (ALTERAR)");
		
		NumberFormat nf = NumberFormat.getInstance (new Locale ("pt", "BR"));
		
		nf.setMaximumFractionDigits (2);
		nf.setMinimumFractionDigits (2);
		
<?
foreach ($fields as $trash => $obj)
	switch ($obj->type)
	{
		case 'Integer':
			echo "		". $obj->class .".setText (String.valueOf (". $object .".get". ucfirst ($obj->class) ." ()));\n";
			echo "		\n";
			break;
		
		case 'Double':
			echo "		". $obj->class .".setText (nf.format (". $object .".get". ucfirst ($obj->class) ." ()));\n";
			echo "		\n";
			break;
		
		case 'Date':
			echo "		". $obj->class .".setText (new SimpleDateFormat (\"d/M/yy\", Locale.US).format (". $object .".get". ucfirst ($obj->class) ." ()));\n";
			echo "		\n";
			break;
		
		case 'Boolean':
			echo "		". $obj->class .".setText (". $object .".get". ucfirst ($obj->class) ." () ? \"Sim\" : \"Não\");\n";
			echo "		\n";
			break;
		
		default:
			?>
		if (<?= $object ?>.get<?= ucfirst ($obj->class) ?> ().equals (""))
		{
			<?= $obj->class ?>.setVisibility (View.GONE);
			<?= $obj->class ?>Title.setVisibility (View.GONE);
		}
		else
			<?= $obj->class ?>.setText (<?= $object ?>.get<?= ucfirst ($obj->class) ?> ());
		
<?
			break;
	}
?>
	}

	@Override
	public boolean onOptionsItemSelected (MenuItem item)
	{
		switch (item.getItemId ())
		{
			case android.R.id.home:

				super.onBackPressed ();

				return true;
		}

		return super.onOptionsItemSelected (item);
	}
}
<?
return ob_get_clean ();
?>