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
// $Id: output.php 68 2010-01-16 11:11:50Z extremo $

	abstract class standardOutput
	{
		protected $project;
		protected $path;
	
		public function init($project, $path)
		{
			$this->project = $project;
			$this->path = $path;
		} // end init();
	
		abstract public function generate($page);

		public function close()
		{
			/*
			 * null
			 */
		} // end close();
		
		abstract public function toAddress($page);
	} // end standardOutput();
