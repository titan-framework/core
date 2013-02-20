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
// $Id: parsers.php 68 2010-01-16 11:11:50Z extremo $

	class tfParsers
	{
		static private $instance;
		
		protected $_parser = NULL;
		
		private function __construct()
		{
			$this->_parser = new MarkdownDocs_Parser;
		} // end __construct();
		
		static public function get()
		{
			if(is_null(tfParsers::$instance))
			{
				tfParsers::$instance = new tfParsers;
			}
			return tfParsers::$instance;
		} // end get();
		
		public function getParser()
		{
			return $this->_parser;
		} // end getParser();
		
		public function tfdoc($filename)
		{
			if(!file_exists($filename))
			{
				throw new SystemException('tfdoc: "'.$filename.'" - file not found');
			}

			$f = fopen($filename, 'r');
			
			$data = array('Tags' => array());
			
			// Part 1 - parsing titles
			$ok = true;
			do
			{
				if($ok)
				{
					$line = trim(fgets($f));
				}
				if(preg_match('/^([a-zA-Z\-]+)\:\\s*$/', $line, $found))
				{
					$hash = $found[1];
					$data['Tags'][$hash] = array();
					$line = trim(fgets($f));
					while(preg_match('/^[ ]?\- (.+)$/', $line, $found))
					{
						$data['Tags'][$hash][] = $found[1];
						$line = trim(fgets($f));
					}
					$ok = false;
					continue;
				}
				elseif(preg_match('/^([a-zA-Z\-]+)\:( )?(.+)$/', $line, $found))
				{
					$data['Tags'][$found[1]] = trim($found[3]);
					$ok = true;
				}
				else
				{
					if(strlen($line) > 0 && !$this->separator($line))
					{
						throw new SystemException('Error in parsing "'.basename($filename).'": unexpected line:'.PHP_EOL.$line); 
					}
				}
				$ok = true;
			}
			while(!$this->separator($line));
			$data['Content'] = '';
			while(!feof($f))
			{
				$data['Content'] .= fread($f, 8192);
			}
			fclose($f);
			return $data;
		} // end tfdoc();

		public function parse($text)
		{
			return $this->_parser->transform($text);
		} // end parse();
		
		public function config($filename)
		{
			$items = parse_ini_file($filename, true);
			
			if(!is_array($items))
			{
				throw new SystemException('The specified file: "'.$filename.'" is not a valid configuration file.');
			}
			return $items;
		} // end config();

		private function separator($text)
		{
			return preg_match('/^[\-\=\*]{3,}$/', trim($text));
		} // end separator();

	} // end tfParsers;

