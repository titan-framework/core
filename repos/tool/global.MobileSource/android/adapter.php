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

package <?= $app ?>.adapter;

import java.util.List;

import android.content.Context;
import android.graphics.Typeface;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ArrayAdapter;
import android.widget.ImageView;
import android.widget.TextView;

import <?= $app ?>.R;
import <?= $app ?>.model.<?= $model ?>;

public class <?= $model ?>ListAdapter extends ArrayAdapter<<?= $model ?>>
{
	public <?= $model ?>ListAdapter (Context context, List<<?= $model ?>> list)
	{
		super (context, 0, list);
	}

	public View getView (int position, View view, ViewGroup parent)
	{
		if (view == null)
			view = LayoutInflater.from (getContext ()).inflate (R.layout.<?= $modelUnderScore ?>_row, null);

		ImageView image = (ImageView) view.findViewById (R.id.<?= $modelUnderScore ?>_row_image);
		
		if (getItem (position).getImage () != 0)
			(new LoadUrlImageTask (image, "http://www.<?= implode ('.', array_reverse (explode ('.', $app))) ?>/photo/" + getItem (position).getImage () + "_0x100_0.jpg", context)).execute ();

		TextView title = (TextView) view.findViewById (R.id.<?= $modelUnderScore ?>_row_title);
		title.setText (getItem (position).getTitle ());

		TextView title = (TextView) view.findViewById (R.id.<?= $modelUnderScore ?>_row_description);
		title.setText (getItem (position).getDescription ());

		return view;
	}
}
<?php
return ob_get_clean ();
?>