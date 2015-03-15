<?
class Ajax
{
	public function nameValidate ($name)
	{
		return fileName ($name);
	}
	
	public function verify ($test, $name)
	{
		$message = Message::singleton ();

		try
		{
			switch ($test)
			{
				case 'UNIQUE':
					$db = Database::singleton ();

					$sth = $db->prepare ("SELECT COUNT(*) AS total FROM _instance WHERE _unix = '". $name ."'");

					$sth->execute ();

					$obj = $sth->fetch (PDO::FETCH_OBJ);

					if (!$obj->total)
						return TRUE;

					return FALSE;

				case 'VALID_PATH':
					if ((file_exists ('instance') && is_dir ('instance')) || @mkdir ('instance', 0777))
						return TRUE;

					return FALSE;

				case 'EXISTS':
					if (file_exists ('instance/'. $name))
						return FALSE;

					return TRUE;

				case 'WRITABLE':
					if (is_writable ('instance'))
						return TRUE;

					return FALSE;
			}
		}
		catch (PDOException $e)
		{
			$message->addWarning ($e->getMessage ());
		}

		$message->save ();

		return FALSE;
	}

	public function saveInstance ($unixName, $insert = FALSE, $form = FALSE)
	{
		$message = Message::singleton ();

		try
		{
			$db = Database::singleton ();

			$user = User::singleton ();

			if ($insert)
				$sql = "INSERT INTO _instance (_unix, _name, _description, _user) VALUES ('". $unixName ."', '". $form ['instance_name'] ."', '". $form ['instance_description'] ."', '". $user->getId () ."')";
			else
				if (!$form)
					$sql = "UPDATE _instance SET _update = NOW(), _user = '". $user->getId () ."' WHERE _unix = '". $unixName ."'";
				else
					$sql = "UPDATE _instance SET _update = NOW(), _user = '". $user->getId () ."', _name = '". $form ['name'] ."', _description = '". $form ['description'] ."' WHERE _unix = '". $unixName ."'";
			
			$sth = $db->prepare ($sql);

			$sth->execute ();
			toLog (print_r ($sql, TRUE));
			return TRUE;
		}
		catch (PDOException $e)
		{
			$message->addWarning ($e->getMessage ());
		}
		catch (Exception $e)
		{
			$message->addWarning ($e->getMessage ());
		}

		$message->save ();

		return FALSE;
	}
	
	public function custTitanMain ($name, $form, $category = FALSE)
	{
		$message = Message::singleton ();
		
		$array = array ();
		
		if ($category === FALSE)
		{
			$url = 'http://'. $_SERVER['HTTP_HOST'] . str_replace ('titan.php', 'instance/'. $name .'/', $_SERVER['PHP_SELF']);
			
			$instance = Instance::singleton ();

			$array ['main'] = array (	'name'			=> $form ['instance_name'],
										'description' 	=> $form ['instance_description'],
										'url'			=> $url,
										'e-mail'		=> $form ['instance_email'],
										'login-url'		=> $url .'titan.php?target=login',
										'core-path'		=> '../../'. $instance->getCorePath (),
										'repos-path'	=> '../../'. $instance->getReposPath (),
										'session'		=> randomHash ());

			$array ['security'] = array ('hash'    => randomHash ());

			$array ['search'] = array ('hash'   => randomHash ());
		}
		else
			$array [$category] = $form;

		try
		{
			customizeTitan ($array, 'instance/'. $name .'/configure/titan.xml');
			
			if ($category !== FALSE)
				if ($category == 'main')
					$this->saveInstance ($name, FALSE, $form);
				else
					$this->saveInstance ($name);
			
			return TRUE;
		}
		catch (Exception $e)
		{
			$message->addWarning ($e->getMessage ());
		}

		$message->save ();

		return FALSE;
	}

	public function loadDbConfig ($name)
	{
		$message = Message::singleton ();

		try
		{
			$file = 'instance/'. $name .'/configure/titan.xml';

			if (!file_exists ($file))
				throw new Exception ('O arquivo ['. $file .'] não foi encontrado no caminho especificado.');

			$xml = new Xml ($file);

			$array = $xml->getArray ();

			if (!isset ($array ['titan-configuration'][0]))
				throw new Exception ('A tag &lt;titan-configuration&gt;&lt;/titan-configuration&gt; não existe no arquivo ['. $file .'].');

			if (!isset ($array ['titan-configuration'][0]['database'][0]))
				throw new Exception ('A tag &lt;database /&gt; não existe no arquivo ['. $file .'].');

			$array = $array ['titan-configuration'][0]['database'][0];

			XOAD_HTML::importForm('formConfigDB', $array);

			return TRUE;
		}
		catch (Exception $e)
		{
			$message->addWarning ($e->getMessage ());
		}

		$message->save ();

		return FALSE;
	}
	
