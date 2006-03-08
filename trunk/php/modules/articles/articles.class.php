<?php
/**
 * @package ComaCMS
 * @subpackage News
 * @copyright (C) 2005-2006 The ComaCMS-Teams
 */
 #----------------------------------------------------------------------#
 # file			: news.class.php				#
 # created		: 2006-02-18					#
 # copyright		: (C) 2005-2006 The ComaCMS-Team		#
 # email		: comacms@williblau.de				#
 #----------------------------------------------------------------------#
 # This program is free software; you can redistribute it and/or modify	#
 # it under the terms of the GNU General Public License as published by	#
 # the Free Software Foundation; either version 2 of the License, or	#
 # (at your option) any later version.					#
 #----------------------------------------------------------------------#
 
 
 	class Articles {
 		var $_SqlConnection;
 		var $_ComaLib;
 		var $_User;
 		var $_Config;
 		
 		function Articles(&$SqlConnection, &$ComaLib, &$User, &$Config) {
 			$this->_SqlConnection = &$SqlConnection;
 			$this->_ComaLib = &$ComaLib;
 			$this->_User = &$User;
 			$this->_Config = &$Config;
 		}
 		
 	}
?>