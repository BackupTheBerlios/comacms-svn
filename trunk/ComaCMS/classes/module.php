<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : module.php
 # created              : 2006-02-18
 # copyright            : (C) 2005-2006 The ComaCMS-Team
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
	require_once('./classes/admin/admin.php');
	
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
 		 * @var array
 		 * @access private
 		 */
 		var $_Lang;
 		
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
 		 * @param array Lang
 		 * @param Config Config
 		 * @param ComaLate ComaLate
 		 * @param ComaLib ComaLib
 		 */
 		function Module(&$SqlConnection, &$User, &$Lang, &$Config, &$ComaLate, &$ComaLib) {
 			$this->_SqlConnection = &$SqlConnection;
 			$this->_User = &$User;	
 			$this->_Lang = &$Lang;
 			$this->_Config = &$Config;
 			$this->_ComaLate = &$ComaLate;
 			$this->_ComaLib = &$ComaLib;
 		}
 		
 		function UseModule($Parameters) {
 			return '';
 		}
 	}
?>