	public function createDb ($name)
	{
		$message = Message::singleton ();

		try
		{
			$section = Business::singleton ()->getSection (Section::TCURRENT);
			
			$dbUser = trim ($section->getDirective ('_CREATE_DB_USER_'));
			$dbPass = trim ($section->getDirective ('_CREATE_DB_PASSWD_'));
			$dbComm = str_replace (array ('[USER]', '[NAME]'), array ($dbUser, $name), trim ($section->getDirective ('_CREATE_DB_COMMAND_')));
			
			set_error_handler ('logPhpError');
			
			system ($dbComm);
			
			restore_error_handler ();
			
			$array = array ('name' 		=> $name,
							'user' 		=> $dbUser,
							'password'	=> $dbPass);
			
			XOAD_HTML::importForm('formConfigDB', $array);

			return TRUE;
		}
		catch (Exception $e)
		{
			$message->addWarning ($e->getMessage ());
		}

		$message->save ();

		return FALSE;
	}
	
	public function saveUserFiles ($form, $newUser)
	{
		$message = Message::singleton ();

		try
		{
			$name = $_SESSION['UNIX_NAME'];
			
			$file = 'instance/'. $name .'/section/'. $newUser .'/default.xml';

			if (!file_exists ($file))
				throw new Exception ('O arquivo ['. $file .'] não foi encontrado no caminho especificado.');

			$xml = new Xml ($file);

			$array = $xml->getArray ();
			
			if (!isset ($array ['form'][0]['field']))
				throw new Exception ('O arquivo ['. $file .'] contêm erro de sintaxe!');
			
			$array = $array ['form'][0]['field'];
			
			$file = 'instance/'. $name .'/section/'. $newUser .'/default.ini';
			
			if (!file_exists ($file))
				throw new Exception ('Arquivo ['. $file .'] não encontrado!');
			
			$default = parse_ini_file ($file, TRUE);
			
			$files = array ('register', 'modify', 'profile', 'create', 'edit', 'list', 'search', 'view');
			
			foreach ($files as $trash => $file)
			{
				$xml = new XmlMaker ('  ', FALSE);
				
				$attr = array ();
				
				$attr ['table'] = '_user';
				
				$attr ['primary'] = '_id';
				
				switch ($file)
				{
					case 'list':
						$attr ['paginate'] = '15';
						
						$xml->push ('view', $attr);
						
						$attrs = array (
							array ('action' => 'view', 'image' => 'view.gif', 'label' => 'Visualizar Dados do Usuário', 'default' => 'true'),
							array ('action' => 'edit', 'image' => 'edit.gif', 'label' => 'Editar Dados do Usuário'),
							array ('action' => 'delete', 'image' => 'delete.gif', 'label' => 'Apagar Usuário')
						); 
			
						foreach ($attrs as $trash => $attr)
							$xml->emptyElement ('icon', $attr);
						
						break;
					
					case 'search':
						$xml->push ('search', $attr);
						break;
					
					default:
						$xml->push ('form', $attr);
						
						$attrs = array (		    
							array ('flag' => 'success', 'action' => '[default]'),
							array ('flag' => 'fail', 'action' => '[same]'),
							array ('flag' => 'cancel', 'action' => '[default]')
						); 
						
						foreach ($attrs as $trash => $attr)
							$xml->emptyElement ('go-to', $attr);
				}
				
				foreach ($array as $trash => $field)
				{
					if ($form [$file .'_'. $field ['column']] != TRUE && (!isset ($default [$field ['column']][$file]) || !((int) $default [$field ['column']][$file])))
						continue;
					
					$field ['label'] = trim ($form ['label_'. $field ['column']]);
					
					if (!isset ($field ['required']) || $field ['required'] != 'true')
						$field ['required'] = $form ['required_'. $field ['column']] == TRUE ? 'true' : 'false';
					
					if (!isset ($field ['unique']) || $field ['unique'] != 'true')
						$field ['unique'] = $form ['unique_'. $field ['column']] == TRUE ? 'true' : 'false';
					
					if (trim ($form ['ldap_'. $field ['column']]) != '')
						$field ['on-ldap-as'] = trim ($form ['ldap_'. $field ['column']]);
					
					if (trim ($form ['help_'. $field ['column']]) != '')
						$field ['help'] = trim ($form ['help_'. $field ['column']]);
					
					$detect = array ();
					
					foreach ($field as $key => $value)
						if (is_array ($value))
						{
							$detect [$key] = $value;
							
							unset ($field [$key]);
						}
					
					if (!sizeof ($detect))
						$xml->emptyElement ('field', $field);
					else
					{
						$xml->push ('field', $field);
						
						foreach ($detect as $key => $value)
							foreach ($value as $trash => $item)
								if (is_array ($item))
									$xml->emptyElement ($key, $item);
								else
									$xml->element ($key, $item);
						
						$xml->pop ();
					}
				}
				
				$xml->pop ();
	
				if (!file_put_contents ('instance/'. $name .'/section/'. $newUser .'/'. $file .'.xml', $xml->getXml ()))
					throw new Exception ('Impossível criar/alterar arquivo [instance/'. $name .'/section/'. $newUser .'/'. $file .'.xml]!');
			}
			
			return TRUE;
		}
		catch (Exception $e)
		{
			$message->addWarning ($e->getMessage ());
		}

		$message->save ();

		return FALSE;
	}
	
