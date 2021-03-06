<?php
/**
 * @package ComaCMS
 * @subpackage AdminInterface
 * @copyright (C) 2005-2007 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : admin.php
 # created              : 2006-01-29
 # copyright            : (C) 2005-2007 The ComaCMS-Team
 # email                : comacms@williblau.de
 #----------------------------------------------------------------------
 # This program is free software; you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation; either version 2 of the License, or
 # (at your option) any later version.
 #----------------------------------------------------------------------
 
 	/**
 	 * @ignore
 	 */
 	require_once __ROOT__ . '/classes/sql.php';
 	require_once __ROOT__ . '/classes/language.php';
 	require_once __ROOT__ . '/classes/config.php';
 	require_once __ROOT__ . '/classes/account.php';
 	require_once __ROOT__ . '/classes/comalib.php';
 	require_once __ROOT__ . '/lib/comalate/comalate.class.php';
 	
 	
	/**
	 * @package ComaCMS 
	 * @subpackage AdminInterface
	 * @abstract
	 */
 	class Admin {
 	
 		/**
 		 * @var Sql
 		 * @access private
 		 */
 		var $_SqlConnection;
 		
 		/**
 		 * @var Language
 		 * @access private
 		 */
 		var $_Translation;
 		
 		/**
 		 * @var Config
 		 * @access private
 		 */
 		var $_Config;
 		
 		/**
 		 * @var User
 		 * @access private
 		 */
 		var $_User;
 		
 		/**
 		 * @var ComaLib
 		 * @access private
 		 */
 		var $_ComaLib;
 		
 		/**
 		 * @var ComaLate
 		 * @access private
 		 */
 		 var $_ComaLate;
 		
 		/**
 		 * Initializes a subpage of the admininterface
 		 * @access public
 		 * @param Sql &$SqlConnection The connection-class for connecting the database
 		 * @param Language &$Translation The language-class for translations
 		 * @param Config &$Config The config-class for config-requests
 		 * @param Account &$User The user-class, handling the current user
 		 * @param ComaLib &$ComaLib The ComaLib-class containing systemrelated functions
 		 * @param ComaLate &$ComaLate The ComaLate-class to handle
 		 */
 		function Admin(&$SqlConnection, &$Translation, &$Config, &$User, &$ComaLib, &$ComaLate) {
 			$this->_SqlConnection = &$SqlConnection;
 			$this->_Translation = &$Translation;
 			$this->_Config = &$Config;
 			$this->_User = &$User;
 			$this->_ComaLib = &$ComaLib;
 			$this->_ComaLate = &$ComaLate;
			$this->_Init();
 		}
 		
 		/**
 		 * Adds pagespecific initialzation actions
 		 * @access private
 		 */
 		function _Init() {
 			
 		}
 		
 		/**
 		 * Returns the page which is selected by <var>$Action</var>
 		 * @return string
 		 * @param string Action
 		 */
		function GetPage($Action = '') {
			return '';
		}
 	}
?>