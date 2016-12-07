<?php

class AnonymousUsageData
{
	private $params;

	const FILE = 'ANONYMOUS';

	const URL = 'http://anonymous.titanframework.com/collect/';

	// For dev:
	// const URL = 'http://192.168.33.11/collect/';

	public function __construct ()
	{
		$this->params = array ();

		$instance = Instance::singleton ();

		$this->params ['name'] = $instance->getName ();
		$this->params ['description'] = $instance->getDescription ();
		$this->params ['email'] = $instance->getEmail ();
		$this->params ['url'] = $instance->getUrl ();
		$this->params ['debug'] = $instance->onDebugMode ();
		$this->params ['timezone'] = $instance->getTimeZone ();
		$this->params ['language'] = implode (',', $instance->getLanguages ());
		$this->params ['author'] = $instance->getAuthor ();

		$this->params ['ip'] = @$_SERVER ['SERVER_ADDR'];
		$this->params ['port'] = @$_SERVER ['SERVER_PORT'];
		$this->params ['software'] = @$_SERVER ['SERVER_SOFTWARE'];
		$this->params ['server'] = @$_SERVER ['SERVER_NAME'];
		$this->params ['admin'] = @$_SERVER ['SERVER_ADMIN'];
		$this->params ['uname'] = php_uname ();
		$this->params ['php'] = phpversion ();
		$this->params ['sapi'] = php_sapi_name ();

		$this->params ['users'] = (int) Database::singleton ()->query ("SELECT COUNT(*) FROM _user WHERE _deleted = B'0'")->fetchColumn ();
		$this->params ['groups'] = (int) Database::singleton ()->query ("SELECT COUNT(*) FROM _group")->fetchColumn ();

		$this->params ['db'] = Database::size ();
		$this->params ['file'] = dirSize (realpath (Archive::singleton ()->getDataPath ()));
		$this->params ['cache'] = dirSize (realpath (Instance::singleton ()->getCachePath ()));

		$this->params ['alert'] = Alert::isActive ();
		$this->params ['mobile'] = MobileDevice::isActive ();
		$this->params ['browser'] = BrowserDevice::isActive ();
		$this->params ['manual'] = Manual::isActive ();
		$this->params ['shopping'] = Shopping::isActive ();
		$this->params ['social'] = Social::isActive ();

		$path = Instance::singleton ()->getCachePath () .'RELEASE';

		$release = array (
			'version' => '',
			'environment' => '',
			'date' => 0,
			'author' => ''
		);

		if (file_exists ($path) && is_readable ($path))
		{
			$file = @parse_ini_file ($path);

			if (is_array ($file))
				foreach ($release as $key => $trash)
					if (array_key_exists ($key, $file))
						$release [$key] = $file [$key];
		}

		$path = 'update'. DIRECTORY_SEPARATOR .'VERSION';

		$version = '';
		if (file_exists ($path) && is_readable ($path))
			$version = trim (file_get_contents ($path, 0, NULL, 0, 16));

		$migration = '';
		if (Database::tableExists ('_version'))
			$migration = (string) Database::singleton ()->query ("SELECT MAX(_version) FROM _version")->fetchColumn ();

		$this->params ['migration'] = $migration;
		$this->params ['version'] = $version .'-'. $release ['version'];
		$this->params ['environment'] = $release ['environment'];
		$this->params ['date'] = (int) $release ['date'];
	}

	static public function alreadySentToday ()
	{
		$file = Instance::singleton ()->getCachePath () . self::FILE;

		if (!file_exists ($file))
			return FALSE;

		$change = filemtime ($file);

		$day = 24 * 60 * 60;

		if ($change + $day < time ())
			return FALSE;

		return TRUE;
	}

	private function register ($json)
	{
		@file_put_contents (Instance::singleton ()->getCachePath () . self::FILE, $json);
	}

	public function send ()
	{
		$json = json_encode ((object) $this->params);

		$this->register ($json);

		set_error_handler ('logPhpError');

		try
		{
			$this->post ($json);
		}
		catch (Exception $e)
		{
			toLog ($e->getMessage ());
		}

		restore_error_handler ();
	}

	static private function post ($json)
	{
		$parts = parse_url (self::URL);

		$s = stream_socket_client ($parts ['host'] .':'. (array_key_exists ('port', $parts) ? $parts ['port'] : 80), $errno, $errstr, 30, STREAM_CLIENT_ASYNC_CONNECT|STREAM_CLIENT_CONNECT);

		if (!$s)
			throw new Exception ("Impossible to send anonymous usage data! ". self::URL ." (". $errstr .") [". $errno ."].");

		$body = 'anonymous='. urlencode ($json);

		$out  = "POST ". $parts ['path'] ." HTTP/1.1\r\n";
		$out .= "Host: ". $parts ['host'] ."\r\n";
		$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$out .= "Content-Length: ". strlen ($body) ."\r\n";
		$out .= "Connection: Close\r\n\r\n";
		$out .= $body;

		fwrite ($s, $out);

		fclose ($s);
	}
}