	public function verifyDB ($name, $db, $save = FALSE)
	{
		$message = Message::singleton ();

		try
		{
			$dbh = $this->connect ($db, FALSE);

			if ($save)
				$_SESSION['DBH_'. $name] = $db;

			return TRUE;
		}
		catch (PDOException $e)
		{
			$message->addWarning ($e->getMessage ());
		}
		catch (Exception $e)
		{
			$message->addWarning ($e->getMessage ());
		}

		$message->save ();

		return FALSE;
	}

	private function connect ($db, $useSchema = TRUE)
	{
		switch ($db ['sgbd'])
		{
			case 'MySQL':
				$dsn = 'mysql:host='. $db ['host'] .';dbname='. $db ['name'];
				break;

			case 'SQLServer':
				$dsn = 'mssql:host='. $db ['host'] .'; dbname='. $db ['name'];
				break;

			case 'FireBird':
				$dsn = 'firebird:User='. $db ['user'] .';Password='. $db ['password'] .';Database='. $db ['name'] .';DataSource='. $db ['host'] .';Port=3050';
				break;

			case 'Sybase':
				$dsn = 'sybase:host='. $db ['host'] .'; dbname='. $db ['name'];
				break;

			case 'PostgreSQL':
				$dsn = 'pgsql:host='. $db ['host'] .' port='. $db ['port'] .' dbname='. $db ['name'] .' user='. $db ['user'] .' password='. $db ['password'];
				break;

			case 'ODBC':
				$dsn = 'odbc:DSN=SAMPLE;UID='. $db ['user'] .';PWD='. $db ['password'];
				break;

			case 'SQLite':
				$dsn = 'sqlite:'. $db ['name'];
				break;

			case 'OCI':
				$dsn = 'oci:dbname=//'. $db ['host'] .':1521/'. $db ['name'];
				break;
		}
		
		$dbh = new PDO ($dsn, $db ['user'], $db ['password']);	
   		
		$dbh = new PDO ($dsn);
	    
		$dbh->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		$dbh->exec ('SET datestyle TO ISO, DMY');

		if ($useSchema)
			$dbh->exec ('SET search_path = titan');

		return $dbh;
	}

