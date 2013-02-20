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
// $Id: console.php 70 2010-05-02 07:04:23Z zyxist $

	define('OPT_REQUIRED', 0);
	define('OPT_OPTIONAL', 1);
	
	define('TYPE_STRING', 0);
	define('TYPE_INTEGER', 1);
	define('TYPE_PATH', 2);

	class tfStream
	{
		private $stream;
		private $nl;
		
		public function __construct($stream)
		{
			$this->stream = $stream;
			if(!is_resource($this->stream))
			{
				throw new Exception('Stream exception: an attempt to initialize an empty stream.');
			}
		} // end __construct();
		
		public function __destruct()
		{
			fclose($this->stream);
		} // end __destruct();
		
		public function write($text)
		{
			fwrite($this->stream, $text);
		} // end write();
		
		public function writeln($text)
		{			
			fwrite($this->stream, $text.PHP_EOL);
		} // end writeln();
		
		public function center($text, $length)
		{
			$tl = strlen($text);
			if($tl > $length)
			{
				fwrite($this->stream, $text.PHP_EOL);
			}
			else
			{
				fwrite($this->stream, str_repeat(' ', floor($length/2) - floor($tl/2)).$text.PHP_EOL);
			}
		} // end center();
		
		public function space()
		{
			fwrite($this->stream, PHP_EOL);
		} // end space();
		
		public function writeHr($type = '-', $repeat=30)
		{
			fwrite($this->stream, str_repeat($type, $repeat).PHP_EOL);
		} // end writeHr();
		
		public function read($length = 80)
		{
			return fread($this->stream, $length);
		} // end read();	
	} // end tfStream;

	class tfConsole
	{
		private $args;
		public $stdin;
		public $stdout;
		public $stderr;
		public $os;

		public function __construct()
		{
			$this->detectOS();
			
			$this->stdin = new tfStream(STDIN);
			$this->stdout = new tfStream(STDOUT);
			$this->stderr = new tfStream(STDERR);
			
			$this->args = $_SERVER['argv'];
		} // end __construct();
		
		public function testArgNum($from, $to = null)
		{
			$size = sizeof($this->args) - 1;
			if(is_null($to))
			{
				return $size == $from;
			}
			else
			{
				return ($size >= $from) && ($size <= $to);
			}
		} // end testArgNum();
		
		public function testArgs(&$list)
		{
			$i = 1;
			foreach($list as $name => &$item)
			{
				if($name[0] == '#')
				{
					if(isset($this->args[$i]) && $this->testValue($this->args[$i], $item[1]))
					{
						$item = $this->args[$i];
						$i++;
					}
					else
					{
						if($item[0] == OPT_OPTIONAL)
						{
							$item = '';
							continue;
						}
						throw new Exception('Invalid argument #'.$i.': '.$name.'.');
					}
				}
				elseif($name[0] == '-')
				{
					if(($j = array_search($name, $this->args)) !== false)
					{
						$j++;
						if($this->testValue($this->args[$j], $item[1]))
						{
							$item = $this->args[$j];
							$i++;
						}
						else
						{
							if($item[0] == OPT_OPTIONAL)
							{
								unset($list[$name]);
								continue;
							}
							throw new Exception('Invalid argument #'.$j.': '.$name.'.');
						}
					}
					elseif($item[0] == OPT_OPTIONAL)
					{
						unset($list[$name]);
						continue;
					}
					else
					{
						throw new Exception('Invalid argument #'.$i.': '.$name.'.');
					}
				}
			}
		} // end testArgs();
		
		private function testValue(&$value, $type)
		{
			switch($type)
			{
				case TYPE_STRING:
					return true;
				case TYPE_INTEGER:
					return ctype_digit($value);	
				case TYPE_PATH:
					$path = realpath($value);
					if($path !== false)
					{
						$value = $path;
					}					
					return true;
			}
		} // end testValue();
		
		public function detectOS()
		{
			$this->os = php_uname('s');
			$explode = explode(' ', $this->os);
			$this->os = $explode[0];
			define('USED_OS', $this->os);
		} // end detectOS();
	} // end tfConsole;
	
	class tfProgram
	{
		/**
		 * The system console.
		 * @var tfConsole
		 */
		public $console;
		public $outputs;
		public $fs;
		protected $app;
		
		static private $instance;
		
		private function __construct()
		{
			date_default_timezone_set('Europe/London');
			$this->console = new tfConsole;
			
			// This is the master filesystem
			$this->fs = new tfFilesystem;
			$this->fs->setMasterDirectory(TF_DIR, TF_READ | TF_EXEC);
		} // end __construct();
		
		static public function get()
		{
			if(is_null(tfProgram::$instance))
			{
				tfProgram::$instance = new tfProgram;
			}
			return tfProgram::$instance;
		} // end get();
		
		final public function loadModule($module)
		{
			if(!file_exists(TF_DIR.$module.'.php'))
			{
				$this->console->stderr->writeln('Specified module has not been found: '.$module);
				die();
			}
			require_once(TF_DIR.$module.'.php');
			$className = 'tf'.ucfirst($module);
			
			if(!class_exists($className))
			{
				$this->console->stderr->writeln('Error while loading a module: '.$module);
				die();
			}
			$this->app = new $className;
		} // end load();
		
		final public function loadLibrary($name)
		{
			require_once(TF_DIR.'includes/'.$name.'.php');
		} // end loadLibrary();
		
		final public function run()
		{
			try
			{
				$this->app->parseArgs($this);
				$a = $this->app->action;
			
				if(!method_exists($this->app, $a))
				{
					$this->app->main($this);
				}
				else
				{
					$this->app->$a($this);
				}
			}
			catch(Exception $e)
			{
				fwrite(STDERR, "\nAn exception occured during the execution: \n".$e->getMessage()."\n");
				die();			
			}
		} // end run();
	} // end tfProgram;
	
	abstract class tfApplication
	{
		public $action;
		
		abstract public function parseArgs(tfProgram $prg);
		abstract public function main(tfProgram $prg);
	} // end tfApplication;
