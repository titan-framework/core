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
// $Id: typefriendly.php 68 2010-01-16 11:11:50Z extremo $

	define('TF_DIR', dirname(__FILE__).'/');
	define('TF_INCLUDES', TF_DIR.'includes/');
	define('TF_VENDOR', TF_DIR.'vendor/');
	define('TF_OUTPUTS', TF_DIR.'outputs/');
	define('TF_TPL', TF_DIR.'templates/');
	require_once(TF_INCLUDES.'console.php');
	require_once(TF_INCLUDES.'resources.php');
	require_once(TF_INCLUDES.'tags.php');
	require_once(TF_INCLUDES.'filesystem.php');
	require_once(TF_INCLUDES.'markdown.php');
	
	if(version_compare(phpversion(), '5.3.0-dev', '<'))
	{
		require_once(TF_INCLUDES.'php52.php');
	}

	$app = tfProgram::get();
	$app->loadModule('main');
	$app->run();