	public function importInstance ($name, $form)
	{
		$message = Message::singleton ();

		try
		{
			$file = 'instance/'. $name .'/configure/titan.xml';

			if (!file_exists ($file))
				throw new Exception ('O arquivo ['. $file .'] não foi encontrado no caminho especificado.');

			$xml = new Xml ($file);

			$array = $xml->getArray ();

			if (!isset ($array ['titan-configuration'][0]))
				throw new Exception ('A tag &lt;titan-configuration&gt;&lt;/titan-configuration&gt; não existe no arquivo ['. $file .'].');

			$array = $array ['titan-configuration'][0];

			if (trim ($form ['instance_name']) == '' && array_key_exists ('name', $array))
				$form ['instance_name'] = $array ['name'];

			if (trim ($form ['instance_description']) == '' && array_key_exists ('description', $array))
				$form ['instance_description'] = $array ['description'];

			if (trim ($form ['instance_email']) == '' && array_key_exists ('e-mail', $array))
				$form ['instance_email'] = $array ['e-mail'];

			unset ($form ['instance_unix']);
			unset ($form ['instance_base']);

			XOAD_HTML::importForm('formConfigInstance', $form);

			return TRUE;
		}
		catch (Exception $e)
		{
			$message->addWarning ($e->getMessage ());
		}

		$message->save ();

		return FALSE;
	}

	public function makeDB ($name)
	{
		$message = Message::singleton ();

		try
		{

			$file = 'instance/'. $name .'/db.sql';

			if (!file_exists ($file))
				throw new Exception ('O arquivo ['. $file .'] não foi encontrado no caminho especificado.');

			$db = $this->connect ($_SESSION['DBH_'. $name], FALSE);

			$db->beginTransaction ();

			$db->exec (file_get_contents($file));

			$db->commit ();

			@unlink ($file);

			return TRUE;
		}
		catch (PDOException $e)
		{
			$db->rollBack ();

			$message->addWarning ($e->getMessage ());
		}
		catch (Exception $e)
		{
			$message->addWarning ($e->getMessage ());
		}

		$message->save ();

		return FALSE;
	}

	public function saveLogo ($id, $name)
	{
		$message = Message::singleton ();

		try
		{
			$fileSrc = File::getFilePath ($id);
			
			if (!file_exists ($fileSrc))
				$fileSrc = File::getLegacyFilePath ($id);
			
			if (!file_exists ($fileSrc))
				throw new Exception ('O arquivo não existe fisicamente no diretório de arquivos. ['. $fileSrc .']');
			
			$fileDst = 'instance/' . $name . '/image/logo_' . str_pad ($id, 19, '0', STR_PAD_LEFT);
			
			if (!copy ($fileSrc, $fileDst))
				throw new Exception ('Impossível copiar o arquivo para a aplicação instanciada.');

			$array = array ('skin' => array ('logo' => 'image/logo_' . str_pad ($id, 19, '0', STR_PAD_LEFT)));

			customizeTitan ($array, 'instance/'. $name .'/configure/titan.xml');

			$db = Database::singleton ();

			$sth = $db->prepare ("UPDATE _instance SET _logo = '". $id ."' WHERE _unix = '". $name ."'");

			$sth->execute ();

			return TRUE;
		}
		catch (Exception $e)
		{
			$message->addWarning ($e->getMessage ());
		}
		catch (PDOException $e)
		{
			$message->addWarning ($e->getMessage ());
		}

		$message->save ();

		return FALSE;
	}

	public function loadNotes ($name)
	{
		try
		{
			$file = Instance::singleton ()->getReposPath () .'package/'. $name .'/readme.txt';

			if (!file_exists ($file))
				throw new Exception ('O arquivo não existe fisicamente. ['. $file .']');

			$buffer = nl2br (file_get_contents ($file));

			$file = Instance::singleton ()->getReposPath () .'package/'. $name .'/db.sql';

			if (file_exists ($file))
				$buffer .= '<br /><br />- SQL:<br /><pre>'. file_get_contents ($file) .'</pre>';

			return $buffer;
		}
		catch (Exception $e)
		{
			return $e->getMessage ();
		}
		catch (PDOException $e)
		{
			return $e->getMessage ();
		}
	}

