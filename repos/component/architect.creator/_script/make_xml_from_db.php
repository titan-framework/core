<?
//include '../config.inc.php';

$_DB ['HOST'] = 'localhost';
$_DB ['DATABASE'] = 'titan';
$_DB ['USER'] = 'titan';
$_DB ['PASSWORD'] = 't1t4N.05/';

$connection = pg_connect ('host='. $_DB ['HOST'] .' port=5432 dbname='. $_DB ['DATABASE'] .' user='. $_DB ['USER'] .' password='. $_DB ['PASSWORD']);

$sql = 'SELECT * FROM instance';

$query = pg_query ($sql);

$i = pg_num_fields ($query);

for ($j = 0 ; $j < $i ; $j++)
{
	$fieldName = trim (pg_field_name ($query, $j));
	$fieldType = trim (pg_field_type ($query, $j));
	
	switch ($fieldType)
	{
		case 'int4':
			echo '&lt;field type="Integer" column="'. $fieldName .'" label="'. $fieldName .'" /&gt;<br />';
			break;
		
		case 'int2':
			echo '&lt;field type="Enum" column="'. $fieldName .'" label="'. $fieldName .'"&gt;<br />';
			echo '&lt;enum-mapping column="0"&gt;NS/NR&lt;/enum-mapping&gt;<br />';
			echo '&lt;/field&gt;<br />';
			break;
		
		case 'varchar':
			echo '&lt;field type="String" column="'. $fieldName .'" label="'. $fieldName .'" max-length="256" /&gt;<br />';
			break;
		
		case 'bit':
			echo '&lt;field type="Boolean" column="'. $fieldName .'" label="'. $fieldName .'" /&gt;<br />';
			break;
		
		case 'text':
			echo '&lt;field type="Text" column="'. $fieldName .'" label="'. $fieldName .'" /&gt;<br />';
			break;
		
		case 'timestamp':
			echo '&lt;field type="Date" column="'. $fieldName .'" label="'. $fieldName .'" /&gt;<br />';
			break;
		
		default:
			echo '<font color="#990000"><b>'. $fieldType .'</b></font><br />';
			break;
	}
}
?>