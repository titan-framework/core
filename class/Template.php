<?php
/**
 * Simple class to load templates files in view layer of engines.
 *
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @category class
 * @package core
 * @subpackage business
 * @copyright 2005-2017 Titan Framework
 * @license http://www.titanframework.com/license/ BSD License (3 Clause)
 * @see Instance, Section, Action
 */
class Template
{
	public static function import ($template)
	{
		if (file_exists ($template))
			return $template;

		if (file_exists (Instance::singleton ()->getReposPath () .'template/'. $template .'.php'))
			return Instance::singleton ()->getReposPath () .'template/'. $template .'.php';

		throw new Exception ('The files ['. $template .'] and ['. Instance::singleton ()->getReposPath () .'template/'. $template .'.php] has not located.');
	}
}
