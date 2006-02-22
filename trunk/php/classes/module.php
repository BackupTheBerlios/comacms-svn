<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: module.php					#
 # created		: 2006-02-18					#
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
 	class Module {
 		var $_SqlConnection;
 		var $_User;
 		var $_Config;
 		var $_Lang;
 		var $_ComaLate;
 		var $_ComaLib;
 		
 		function Module(&$SqlConnection, &$User, &$Lang, &$Config, &$ComaLate, &$ComaLib) {
 			$this->_SqlConnection = &$SqlConnection;
 			$this->_User = &$User;	
 			$this->_Lang = &$Lang;
 			$this->_Config = &$Config;
 			$this->_ComaLate = &$ComaLate;
 			$this->_ComaLib = &$ComaLib;
 		}
 		
 		function UseModule($Parameters) {
 			
 		}
 	}
?>