	public function copySection ($section)
	{
		$message = Message::singleton ();

		try
		{
			if (!isset ($_SESSION['UNIX_NAME']) && isset ($_SESSION['SECTION_PROPS_'. $section]))
				throw new Exception ('Perda de variáveis! Impossível obter nome da instância.');

			$name = $_SESSION['UNIX_NAME'];

			$pack = $_SESSION['PACKS_'. $name][$section];

			$props = $_SESSION['SECTION_PROPS_'. $section];

			$packages = array ($section => $props ['_UNIX_NAME_'. str_replace ('.', '_', $section)]);

			if (array_key_exists ('depends', $pack))
			{
				$depends = explode (',', $pack ['depends']);

				foreach ($depends as $trash => $package)
					if (isset ($props ['_UNIX_NAME_'. str_replace ('.', '_', $package)]))
						$packages [$package] = $props ['_UNIX_NAME_'. str_replace ('.', '_', $package)];
			}

			unset ($_SESSION['SECTION_PROPS_'. $section]);

			$_SESSION['MAPPING_PACKS_'. $section] = $packages;

			$number = 0;
			
			$auxTags = array_keys ($props);
			$auxRepl = array_values ($props);

			array_walk ($auxTags, 'changeTags');

			foreach ($packages as $package => $section)
			{
				//$src = Instance::singleton ()->getReposPath () .'package/'. $package;
				$src = $_SESSION['PACKS_'. $name][$package]['pPackage'];

				$dst = 'instance/'. $name .'/section/'. $section;

				$number += copyDir ($src, $dst);

				replace ($dst, $auxTags, $auxRepl);

				$element = $_SESSION['PACKS_'. $name][$package];

				$element ['name'] = $section;
				$element ['package'] = $package;

				unset ($element ['depends']);
				unset ($element ['help']);
				unset ($element ['property']);

				$_SESSION['SECTIONS_'. $name][$section] = $element;
			}

			return $number;
		}
		catch (Exception $e)
		{
			$message->addWarning ($e->getMessage ());
		}
		catch (PDOException $e)
		{
			$message->addWarning ($e->getMessage ());
		}

		$message->save ();

		return FALSE;
	}

	public function copyUser ($form)
	{
		$message = Message::singleton ();
		
		try 
		{
			if (!isset ($_SESSION['UNIX_NAME']) && isset ($_SESSION['SECTION_PROPS_'. $section]))
				throw new Exception ('Perda de variáveis! Impossível obter nome da instância.');
			
			$name = $_SESSION['UNIX_NAME'];
			
			$src = Instance::singleton ()->getReposPath () .'package/br.ufms.cpcx.user';
			
			$dst = 'instance/'. $name .'/section/'. $form['user_name_unix'];
			
			copyDir ($src, $dst);
			
			$file = 'instance/'. $name .'/configure/business.xml';
			
			if (!file_exists ($file))
				throw new Exception ('O arquivo ['. $file .'] não foi encontrado no caminho especificado.');
		    
		    $user = array (
				'name' => $form['user_name_unix'],
				'label' => $form ['user_title'],
				'description' => $form ['user_description'],
				'component' => '',
				'father' => 'net.ledes.access'
		    );
			
		    switch ($form ['user_type'])
		    {
				case 'public':
					$user['component'] = 'global.userPublic';
					break;

				case 'protected':
					$user['component'] = 'global.userProtected';
					break;
				
				case 'private':
				default:
					$user['component'] = 'global.userPrivate';
					break;
		    }
   
		    $_SESSION['SECTIONS_'. $name][$section] = $user;
		
		    $array = $_SESSION['SECTIONS_'. $name];

		    $xml = new XmlMaker ();

		    $xml->push ('section-mapping', $original ['main']);

		    foreach ($array as $trash => $attributes)
				$xml->emptyElement ('section', $attributes);

		    $xml->pop ();

		    return file_put_contents ($file, $xml->getXml());

		}
		catch (Exception $e)
		{
			$message->addWarning ($e->getMessage ());
		}
		catch (PDOException $e)
		{
			$message->addWarning ($e->getMessage ());
		}

		$message->save ();

		return FALSE;
	}

	public function saveSecurity ($form)
	{
		$message = Message::singleton ();

		try
		{
			if (!isset ($_SESSION['UNIX_NAME']))
				throw new Exception ('Perda de variáveis! Impossível obter nome da instância.');

			$name = $_SESSION['UNIX_NAME'];
  
			if ($form['user_name_unix'] != '')
				$_SESSION['USERS_'. $name][$section] = array(
					'name'  => $form['user_name_unix'],
					'label'   => $form ['user_title'],
					'description'   => $form ['user_description'],
					'type' => $form ['user_type'],
					'form-modify'=>"modify.xml",
					'form-register'=>"register.xml",
				);
 
			$array = $_SESSION['USERS_'. $name];			

			$xml = new XmlMaker ();

			$xml->push ('security-mapping', $original ['main']);

			foreach ($array as $trash => $attributes) 
				$xml->emptyElement ('user-type', $attributes);

			$xml->pop ();

			$file = 'instance/'. $name .'/configure/security.xml';

			return file_put_contents ($file, $xml->getXml());
		}
		catch (Exception $e)
		{
			$message->addWarning ($e->getMessage ());
		}
		catch (PDOException $e)
		{
			$message->addWarning ($e->getMessage ());
		}

		$message->save ();

		return FALSE;
	}	
	
