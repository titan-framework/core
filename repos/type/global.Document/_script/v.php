<?php
if (!isset ($_GET['c']) || !is_numeric ($_GET['c']) || !((int) $_GET['c']) || !isset ($_GET['a']) || strlen (trim ($_GET['a'])) != 16)
	throw new Exception (__ ('Error! Data losted.'));

$_file = $_GET['c'];
$_auth = $_GET['a'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title><?= __ ('Document Validation') ?></title>
		<style type="text/css">
			html, body { height:100%; overflow:hidden; }
			body { margin:0; font-family: Arial, Helvetica, sans-serif; font-size: 12px; }
			div { margin: 10px; padding: 10px; font-weight: bold; text-align: center; }
			a { text-decoration: none; color: #000; }
			a:hover { text-decoration: underline; }
			.success { border: #C6D880 2px solid; color: #264409; background-color: #E6EFC2; }
			.warning { border: #FFD324 2px solid; color: #974721; background-color: #FFF6BF; }
			.error { border: #FBC2C4 2px solid; color: #B71F11; background-color: #FBE3E4; }
		</style>
	</head>
	<body>
		<?php
		$db = Database::singleton ();
		
		$sql = "SELECT table_name AS name, table_schema AS schema FROM information_schema.columns WHERE column_name='_file' AND column_default = 'nextval((''". $db->getSchema () ."._document''::text)::regclass)'";
		
		$stt = $db->prepare ($sql);
		
		$stt->execute ();
		
		$querys = array ();
		
		while ($table = $stt->fetch (PDO::FETCH_OBJ))
		{
			$sth = $db->prepare ("SELECT _version, _relation, _id, _file, _auth FROM ". $table->schema .".". $table->name ." WHERE _file = '". $_file ."' AND _auth = '". $_auth ."' AND _validate = B'1' AND _hash IS NOT NULL");
			
			$sth->execute ();
			
			$term = $sth->fetch (PDO::FETCH_OBJ);
			
			if (!$term)
				continue;
			
			$query = $db->query ("SELECT MAX(_version) FROM ". $table->schema .".". $table->name ." WHERE _relation = '". $term->_relation ."' AND _id = '". $term->_id ."'");
			
			$version = (int) $query->fetchColumn ();
			
			if ($version > (int) $term->_version)
			{
				$sth = $db->prepare ("SELECT _version, _relation, _id, _file, _auth FROM ". $table->schema .".". $table->name ." WHERE _relation = '". $term->_relation ."' AND _id = '". $term->_id ."' AND _version = '". $version ."' AND _validate = B'1' AND _hash IS NOT NULL");
				
				$sth->execute ();
				
				$obj = $sth->fetch (PDO::FETCH_OBJ);
				
				echo '<div class="warning">'. __ ('Warning! The document exists in our database, however this is not your last version. The document that you have on hand is version [1], but is the correct version [2], available at the link below:', $term->_version, $version) .'<br /><br />'; 
				
				if ($obj)
					echo '<a href="?target=tScript&type=Document&file=view&c='. $obj->_file .'&a='. $obj->_auth .'" target="_blank">'. __ ('VIEW DOCUMENT') .'</a>';
				else
					echo '<b>'. __ ('DOCUMENT IS NOT GENERATED') .'</b>';
				
				echo '&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;<a href="#" onclick="JavaScript: history.back ();">'. __ ('GO BACK') .'</a></div>';
			}
			else
				echo '<div class="success">'. __ ('The document exists in our database! It\'s available at the link below:') .'<br /><br /><a href="?target=tScript&type=Document&file=view&c='. $term->_file .'&a='. $term->_auth .'" target="_blank">'. __ ('VIEW DOCUMENT') .'</a>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;<a href="#" onclick="JavaScript: history.back ();">'. __ ('GO BACK') .'</a></div>';
			
			exit ();
		}
		
		echo '<div class="error">'. __ ('This document do not exists in our database or do not be open!') .'<br /><br /><a href="#" onclick="JavaScript: history.back ();">'. __ ('GO BACK') .'</a></div>';
		?>
	</body>
</html>