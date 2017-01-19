<?php
/**
 * Simple fake class to use if not exists a real implementation of Ajax on component.
 *
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @category class
 * @package core
 * @subpackage ajax
 * @copyright 2005-2017 Titan Framework
 * @license http://www.titanframework.com/license/ BSD License (3 Clause)
 * @see Instance, Business, Section, Xoad
 */
class Ajax
{
	public function xoadGetMeta()
	{
		XOAD_Client::mapMethods ($this, array ());

		XOAD_Client::publicMethods ($this, array ());

		XOAD_Client::privateMethods ($this, array ());
	}
}