	public function editSecurity ($form)
	{
		$message = Message::singleton ();

		try
		{
			if (!is_array ($form) || !array_key_exists ('name', $form))
				throw new Exception ('Perda de variáveis! Impossível obter nome da seção.');

			$user = $form ['name'];

			if (!isset ($_SESSION['UNIX_NAME']))
				throw new Exception ('Perda de variáveis! Impossível obter nome da instância.');

			$name = $_SESSION['UNIX_NAME'];

			if (!isset ($_SESSION['USERS_'. $name][$user]))
				throw new Exception ('Perda de variáveis! Impossível obter seção.');

			$array = $_SESSION['USERS_'. $name][$user];

			$fields = array ('label', 'description', 'type');

			foreach ($fields as $trash => $field)
				if (array_key_exists ($field, $form))
					$array [$field] = $form [$field];

			$_SESSION['USERS_'. $name][$user] = $array;
			
			$array = $_SESSION['USERS_'. $name];			

			$xml = new XmlMaker ();

			$xml->push ('security-mapping', $original ['main']);

			foreach ($array as $trash => $attributes) 
				$xml->emptyElement ('user-type', $attributes);

			$xml->pop ();

			$file = 'instance/'. $name .'/configure/security.xml';

			return file_put_contents ($file, $xml->getXml());		

		}
		catch (Exception $e)
		{
			$message->addWarning ($e->getMessage ());
		}
		catch (PDOException $e)
		{
			$message->addWarning ($e->getMessage ());
		}

		$message->save ();

		$this->showMessages ();

		return FALSE;
	}

	public function saveBusiness ()
	{
		$message = Message::singleton ();

		try
		{
			if (!isset ($_SESSION['UNIX_NAME']))
				throw new Exception ('Perda de variáveis! Impossível obter nome da instância.');

			$name = $_SESSION['UNIX_NAME'];

			$array = $_SESSION['SECTIONS_'. $name];       

			$xml = new XmlMaker ();

			$xml->push ('section-mapping', $original ['main']);

			foreach ($array as $trash => $attributes) 
				$xml->emptyElement ('section', $attributes);

			$xml->pop ();

			$file = 'instance/'. $name .'/configure/business.xml';

			return file_put_contents ($file, $xml->getXml());
		}
		catch (Exception $e)
		{
			$message->addWarning ($e->getMessage ());
		}
		catch (PDOException $e)
		{
			$message->addWarning ($e->getMessage ());
		}

		$message->save ();

		return FALSE;
	}
	
	public function saveLdap ($form)
	{
		$message = Message::singleton ();

		try
		{
			if (!isset ($_SESSION['UNIX_NAME']))
				throw new Exception ('Perda de variáveis! Impossível obter nome da instância.');

			$name = $_SESSION['UNIX_NAME'];
	
			$_SESSION['LDAP_'. $name][$section] = array (
				'id'  => $form['ldap_id'],
				'host' => $form['ldap_host'],
				'user' => $form['ldap_user'],
				'password' => $form['ldap_password'],
				'dn' => $form['ldap_dn'],
				'ou' => $form['ldap_ou'],
				'gid' => $form['ldap_gid'],
				'update' => $form['ldap_update']
			);
 
			$array = $_SESSION['LDAP_'. $name];
			
			$xml = new XmlMaker ();
			
			$xml->push ('ldap-mapping', $original ['main']);
			
			foreach ($array as $trash => $attributes) 
				$xml->emptyElement ('ldap', $attributes);
			
			$xml->pop ();
			
			$file = 'instance/'. $name .'/configure/ldap.xml';

			return file_put_contents ($file, $xml->getXml());
		}
		catch (Exception $e)
		{
			$message->addWarning ($e->getMessage ());
		}
		catch (PDOException $e)
		{
			$message->addWarning ($e->getMessage ());
		}

		$message->save ();

		return FALSE;
	}
	
