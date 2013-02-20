<?php
/*
  --------------------------------------------------------------------
                           TypeFriendly
              Copyright (c) 2008-2010 Invenzzia Team
                    http://www.invenzzia.org/
                See README for more author details
  --------------------------------------------------------------------
  This file is part of TypeFriendly.
                                                                   
  TypeFriendly is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  TypeFriendly is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with TypeFriendly. If not, see <http://www.gnu.org/licenses/>.
*/
// $Id: i18n.php 68 2010-01-16 11:11:50Z extremo $

	class tfTranslate
	{
		private $_baseData;
		private $_usedData;
		
		private $_baseLang;
		private $_usedLang;
		
		private $fs;	// filesystem
		private $parsers;
		
		static private $instance;
		
		private function __construct()
		{
			$program = tfProgram::get();
			$this->parsers = tfParsers::get();
			$this->fs = $program->fs;			
		} // end __construct();

		static public function get()
		{
			if(is_null(tfTranslate::$instance))
			{
				tfTranslate::$instance = new tfTranslate;
			}
			return tfTranslate::$instance;
		} // end get();
		
		public function setBaseLanguage($lang)
		{
			$this->_baseLang = $lang;
		} // end setBaseLanguage();

		public function setLanguage($lang)
		{
			$this->_usedLang = $lang;
		} // end setLanguage();

		public function _($group, $id)
		{
			if(!isset($this->_usedData[$group]))
			{
				$this->tryLoadGroup($group);
			}
			if(!isset($this->_usedData[$group][$id]))
			{
				if($this->_usedLang == $this->_baseLang)
				{
					throw new Exception('The language block $'.$group.'@'.$id.' does not exist.');
				}
				if(!isset($this->_baseData[$group][$id]))
				{
					throw new Exception('The language block $'.$group.'@'.$id.' does not exist.');
				}
				$text = &$this->_baseData[$group][$id];
			}
			else
			{
				$text = &$this->_usedData[$group][$id];
			}
			
			if(func_num_args() > 2)
			{
				$args = func_get_args();
				unset($args[0]);
				unset($args[1]);
				return vsprintf($text, $args);
			}
			return $text;
		} // end _();	

		public function reset()
		{
			$this->_usedData = array();
		} // end reset();

		private function tryLoadGroup($group)
		{
			try
			{
				$this->_usedData[$group] = $this->parsers->config($this->fs->get('languages/'.$this->_usedLang.'/'.$group.'.txt'));
				if($this->_usedLang != $this->_baseLang && !isset($this->_baseData[$group]))
				{
					$this->_baseData[$group] = $this->parsers->config($this->fs->get('languages/'.$this->_baseLang.'/'.$group.'.txt'));			
				}
			}
			catch(SystemException $e)
			{
				throw new Exception('The language group "'.$group.'" cannot be loaded.');
			}
		} // end tryLoadGroup();
	} // end tfTranslate;
