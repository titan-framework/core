<pre>
<?php
error_reporting (E_ALL);
set_time_limit (0);
ini_set ('memory_limit', '-1');

$db = Database::singleton ();

try
{
	$db->beginTransaction ();
	
	$schema = $db->getSchema ();
	
	if (!Database::tableExists ('_cloud'))
	{
		echo "INFO > Table [_cloud] do not exists! Try to create... \n";
		
		$sql = "CREATE TABLE _cloud (
					_id BIGSERIAL,
					_code VARCHAR DEFAULT currval('_cloud__id_seq'::regclass)::character varying NOT NULL,
					_name VARCHAR(256),
					_mimetype VARCHAR(256),
					_size INTEGER DEFAULT 0 NOT NULL,
					_counter BIGINT DEFAULT 0 NOT NULL,
					_user INTEGER NOT NULL,
					_create TIMESTAMP WITH TIME ZONE DEFAULT now() NOT NULL,
					_update TIMESTAMP WITH TIME ZONE DEFAULT now() NOT NULL,
					_author INTEGER,
					_devise TIMESTAMP WITH TIME ZONE,
					_change TIMESTAMP WITH TIME ZONE,
					_ready BIT(1) DEFAULT B'0' NOT NULL,
					_deleted BIT(1) DEFAULT B'0' NOT NULL,
					CONSTRAINT _cloud__code_key UNIQUE(_code),
					CONSTRAINT _cloud_pkey PRIMARY KEY(_id),
					CONSTRAINT _cloud_author_fk FOREIGN KEY (_author)
					REFERENCES _user(_id)
					ON DELETE RESTRICT
					ON UPDATE CASCADE
					NOT DEFERRABLE,
					CONSTRAINT _cloud_user_fk FOREIGN KEY (_user)
					REFERENCES _user(_id)
					ON DELETE RESTRICT
					ON UPDATE CASCADE
					NOT DEFERRABLE
				)";
		
		$db->exec ($sql);
		
		echo "SUCCESS > Created table [_cloud]! \n\n";
	}
	
	$query = $db->query ("SELECT COUNT(*) AS n FROM _cloud");
	
	if ((int) $query->fetchColumn ())
		throw new Exception ('CRITICAL > Table [_cloud] must be empty!'); 
	
	echo "INFO > Copying content of table [_file] to [_cloud]... \n";
	
	$sql = "INSERT INTO _cloud (_id, _code, _name, _mimetype, _size, _user, _create, _update, _author, _change, _devise, _ready)
			SELECT
				_id,
				_id::VARCHAR AS _code,
				_name,
				_mimetype,
				_size,
				_user,
				_create_date AS _create,
				_create_date AS _update,
				_user AS _author,
				_create_date AS _devise,
				_create_date AS _change,
				B'1' AS _ready
			FROM _file";
	
	$db->exec ($sql);
	
	$sth = $db->prepare ("SELECT * FROM _file");
	
	$sth->execute ();
	
	$obj = $sth->fetch (PDO::FETCH_OBJ);
	
	$cSth = $db->prepare ("UPDATE _cloud SET _counter = :c WHERE _id = :id");
	
	if (isset ($obj->_counter))
		do
		{
			$cSth->bindParam (':c', $obj->_counter, PDO::PARAM_INT);
			$cSth->bindParam (':id', $obj->_id, PDO::PARAM_INT);
			
			$cSth->execute ();
			
		} while ($obj = $sth->fetch (PDO::FETCH_OBJ));
	
	$query = $db->query ("SELECT MAX(_id) AS n FROM _cloud");
	
	$max = (int) $query->fetchColumn ();
	
	$db->exec ("SELECT setval('_cloud__id_seq', ". $max .", true)");
	
	echo "SUCCESS > All tuples copied! \n\n";
	
	$path = Archive::singleton ()->getDataPath ();
	
	$sth = $db->prepare ("SELECT _id FROM _file");
	
	$sth->execute ();
	
	echo "INFO > Copying binary files of [_file] to [_cloud]... \n";
	
	while ($obj = $sth->fetch (PDO::FETCH_OBJ))
	{
		$src = $path . 'file_' . str_pad ($obj->_id, 7, '0', STR_PAD_LEFT);
		
		if (!file_exists ($src))
			$src = $path . 'file_' . str_pad ($obj->_id, 19, '0', STR_PAD_LEFT);
		
		$dst = $path . 'cloud_' . str_pad ($obj->_id, 19, '0', STR_PAD_LEFT);
		
		if (file_exists ($src) && is_readable ($src) && (int) filesize ($src))
			if (copy ($src, $dst))
				echo "SUCCESS > File [". $src ."] copied to [". $dst ."] \n";
			else
				throw new Exception ('CRITICAL > Impossible to copy file ['. $src .' > '. $dst .']!');
	}
	
	echo "SUCCESS > All files copied! \n\n";
	
	echo "INFO > Changing foreign keys in all database tables to point from [_file] to [_cloud] table... \n\n";
	
	$sql = "SELECT
				tc.constraint_name, tc.constraint_schema, tc.table_name, tc.table_schema, kcu.column_name, 
				ccu.table_name AS foreign_table_name, ccu.column_name AS foreign_column_name 
			FROM information_schema.table_constraints AS tc 
			JOIN information_schema.key_column_usage AS kcu ON tc.constraint_name = kcu.constraint_name
			JOIN information_schema.constraint_column_usage AS ccu ON ccu.constraint_name = tc.constraint_name
			WHERE constraint_type = 'FOREIGN KEY' AND ccu.table_name = '_file' AND ccu.table_schema = :schema";
	
	$sth = $db->prepare ($sql);
	
	$sth->bindParam (':schema', $schema, PDO::PARAM_STR);
	
	$sth->execute ();
	
	while ($obj = $sth->fetch (PDO::FETCH_OBJ))
	{
		$drop = "ALTER TABLE ". $obj->table_schema .".". $obj->table_name ." DROP CONSTRAINT ". $obj->constraint_name;
		
		$add = "ALTER TABLE ". $obj->table_schema .".". $obj->table_name ." ADD CONSTRAINT ". $obj->constraint_name ." FOREIGN KEY (". $obj->column_name .") REFERENCES titan._cloud(_id) ON DELETE RESTRICT ON UPDATE CASCADE NOT DEFERRABLE";
		
		$db->exec ($drop);
		$db->exec ($add);
		
		echo "SUCCESS > Done to: \n";
		
		echo $drop ."\n";
		echo $add ."\n\n";
	}
	
	echo "SUCCESS > All constraint changed! \n\n";
	
	echo "INFO > Changing type of fields in XMLs of sections [from 'File' to 'CloudFile']... \n";
	
	$remove = array ('.', '..', '.svn');
	
	$path = getcwd () . DIRECTORY_SEPARATOR .'section';
	
	$dh = opendir ($path);
	
	while (($file = readdir ($dh)) !== false)
	{
		if (in_array ($file, $remove))
			continue;
		
		$fullpath = realpath ($path . DIRECTORY_SEPARATOR . $file);
		
		if (!is_dir ($fullpath) || is_link ($fullpath))
			continue;
		
		foreach (glob ($fullpath . DIRECTORY_SEPARATOR .'*.xml') as $xml)
		{
			file_put_contents ($xml, str_replace ('type="File"', 'type="CloudFile"', file_get_contents ($xml)));
			
			echo "SUCCESS > The file [". $xml ."] has changed! \n";
		}
	}
	
	closedir ($dh);
	
	echo "SUCCESS > All XMLs changed! \n\n";
	
	$db->commit ();
	
	echo "SUCCESS > All done!";
}
catch (Exception $e)
{
	$db->rollBack ();
	
	die ($e->getMessage ());
}
catch (Exception $e)
{
	$db->rollBack ();
	
	die ('CRITICAL  > '. $e->getMessage ());
}
?>
</pre>