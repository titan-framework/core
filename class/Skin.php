<?php
/**
 * Treatment of visual artifacts, like CSS files and logo.
 *
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @category class
 * @package core
 * @subpackage util
 * @copyright 2005-2017 Titan Framework
 * @license http://www.titanframework.com/license/ BSD License (3 Clause)
 * @see Instance, Section, Action
 */
class Skin
{
	static private $skin = FALSE;

	private $array = array ();

	const URL = '__CSS_URL__';
	const PATH = '__CSS_PATH__';

	private final function __construct ()
	{
		$instance = Instance::singleton ();

		$fromXml = $instance->getSkin ();

		$corePath = $instance->getCorePath ();

		$this->array = array (
			'logo'				=> '',
			'icon'				=> 'titan.php?target=loadFile&amp;file=interface/image/titan.ico',
		  	'mobile-logo'		=> '',
			'image-fault'		=> 'titan.php?target=loadFile&file=interface/image/fault.jpg',
			'icons-folder'		=> 'titan.php?target=loadFile&file=interface/icon/',
			'icons-mime'		=> 'titan.php?target=loadFile&file=interface/file/',
			'icons-menu'		=> 'titan.php?target=loadFile&file=interface/menu/',
			'use-xsl'			=> FALSE,
			'path'				=> '',
			'css-main'			=> Instance::singleton ()->getCorePath () .'interface/css/general.css',
			'css-ie'			=> Instance::singleton ()->getCorePath () .'interface/css/general-ie.css',
			'css-top'			=> Instance::singleton ()->getCorePath () .'interface/css/top.css',
			'css-login'			=> Instance::singleton ()->getCorePath () .'interface/css/logon.css',
			'css-menu'			=> Instance::singleton ()->getCorePath () .'interface/css/menu.css',
			'css-message'		=> Instance::singleton ()->getCorePath () .'interface/css/modalbox.css',
			'css-password'		=> Instance::singleton ()->getCorePath () .'interface/css/password.css',
			'css-boxes'			=> Instance::singleton ()->getCorePath () .'interface/css/dragable-boxes.css',
			'css-bug'			=> Instance::singleton ()->getCorePath () .'interface/css/bug-report.css',
			'css-backup'		=> Instance::singleton ()->getCorePath () .'interface/css/instance-backup.css',
			'css-print'			=> Instance::singleton ()->getCorePath () .'interface/css/print.css',
			'css-firefox'		=> Instance::singleton ()->getCorePath () .'interface/css/firefox.css',
			'css-gallery'		=> Instance::singleton ()->getCorePath () .'interface/css/lightbox.css',
			'css-mobile'		=> Instance::singleton ()->getCorePath () .'interface/css/mobile.css',
			'css-instance-top' 	=> 'titan.php?target=loadFile&file=interface/css/empty.css',
			'css-instance-body' => 'titan.php?target=loadFile&file=interface/css/empty.css'
		);

		foreach ($this->array as $key => $trash)
			if (array_key_exists ($key, $fromXml) && !empty ($fromXml [$key]))
				if (is_bool ($this->array [$key]))
					$this->array [$key] = strtoupper ($fromXml [$key]) == 'TRUE' ? TRUE : FALSE;
				else
					$this->array [$key] = $fromXml [$key];
	}

	static public function singleton ()
	{
		if (self::$skin !== FALSE)
			return self::$skin;

		$class = __CLASS__;

		self::$skin = new $class ();

		return self::$skin;
	}

	public function getLogo ()
	{
		return $this->array ['logo'];
	}

	public function getIcon ()
	{
		return $this->array ['icon'];
	}

	public function getMobileLogo ()
	{
		return $this->array ['mobile-logo'];
	}

	public function getImageFault ()
	{
		return $this->array ['image-fault'];
	}

	public function getCss ($context, $type = NULL)
	{
		switch ($type)
		{
			case self::URL:
				if (!is_array ($context))
					$context = array ($context);

				return 'titan.php?target=packerCss&amp;contexts='. implode (',', $context) .'&amp;v='. VersionHelper::singleton ()->getTitanBuild ();

			case self::PATH:
			default:
				return $this->array ['css-'. $context];
		}
	}

	public function getIconsFolder ()
	{
		return $this->array ['icons-folder'];
	}

	public function getIconsMimeType ()
	{
		return $this->array ['icons-mime'];
	}

	public function getIconsMenu ()
	{
		return $this->array ['icons-menu'];
	}

	public function getTemplate ()
	{
		return $this->array ['xsl'];
	}

	public function getPath ()
	{
		return $this->array ['path'];
	}
}
