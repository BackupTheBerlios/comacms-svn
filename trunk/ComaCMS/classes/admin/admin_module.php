<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: admin_module.php				#
 # created		: 2006-03-04					#
 # copyright		: (C) 2005-2006 The ComaCMS-Team		#
 # email		: comacms@williblau.de				#
 #----------------------------------------------------------------------#
 # This program is free software; you can redistribute it and/or modify	#
 # it under the terms of the GNU General Public License as published by	#
 # the Free Software Foundation; either version 2 of the License, or	#
 # (at your option) any later version.					#
 #----------------------------------------------------------------------#
 	/**
	 * @package ComaCMS
	 */
 	class Admin_Module extends Admin{
 		/**
 		 * @access private
 		 * @var Sql
 		 */
 		var $_SqlConnection;
 		
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
 		 * @access private
 		 * @var array
 		 */
 		var $_Lang;
 		
 		/**
 		 * @access private
 		 * @var ComaLate
 		 */
 		var $_ComaLate;
 		
 		/**
 		 * @access private
 		 * @var ComaLib
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
 		function Admin_Module(&$SqlConnection, &$User, &$Lang, &$Config, &$ComaLate, &$ComaLib) {
 			$this->_SqlConnection = &$SqlConnection;
 			$this->_User = &$User;	
 			$this->_Lang = &$Lang;
 			$this->_Config = &$Config;
 			$this->_ComaLate = &$ComaLate;
 			$this->_ComaLib = &$ComaLib;
 		}
 		
 		
 		
 		function GetTitle() {
 			return '';
 		}
 	}
?>