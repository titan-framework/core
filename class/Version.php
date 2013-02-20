<?
/**
 * Version.php
 *
 * This class create DB artefacts for activate Titan Version Control.
 *
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @category class
 * @package core
 * @subpackage version
 * @copyright Creative Commons Attribution No Derivatives (CC-BY-ND)
 * @see VersionForm, VersionView, VersionSearch
 */
class Version
{
	static private $version = FALSE;
	
	private $array = array ();
	
	private final function __construct ()
	{
		$fromXml = Instance::singleton ()->getVersionControl ();
		
		$this->array = array ('schema' => 'public');
		
		foreach ($this->array as $key => $trash)
			if (array_key_exists ($key, $fromXml))
				$this->array [$key] = trim ($fromXml [$key]);
	}
	
	static public function singleton ()
	{
		if (self::$version !== FALSE)
			return self::$version;
		
		$class = __CLASS__;
		
		self::$version = new $class ();
		
		return self::$version;
	}
	
	public function getSchema ()
	{
		return $this->array ['schema'];
	}
	
	public function vcTable ($table, $schema = FALSE)
	{
		$array = explode ('.', $table);
		
		$db = Database::singleton ();
		
		if (sizeof ($array) == 2)
		{
			$schema = $schema === FALSE ? $array [0] : $schema;
			$table = $array [1];
		}
		elseif ($schema === FALSE)
			$schema = $db->getSchema ();
		
		return $this->getSchema () .'._tvc_'. $schema .'_'. $table; 
	}
	
	public function hasControl ($table, $schema = FALSE)
	{
		return tableExists ($this->vcTable ($table, $schema));
	}
	
	public function make ($table, $primary, $schema = FALSE)
	{
		$array = explode ('.', $table);
		
		$db = Database::singleton ();
		
		if (sizeof ($array) == 2)
		{
			$schema = !$schema ? $array [0] : $schema;
			$table = $array [1];
		}
		elseif (!$schema)
			$schema = $db->getSchema ();
		
		if ($this->hasControl ($table, $schema))
			throw new Exception ('A tabela indicada já esta sob controle de versões!');
		
		$sql = "SELECT
					attnum AS number, 
					attname AS name, 
					typname AS type, 
					atttypmod-4 AS size, 
					attnotnull AS not_null, 
					atthasdef AS has_default, 
					adsrc AS value_default 
				FROM (
						(
							SELECT attnum, attname, typname, atttypmod, attnotnull, atthasdef, adsrc 
							FROM pg_attribute, pg_class, pg_type, pg_attrdef 
							WHERE 
								pg_class.oid = attrelid AND 
								pg_type.oid = atttypid AND 
								attnum > 0 AND 
								pg_class.oid = adrelid AND 
								adnum = attnum AND 
								atthasdef = 't' AND 
								lower(relname) = '". $table ."'
						)
						UNION
						( 
							SELECT attnum, attname, typname, atttypmod, attnotnull, atthasdef, NULL AS adsrc 
							FROM pg_attribute, pg_class, pg_type 
							WHERE 
								pg_class.oid = attrelid AND 
								pg_type.oid = atttypid AND 
								attnum > 0 AND 
								atthasdef = 'f' AND 
								lower(relname) = '". $table ."'
						)
					) sq
				ORDER BY sq.attnum";
		
		$sth = $db->prepare ($sql);
		
		$sth->execute ();
		
		$fields = array ();
		while ($field = $sth->fetch (PDO::FETCH_OBJ))
			$fields [] = '"'. $field->name .'" '. (trim ($field->type) == 'bpchar' ? 'CHAR' : strtoupper ($field->type)) . ((int) $field->size > 0 ? '('. $field->size .')' : '') . ((int) $field->not_null ? ' NOT NULL' : '');
		
		$fields [] = '"_tvc_date" TIMESTAMP WITHOUT TIME ZONE DEFAULT now() NOT NULL';
		$fields [] = '"_tvc_version" INTEGER NOT NULL';
		$fields [] = '"_tvc_action" CHAR(1) NOT NULL';
		
		$commands = array ();
		
		$commands [] = 'CREATE TABLE "'. $this->getSchema () .'"."_tvc_'. $schema .'_'. $table .'" ('. implode (', ', $fields) .') WITHOUT OIDS;';
		
		$commands [] = 'ALTER TABLE "'. $this->getSchema () .'"."_tvc_'. $schema .'_'. $table .'" ADD CONSTRAINT "_tvc_'. $schema .'_'. $table .'_pkey" PRIMARY KEY ("'. $primary .'", "_tvc_version");';
		
		ob_start ();
		?>
		CREATE OR REPLACE FUNCTION "<?= $schema ?>"."_tvc_<?= $schema ?>_<?= $table ?>" () RETURNS trigger AS
		$body$
		DECLARE
		  version integer;
		BEGIN
		  IF (TG_OP = 'UPDATE') THEN
			 SELECT INTO version max(_tvc_version) FROM "<?= $this->getSchema () ?>"."_tvc_<?= $schema ?>_<?= $table ?>" WHERE <?= $primary ?> = OLD.<?= $primary ?>;
			 
			 IF (version IS NULL) THEN
				version = '1';
			 ELSE
				version = version + 1;
			 END IF;
			 
			 INSERT INTO "<?= $this->getSchema () ?>"."_tvc_<?= $schema ?>_<?= $table ?>" SELECT NEW.*, CURRENT_TIMESTAMP, version, 'U';
			 
			 RETURN NEW;
		  ELSIF (TG_OP = 'INSERT') THEN
			 INSERT INTO "<?= $this->getSchema () ?>"."_tvc_<?= $schema ?>_<?= $table ?>" SELECT NEW.*, CURRENT_TIMESTAMP, '1', 'I';
			 
			 RETURN NEW;
		  ELSIF (TG_OP = 'DELETE') THEN
			 SELECT INTO version max(_tvc_version) FROM "<?= $this->getSchema () ?>"."_tvc_<?= $schema ?>_<?= $table ?>" WHERE <?= $primary ?> = OLD.<?= $primary ?>;
			 
			 IF (version IS NULL) THEN
				version = '1';
			 ELSE
				version = version + 1;
			 END IF;
			 
			 INSERT INTO "<?= $this->getSchema () ?>"."_tvc_<?= $schema ?>_<?= $table ?>" SELECT OLD.*, CURRENT_TIMESTAMP, version, 'D';
			 
			 RETURN OLD;
		  END IF;
		END
		$body$
		LANGUAGE 'plpgsql' VOLATILE CALLED ON NULL INPUT SECURITY INVOKER;
		<?
		$commands [] = ob_get_clean ();
		
		$commands [] = 'CREATE TRIGGER "_tvc_'. $schema .'_'. $table .'" BEFORE INSERT OR UPDATE OR DELETE ON "'. $schema .'"."'. $table .'" FOR EACH ROW EXECUTE PROCEDURE "'. $schema .'"."_tvc_'. $schema .'_'. $table .'"();';
		
		$commands [] = 'UPDATE '. $schema .'.'. $table .' SET '. $primary .' = '. $primary;
		
		// throw new Exception (print_r (implode ("\n\n", $commands), TRUE));
		
		try
		{
			$db->beginTransaction ();
			
			foreach ($commands as $trash => $command)
				$db->exec ($command);
			
			$db->commit ();
		}
		catch (PDOException $e)
		{
			$db->rollBack ();
			
			throw new Exception ($e->getMessage () .' ['. $command .']');
		}
		
		return TRUE;
	}
}
?>