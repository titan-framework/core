<?php
/**
 * Implements automatic send of e-mails using templates.
 *
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @category class
 * @package core
 * @subpackage business
 * @copyright 2005-2017 Titan Framework
 * @license http://www.titanframework.com/license/ BSD License (3 Clause)
 * @see Instance, Section, Action, Business
 */
class Mail
{
	static private $mail = FALSE;

	private $templates = array ();

	private $cacheMails = array ();

	private $tags = array ();

	private $receivers = array ();

	private $types = [ 'register', 'forgot', 'create', 'pin-add', 'pin-del' ];

	private final function __construct ()
	{
		$array = Instance::singleton ()->getMail ();

		if (!array_key_exists ('xml-path', $array))
			throw new Exception ('Não foi encontrada a propriedade [xml-path] na tag &lt;mail&gt;&lt;/mail&gt; do arquivo [configure/titan.xml]!');

		$file = $array ['xml-path'];

		$cacheFile = Instance::singleton ()->getCachePath () .'parsed/'. fileName ($file) .'_'. md5_file ($file) .'.php';

		if (file_exists ($cacheFile))
			$array = include $cacheFile;
		else
		{
			$xml = new Xml ($file);

			$array = $xml->getArray ();

			$array = $array ['mail-mapping'][0];

			xmlCache ($cacheFile, $array);
		}

		foreach ($this->types as $trash => $type)
			if (array_key_exists ($type, $array))
				$this->templates [$type] = $array [$type][0];
	}

	static public function singleton ()
	{
		if (self::$mail !== FALSE)
			return self::$mail;

		$class = __CLASS__;

		self::$mail = new $class ();

		return self::$mail;
	}

	public function getForgot ($property = FALSE)
	{
		if ($property === FALSE)
			return $this->templates ['forgot'];

		if (array_key_exists ($property, $this->templates ['forgot']))
			return $this->templates ['forgot'][$property];

		return '';
	}

	public function getRegister ($property = FALSE)
	{
		if ($property === FALSE)
			return $this->templates ['register'];

		if (array_key_exists ($property, $this->templates ['register']))
			return $this->templates ['register'][$property];

		return '';
	}

	public function getCreate ($property = FALSE)
	{
		if ($property === FALSE)
			return $this->templates ['create'];

		if (array_key_exists ($property, $this->templates ['create']))
			return $this->templates ['create'][$property];

		return '';
	}

	public function getTemplate ($type, $property = FALSE)
	{
		if (!array_key_exists ($type, $this->templates))
			return '';
		
		if ($property === FALSE)
			return $this->templates [$type];

		if (array_key_exists ($property, $this->templates [$type]))
			return $this->templates [$type][$property];

		return '';
	}

	public function clear ()
	{
		$this->tags = array ();
	}

	public function addTag ($name, $value)
	{
		$this->tags [$name] = $value;
	}

	public function addReceiver ($name, $mail)
	{
		$this->receivers [] = array ($name, $mail);

		reset ($this->receivers);
	}

	public function getReceiver ()
	{
		$receiver = each ($this->receivers);

		if ($receiver !== FALSE)
			return $receiver ['value'];

		reset ($this->receivers);

		return NULL;
	}

	public function send ($name, $tags = FALSE)
	{
		$section = Business::singleton ()->getSection (Section::TCURRENT);

		$realName = 'MAIL_'. $section->getName () .'_'. $name;

		$db = Database::singleton ();

		$sql = "SELECT _user._email, _user._name FROM _mail
				LEFT JOIN _user_group ON _user_group._group = _mail._group
				LEFT JOIN _user ON _user._id = _user_group._user
				WHERE _mail._name = '". $realName ."'";

		$sth = $db->prepare ($sql);

		$sth->execute ();

		$receivers = array ();

		while ($obj = $sth->fetch (PDO::FETCH_OBJ))
			$receivers [$obj->_email] = $obj->_name;

		while ($receiver = $this->getReceiver ())
			$receivers [$receiver [1]] = $receiver [0];

		if (!sizeof ($receivers))
			return TRUE;

		if (!array_key_exists ($realName, $this->cacheMails))
		{
			$this->cacheMails = array_merge ($this->cacheMails, self::parseMail ($section->getName ()));

			if (!array_key_exists ($realName, $this->cacheMails))
				throw new Exception ('O arquivo ['. $file .'] não está definindo o e-mail ['. $realName .']! Há inconsistência entre os dados do Banco de Dados e os XMLs de configuração da seção. Isto pode ser facilmente resolvido através do módulo de configuração de e-mails.');
		}

		$mail = $this->cacheMails [$realName];

		$instance = Instance::singleton ();

		if (!is_array ($tags))
			$tags = $this->tags;

		if (!array_key_exists ('_USER_', $tags))
			$flag = TRUE;
		else
			$flag = FALSE;

		if (!array_key_exists ('_SYSTEM_', $tags))
			$tags ['_SYSTEM_'] = html_entity_decode ($instance->getName (), ENT_QUOTES, 'UTF-8');

		$replace = array ();
		foreach ($tags as $key => $tag)
			$replace ['['. $key .']'] = $tag;

		if (array_key_exists ('subject', $mail))
			$subject = strtr ($mail ['subject'], $replace);
		else
			$subject = '['. html_entity_decode ($instance->getName (), ENT_QUOTES, 'UTF-8') .'] E-mail Automático';

		$text = strtr ($mail [0], $replace);

		$headers  = "From: ". html_entity_decode ($instance->getName (), ENT_QUOTES, 'UTF-8') ." <". $instance->getEmail () .">\r\nContent-Type: text/plain; charset=utf-8\r\n";

		if (array_key_exists ('reply-to', $mail))
			$headers .= "Reply-To: ". $mail ['reply-to'];

		foreach ($receivers as $address => $name)
		{
			if ($flag)
			{
				$auxSubject = str_replace ('[_USER_]', $name, $subject);

				$auxText = str_replace ('[_USER_]', $name, $text);

				@mail ($address, '=?utf-8?B?'. base64_encode ($auxSubject) .'?=', $auxText, $headers);
			}
			else
				@mail ($address, '=?utf-8?B?'. base64_encode ($subject) .'?=', $text, $headers);
		}

		return TRUE;
	}

	public static function parseMail ($sectionName)
	{
		$file = 'section/'. $sectionName .'/mail.xml';

		if (!file_exists ($file))
			return array ();

		$xml = new Xml ($file);

		$array = $xml->getArray ();

		if (!isset ($array ['mail-mapping'][0]['mail']))
			throw new Exception ('O arquivo ['. $file .'] possui irregularidade sintática!');

		$array = $array ['mail-mapping'][0]['mail'];

		if (!is_array ($array) || !sizeof ($array))
			throw new Exception ('O arquivo ['. $file .'] não está definindo o conteúdo de nenhum e-mail! Há inconsistência entre os dados do Banco de Dados e os XMLs de configuração da seção. Isto pode ser facilmente resolvido através do módulo de configuração de e-mails.');

		$mails = array ();
		foreach ($array as $trash => $mail)
		{
			if (!array_key_exists ('name', $mail) || !array_key_exists (0, $mail))
				continue;

			$mails ['MAIL_'. $sectionName .'_'. $mail ['name']] = $mail;
		}

		return $mails;
	}
}
