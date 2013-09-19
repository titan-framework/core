<?
ob_start ();

echo '<?xml version="1.0" encoding="utf-8"?>'."\n";
?>
<ScrollView xmlns:android="http://schemas.android.com/apk/res/android"
	android:layout_width="match_parent"
	android:layout_height="match_parent"
	android:background="#FFEEEEEE"
	android:padding="5dp">
	
	<LinearLayout
		android:layout_width="fill_parent"
		android:layout_height="wrap_content"
		android:orientation="vertical"
		android:gravity="center_horizontal"
		android:layout_margin="5dp">
		
<?
foreach ($fields as $trash => $obj)
{
	echo '		<TextView android:id="@+id/'. $modelUnderScore .'_view_'. $obj->json .'_title" android:text="'. strtoupper ($obj->label) .'" android:textStyle="bold" android:background="@drawable/title_background" android:textSize="16sp" android:layout_width="fill_parent" android:layout_height="wrap_content" android:layout_margin="5dp" android:paddingTop="5dp" android:paddingBottom="5dp" />'."\n";
	echo "		\n";
	echo '		<TextView android:id="@+id/'. $modelUnderScore .'_view_'. $obj->json .'" android:text="" android:textSize="16sp" android:layout_width="fill_parent" android:layout_height="wrap_content" android:layout_margin="5dp" />'."\n";
	echo "		\n";
}
?>
	</LinearLayout>

</ScrollView>
<?
return ob_get_clean ();
?>