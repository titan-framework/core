<?php
ob_start ();

echo '<?xml version="1.0" encoding="utf-8"?>'."\n";
?>
<LinearLayout xmlns:android="http://schemas.android.com/apk/res/android"
	android:layout_width="match_parent"
	android:layout_height="wrap_content"
	android:minHeight="60dp"
	android:gravity="center_vertical"
	android:orientation="horizontal"
	android:padding="5dp">
	
	<ImageView android:id="@+id/<?= $modelUnderScore ?>_row_image" android:src="@drawable/no_image" android:layout_width="50dp" android:layout_height="50dp" android:layout_marginRight="10dp" android:layout_gravity="center_vertical" />
	
	<LinearLayout
		android:layout_width="fill_parent"
		android:layout_height="wrap_content"
		android:gravity="center_vertical"
		android:orientation="vertical">
		
		<TextView android:id="@+id/<?= $modelUnderScore ?>_row_title" android:layout_width="fill_parent" android:layout_height="wrap_content" android:textSize="14sp" android:textStyle="bold" />
		
		<TextView android:id="@+id/<?= $modelUnderScore ?>_row_description" android:layout_width="fill_parent" android:layout_height="wrap_content" android:textSize="12sp" />
	
	</LinearLayout>

</LinearLayout>
<?php
return ob_get_clean ();
?>