	public function getNewSections ($package, $type = 'NAME')
	{
		$message = Message::singleton ();

		try
		{
			if (!isset ($_SESSION['UNIX_NAME']) && isset ($_SESSION['MAPPING_PACKS_'. $package]))
				throw new Exception ('Perda de variáveis! Impossível obter nome da instância.');

			$name = $_SESSION['UNIX_NAME'];

			$sections = $_SESSION['SECTIONS_'. $name];

			$mapping = $_SESSION['MAPPING_PACKS_'. $package];

			$packages = array ();

			foreach ($mapping as $trash => $section)
				$packages [$section] = $sections [$section]['label'];

			if ($type == 'NAME')
				return "'". implode ("', '", array_keys ($packages)) ."'";

			return "'". implode ("', '", array_values ($packages)) ."'";
		}
		catch (Exception $e)
		{
			$message->addWarning ($e->getMessage ());
		}
		catch (PDOException $e)
		{
			$message->addWarning ($e->getMessage ());
		}

		$message->save ();

		return '';
	}

	public function saveSort ($str)
	{
		$message = Message::singleton ();

		$return = TRUE;

		try
		{
			if (!isset ($_SESSION['UNIX_NAME']))
				throw new Exception ('Perda de variáveis! Impossível obter nome da instância.');

			$name = $_SESSION['UNIX_NAME'];

			parse_str ($str, $array);

			$sections = $_SESSION['SECTIONS_'. $name];

			$aux = array ();
			foreach ($array ['sortableList'] as $trash => $id)
				if (array_key_exists ($id, $sections))
					$aux [$id] = $sections [$id];

			if (sizeof ($aux) != sizeof ($sections))
				throw new Exception ('Não é possível efetuar a ordenação pois há falha na integridade dos dados a serem ordenados. Recomendamos que você recarregue a página para tentar consertar o problema. Se isto não resolver tente reiniciar o seu navegador e acessar novamente o sistema.');

			$_SESSION['SECTIONS_'. $name] = $aux;

			$this->saveBusiness ();
		}
		catch (PDOException $e)
		{
			$message->addWarning ($e->getMessage ());

			$return = FALSE;
		}
		catch (Exception $e)
		{
			$message->addWarning ($e->getMessage ());

			$return = FALSE;
		}

		$message->save ();

		$this->showMessages ();

		return $return;
	}

	public function makeSectionDB ($section)
	{
		$message = Message::singleton ();

		try
		{
			if (!isset ($_SESSION['UNIX_NAME']) || !isset ($_SESSION['MAPPING_PACKS_'. $section]))
				throw new Exception ('Perda de variáveis! Impossível obter nome da instância.');

			$name = $_SESSION['UNIX_NAME'];

			$db = $this->connect ($_SESSION['DBH_'. $name]);

			$mapping = $_SESSION['MAPPING_PACKS_'. $section];

			$mapping = array_reverse ($mapping, TRUE);

			foreach ($mapping as $trash => $section)
			{
				try
				{
					$file = 'instance/'. $name .'/section/'. $section .'/db.sql';

					if (!file_exists ($file))
						continue;

					$db->beginTransaction ();

					$db->exec (file_get_contents($file));

					$db->commit ();

					@unlink ($file);
				}
				catch (PDOException $e)
				{
					$db->rollBack ();

					$message->addWarning ($e->getMessage ());
				}
			}

			$this->showMessages ();

			return TRUE;
		}
		catch (PDOException $e)
		{
			$message->addWarning ($e->getMessage ());
		}
		catch (Exception $e)
		{
			$message->addWarning ($e->getMessage ());
		}

		$message->save ();

		return FALSE;
	}

	public function loadSection ($section)
	{
		$message = Message::singleton ();

		try
		{
			if (!isset ($_SESSION['UNIX_NAME']))
				throw new Exception ('Perda de variáveis! Impossível obter nome da instância.');

			$name = $_SESSION['UNIX_NAME'];

			if (!isset ($_SESSION['SECTIONS_'. $name][$section]))
				throw new Exception ('Perda de variáveis! Impossível obter seção.');

			$aux = $_SESSION['SECTIONS_'. $name][$section];

			$fields = array ('name', 'label', 'description', 'father');

			$array = array ();
			foreach ($fields as $trash => $field)
				if (array_key_exists ($field, $aux))
					$array [$field] = $aux [$field];
				else
					$array [$field] = '';

			XOAD_HTML::importForm('form_edit_section', $array);

			return TRUE;
		}
		catch (Exception $e)
		{
			$message->addWarning ($e->getMessage ());
		}

		$message->save ();

		$this->showMessages ();

		return FALSE;
	}
	
