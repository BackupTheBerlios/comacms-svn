<?php
/**
 * @package ComaCMS
 * @subpackage Contact
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : contact.class.php
 # created              : 2006-12-03
 # copyright            : (C) 2005-2006 The ComaCMS-Team
 # email                : comacms@williblau.de
 #----------------------------------------------------------------------
 # This program is free software; you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation; either version 2 of the License, or
 # (at your option) any later version.
 #----------------------------------------------------------------------
	
	/**
	 * @package ComaCMS
	 * @subpackage Contact 
	 */
	class News {

		/**
		 * @access private
		 * @var Sql
		 */
		var $_SqlConnection;
		
		/**
		 * @access private
		 * @var ComaLib
		 */
		var $_ComaLib;
		
		/**
		 * @access private
		 * @var User
		 */
		var $_User;
		
		/**
		 * @access private
		 * @var Config
		 */
		var $_Config;
		
		/**
		 * @param Sql SqlConnection
		 * @param ComaLib ComaLib
		 * @param User User
		 * @param Config Config
		 */
    		function News(&$SqlConnection, &$ComaLib, &$User, &$Config) {
    			$this->_SqlConnection = &$SqlConnection;
    			$this->_ComaLib = &$ComaLib;
    			$this->_User = &$User;
    			$this->_Config = &$Config;

    		}
    		
	}
?>