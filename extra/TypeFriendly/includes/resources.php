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
// $Id: resources.php 68 2010-01-16 11:11:50Z extremo $

	class tfResources
	{
		public $outputs;
		
		private $pr;
		
		static private $instance;
		
		static public function get()
		{
			if(is_null(tfResources::$instance))
			{
				tfResources::$instance = new tfResources;
			}
			return tfResources::$instance;
		} // end get();
		
		private function __construct()
		{
			$app = tfProgram::get();
			
			// Load outputs
			$list = $app->fs->listDirectory('outputs/', true, false);
			foreach($list as &$item)
			{
				if(strpos($item, '.php') !== false)
				{
					$item = substr($item, 0, strlen($item) - 4);
				}
			}
			$this->outputs = $list;
		} // end __construct();
	} // end tfResources;
