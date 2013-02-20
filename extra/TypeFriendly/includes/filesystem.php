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
// $Id: filesystem.php 68 2010-01-16 11:11:50Z extremo $

define('TF_READ', 4);
define('TF_WRITE', 2);
define('TF_EXEC', 1);

class tfFilesystem
{
	private $master;
	public $ignoreHidden = true;

	public function setMasterDirectory($dir, $flags)
	{
		$n = strlen($dir)-1;
		if($n < 1)
		{
			return false;
		}
		
		if($dir[$n] != '/')
		{
			$dir .= '/';
		}

		if(!$this->checkFlags($dir, $flags))
		{
			return false;
		}
		$this->master = $dir;
		return true;
	} // end setMasterDirectory();

	public function get($name)
	{
		$name = str_replace('../', '', $name);
		if(!file_exists($this->master.$name))
		{
			throw new SystemException('The file "'.$this->master.$name.'" is not accessible.');
		}

		return $this->master.$name;
	} // end get();

	public function read($name)
	{
		$name = str_replace('../', '', $name);
		if(!file_exists($this->master.$name))
		{
			throw new SystemException('The file "'.$this->master.$name.'" is not accessible.');
		}

		return file_get_contents($this->master.$name);
	} // end read();

	public function readAsArray($name)
	{
		$name = str_replace('../', '', $name);
		if(!file_exists($this->master.$name))
		{
			throw new SystemException('The file "'.$this->master.$name.'" is not accessible.');
		}

		$data = file($this->master.$name);
		foreach($data as &$item)
		{
			$item = trim($item);
		}
		return $data;
	} // end readAsArray();

	public function loadObject($name, $object)
	{
		$name = str_replace('../', '', $name);
		if(!file_exists($this->master.$name))
		{
			throw new SystemException('The file "'.$this->master.$name.'" is not accessible.');
		}
		require($this->master.$name);
		if(!class_exists($object))
		{
			throw new SystemException('The file "'.$this->master.$name.'" does not contain the required class: '.$object);
		}
		return new $object;
	} // end loadObject();

	public function write($name, $content)
	{
		return file_put_contents($this->master.str_replace('../', '', $name), $content);
	} // end write();

	public function containsItems($directory)
	{
		$dir = opendir($this->master.$directory);
		while($f = readdir($dir))
		{
			if($f != '.' && $f != '..')
			{
				closedir($dir);
				return true;
			}
		}
		closedir($dir);
		return false;
	} // end containsItems();

	public function checkDirectories($list)
	{
		$err = false;
		$errors = array();
		foreach($list as $name => $param)
		{
			if(!is_dir($this->master.$name))
			{
				$errors[$name] = 'Not a directory.';
				$err = true;
				continue;
			}
			if($param & TF_READ)
			{
				if(!is_readable($this->master.$name))
				{
					$errors[$name] = 'Not readable';
					$err = true;
				}
			}
			if($param & TF_WRITE)
			{
				if(!is_writeable($this->master.$name))
				{
					$errors[$name] = 'Not writeable';
					$err = true;
				}
			}
			if($param & TF_EXEC)
			{
				if(!is_executable($this->master.$name))
				{
					$errors[$name] = 'Not executable';
					$err = true;
				}
			}
		}
		if($err)
		{
			return $errors;
		}
		return true;
	} // end checkDirectories();

	public function listDirectory($directory, $files = true, $directories = false)
	{
		$dir = @opendir($this->master.$directory);
		if(!is_resource($dir))
		{
			throw new SystemException('Cannot open directory: '.$directory);
		}
		$list = array();
		while($f = readdir($dir))
		{
			if($f == '.' || $f == '..')
			{
				continue;
			}

			if($this->ignoreHidden && $f[0] == '.')
			{
				continue;
			}
			if($files && is_file($this->master.$directory.$f))
			{
				$list[] = $f;
			}
			elseif($directories && is_dir($this->master.$directory.$f))
			{
				$list[] = $f;
			}
		}
		closedir($dir);
		return $list;
	} // end listDirectory();