	public function loadUser ($user)
	{
		$message = Message::singleton ();

		try
		{
			if (!isset ($_SESSION['UNIX_NAME']))
				throw new Exception ('Perda de variáveis! Impossível obter nome da instância.');

			$name = $_SESSION['UNIX_NAME'];
		
			if (!isset ($_SESSION['USERS_'. $name][$user]))
				throw new Exception ('Perda de variáveis! Impossível obter usuário.');
				
			$aux = $_SESSION['USERS_'. $name][$user];

			$fields = array ('name', 'label', 'description', 'type');

			$array = array ();
			foreach ($fields as $trash => $field)
				if (array_key_exists ($field, $aux))
					$array [$field] = $aux [$field];
				else
					$array [$field] = '';

			XOAD_HTML::importForm ('form_edit_user', $array);

			return TRUE;
		}
		catch (Exception $e)
		{
			$message->addWarning ($e->getMessage ());
		}

		$message->save ();

		$this->showMessages ();

		return FALSE;
	}	

	public function saveSection ($form)
	{
		$message = Message::singleton ();

		try
		{
			if (!is_array ($form) || !array_key_exists ('name', $form))
				throw new Exception ('Perda de variáveis! Impossível obter nome da seção.');

			$section = $form ['name'];

			if (!isset ($_SESSION['UNIX_NAME']))
				throw new Exception ('Perda de variáveis! Impossível obter nome da instância.');

			$name = $_SESSION['UNIX_NAME'];

			if (!isset ($_SESSION['SECTIONS_'. $name][$section]))
				throw new Exception ('Perda de variáveis! Impossível obter seção.');

			$array = $_SESSION['SECTIONS_'. $name][$section];

			$fields = array ('label', 'description', 'father');

			foreach ($fields as $trash => $field)
				if (array_key_exists ($field, $form))
					$array [$field] = $form [$field];

			$_SESSION['SECTIONS_'. $name][$section] = $array;

			return TRUE;
		}
		catch (Exception $e)
		{
			$message->addWarning ($e->getMessage ());
		}

		$message->save ();

		$this->showMessages ();

		return FALSE;
	}

	public function deleteSection ($section)
	{
		$message = Message::singleton ();

		try
		{
			if (!isset ($_SESSION['UNIX_NAME']))
				throw new Exception ('Perda de variáveis! Impossível obter nome da instância.');

			$name = $_SESSION['UNIX_NAME'];

			removeDir ('instance/'. $name .'/section/'. $section);

			unset ($_SESSION['SECTIONS_'. $name][$section]);

			return TRUE;
		}
		catch (Exception $e)
		{
			$message->addWarning ($e->getMessage ());
		}

		$message->save ();

		$this->showMessages ();

		return FALSE;
	}
	
	public function deleteUser ($user)
	{
		$message = Message::singleton ();

		try
		{
			if (!isset ($_SESSION['UNIX_NAME']))
				throw new Exception ('Perda de variáveis! Impossível obter nome da instância.');

			$name = $_SESSION['UNIX_NAME'];

			removeDir ('instance/'. $name .'/section/'. $user);

			unset ($_SESSION['USERS_'. $name][$user]);
						unset ($_SESSION['SECTIONS_'. $name][$user]);

			return TRUE;
		}
		catch (Exception $e)
		{
			$message->addWarning ($e->getMessage ());
		}

		$message->save ();

		$this->showMessages ();

		return FALSE;
	}

	public function delay ()
	{
		sleep (1);
	}

	public function showMessages ()
	{
		$message = Message::singleton ();

		if (!is_object ($message) || !$message->has ())
			return FALSE;

		$str = '';
		while ($msg = $message->get ())
			$str .= $msg;

		$msgs = &XOAD_HTML::getElementById ('labelMessage');

		$msgs->innerHTML = '<div id="idMessage">'. $str .'</div>';

		$message->clear ();

		return TRUE;
	}

	public function xoadGetMeta()
	{
		$methods = get_class_methods ($this);
		
		XOAD_Client::mapMethods ($this, $methods);

		XOAD_Client::publicMethods ($this, $methods);

		XOAD_Client::privateMethods ($this, array ());
	}
}
?>
