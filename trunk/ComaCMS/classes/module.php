<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2007 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : module.php
 # created              : 2006-02-18
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
	require_once __ROOT__ . '/classes/admin/admin.php';
	
	/**
	 * @package ComaCMS
	 */
 	class Module {
 		/**
 		 * @var Sql
 		 * @access private
 		 */
 		var $_SqlConnection;
 		
 		/**
 		 * @var User
 		 * @access private
 		 */
 		var $_User;
 		
 		/**
 		 * @var Config
 		 * @access private
 		 */
 		var $_Config;
 		
 		/**
 		 * @var Language
 		 * @access private
 		 */
 		var $_Translation;
 		
 		/**
 		 * @var ComaLate
 		 * @access private
 		 */
 		var $_ComaLate;
 		
 		/**
 		 * @var ComaLib
 		 * @access private
 		 */
 		var $_ComaLib;
 		
 		/**
 		 * @param Sql SqlConnection
 		 * @param User User
 		 * @param Language Translation
 		 * @param Config Config
 		 * @param ComaLate ComaLate
 		 * @param ComaLib ComaLib
 		 */
 		function Module(&$SqlConnection, &$User, &$Translation, &$Config, &$ComaLate, &$ComaLib) {
 			$this->_SqlConnection = &$SqlConnection;
 			$this->_User = &$User;	
 			$this->_Translation = &$Translation;
 			$this->_Config = &$Config;
 			$this->_ComaLate = &$ComaLate;
 			$this->_ComaLib = &$ComaLib;
 			$this->_Init();
 		}
 		
 		/**
 		 * @access private
 		 */
 		function _Init() {
 		
 		}
 		
 		function UseModule($Parameters) {
 			return '';
 		}
 		
 		function GetPage($Action) {
 			return '';
 		}
 		
 		function GetTitle() {
 			return '';
 		}
 	}
?>