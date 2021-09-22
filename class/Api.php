<?php
/**
 * Implements the REST-Like API bus.
 *
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @category class
 * @package core
 * @subpackage api
 * @copyright 2005-2017 Titan Framework
 * @license http://www.titanframework.com/license/ BSD License (3 Clause)
 * @see ApiAuth, ApiEntity, ApiException, ApiList
 * @link http://www.titanframework.com/docs/api/
 */
class Api
{
	static private $api = FALSE;

	static private $active = NULL;

	private $applications = array ();

	const GET = 'GET';
	const POST = 'POST';
	const PUT = 'PUT';
	const DELETE = 'DELETE';
	const PATCH = 'PATCH';

	private final function __construct ()
	{
		$array = Instance::singleton ()->getApi ();

		if (!array_key_exists ('xml-path', $array))
			throw new Exception ('Not located [xml-path] attribute on &lt;api&gt;&lt;/api&gt; tag in file [configure/titan.xml]!');

		$file = $array ['xml-path'];

		$cacheFile = Instance::singleton ()->getCachePath () .'parsed/'. fileName ($file) .'_'. md5_file ($file) .'.php';

		if (file_exists ($cacheFile))
			$array = include $cacheFile;
		else
		{
			$xml = new Xml ($file);

			$array = $xml->getArray ();

			$array = $array ['api-mapping'][0];

			xmlCache ($cacheFile, $array);
		}

		if (array_key_exists ('application', $array) && is_array ($array ['application']))
			foreach ($array ['application'] as $trash => $app)
			{
				if (!array_key_exists ('name', $app) || trim ($app ['name']) == '' ||
					!array_key_exists ('protocol', $app) || trim ($app ['protocol']) == '')
					continue;

				$this->applications [trim ($app ['name'])] = self::factory (trim ($app ['protocol']), $app);
			}
	}

	static public function singleton ()
	{
		if (self::$api !== FALSE)
			return self::$api;

		$class = __CLASS__;

		self::$api = new $class ();

		return self::$api;
	}

	static public function factory ($drive, $array)
	{
		if (!file_exists (Instance::singleton ()->getReposPath () .'auth/'. $drive .'.php'))
			return NULL;

		require_once Instance::singleton ()->getReposPath () .'auth/'. $drive .'.php';

		$class = $drive .'Auth';

		if (!class_exists ($class, FALSE))
			return NULL;

		return new $class ($array);
	}

	public function applicationExists ($app)
	{
		return array_key_exists ($app, $this->applications);
	}

	public static function isActive ()
	{
		if (is_null (self::$active))
			self::$active = sizeof (Instance::singleton ()->getApi ());

		return self::$active;
	}

	public function getActiveApp ()
	{
		foreach ($this->applications as $name => $app)
			if ($app->isActive ())
			{
				$app->load ();

				return $app;
			}

		return NULL;
	}

	static public function getHttpRequestMethod ()
	{
		return $_SERVER['REQUEST_METHOD'];
	}

	public function getApp ($name = FALSE)
	{
		if ($name !== FALSE)
		{
			if (!array_key_exists ($name, $this->applications))
				return NULL;

			return $this->applications [$name];
		}

		$app = each ($this->applications);

		if ($app === FALSE)
		{
			reset ($this->applications);

			return NULL;
		}

		return $app ['value'];
	}

	public static function decrypt ($input, $key)
	{
		$key = substr ($key, 0, 16);

		return Blowfish::decrypt (
			base64_decode (trim ($input)),
			$key,
			Blowfish::BLOWFISH_MODE_EBC,
			Blowfish::BLOWFISH_PADDING_NONE,
			NULL
		);
	}

	public static function encrypt ($input, $key)
	{
		$key = substr ($key, 0, 16);

		return base64_encode (Blowfish::encrypt (
			$input,
			$key,
			Blowfish::BLOWFISH_MODE_EBC,
			Blowfish::BLOWFISH_PADDING_NONE,
			NULL
		));
	}

	public static function code ($code)
	{
		return preg_replace ('/[^0-9\.]/i', '', $code);
	}
}