	public function safeMkdir($directory, $access)
	{
		if(!is_dir($this->master.$directory))
		{
			mkdir($this->master.$directory);
		}
		$what = '';
		if($access & TF_READ)
		{
			if(!is_readable($this->master.$directory))
			{
				$what .= 'r';
			}
		}
		if($access & TF_WRITE)
		{
			if(!is_writeable($this->master.$directory))
			{
				$what .= 'w';
			}
		}
		if($access & TF_EXEC)
		{
			if(!is_executable($this->master.$directory))
			{
				$what .= 'x';
			}
		}
		if(USED_OS != 'Windows' && strlen($what) > 0)
		{
			system('chmod u+'.$what.' "'.$this->master.$directory.'"');
		}
	} // end safeMkdir();

	public function cleanUpDirectory($directory)
	{
		if(!is_dir($this->master.$directory))
		{
			return false;
		}

		$this->_cleanUpDirectory($this->master.$directory, false);

	} // end cleanUpDirectory();

	protected function _cleanUpDirectory($directory, $deleteSelf = true)
	{

		$dir = @opendir($directory);
		if(!is_resource($dir))
		{
			return false;
		}

		if(!is_writeable($directory) && USED_OS != 'Windows')
		{
			system('chmod u+w "'.$directory.'"');
		}

		while($f = readdir($dir))
		{
			if($f == '.' || $f == '..')
			{
				continue;
			}

			if(is_file($directory.'/'.$f))
			{
				unlink($directory.'/'.$f);
			}
			if(is_dir($directory.'/'.$f))
			{
				$this->_cleanUpDirectory($directory.'/'.$f);
			}
		}
		closedir($dir);
		if($deleteSelf)
		{
			rmdir($directory);
		}
	} // end _cleanUpDirectory();

	public function copyItem($from, $to)
	{
		if(is_file($this->master.$from))
		{
			copy($this->master.$from, $this->master.$to);
		}
		elseif(is_dir($this->master.$from))
		{
			$this->safeMkdir($to, TF_WRITE);
			$this->recursiveCopy($this->master.$from, $this->master.$to);
		}
		else
		{
			throw new SystemException('The directory "'.$this->master.$from.'" does not exist.');
		}
	} // end copyItem();

	public function copyFromVFS(tfFilesystem $sys, $from, $to)
	{
		if(is_file($sys->master.$from))
		{
			copy($sys->master.$from, $this->master.$to);
		}
		else
		{
			$this->safeMkdir($to, TF_WRITE);

			if(!is_dir($sys->master.$from))
			{
				throw new SystemException('The directory "'.$sys->master.$from.'" does not exist.');
			}

			$this->recursiveCopy($sys->master.$from, $this->master.$to);
		}
	} // end copyFromVFS();

	public function getModificationTime($directory)
	{
		$dir = @opendir($this->master.$directory);
		if(!is_resource($dir))
		{
			throw new SystemException('Cannot open directory: '.$directory);
		}

		$list = array();
		while($f = readdir($dir))
		{
			if($f != '.' && $f != '..')
			{
				if(is_file($this->master.$directory.$f))
				{
					$list[$f] = filemtime($this->master.$directory.$f);
				}
			}
		}
		closedir($dir);
		return $list;
	} // end getModificationTime();

	private function recursiveCopy($source, $dest)
	{
		$dir = opendir($source);
		while($f = readdir($dir))
		{
			if($f != '.' && $f != '..')
			{
				if($this->ignoreHidden && $f[0] == '.')
				{
					continue;
				}
				if(is_file($source.$f))
				{
					copy($source.$f, $dest.$f);
				}
				else
				{

					if(!is_dir($dest.$f))
					{
						mkdir($dest.$f);
					}
					$this->recursiveCopy($source.$f.'/', $dest.$f.'/');
				}
			}
		}
		closedir($dir);
	} // end recursiveCopy();

	private function checkFlags($directory, $access)
	{
		if($access & TF_READ)
		{
			if(!is_readable($directory))
			{
				return false;
			}
		}
		if($access & TF_WRITE)
		{
			if(!is_writeable($directory))
			{
				return false;
			}
		}
		if($access & TF_EXEC && USED_OS != 'Windows')
		{
			if(!is_executable($directory))
			{
				return false;
			}
		}
		return true;
	} // end checkFlags();
} // end tfFilesystem;
