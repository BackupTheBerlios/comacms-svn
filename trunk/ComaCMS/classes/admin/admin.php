<?php
/**
 * @package ComaCMS
 * @subpackage AdminInterface
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: admin.php					#
 # created		: 2006-01-29					#
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
 	class Admin {
 	
 		/**
 		 * @var Sql
 		 * @access private
 		 */
 		var $_SqlConnection;
 		
 		/**
 		 * @var array
 		 * @access private
 		 */
 		var $_AdminLang;
 		
 		/**
 		 * Returns a page which is selected by <var>$Action</var>
 		 * @return string
 		 * @param string Action
 		 */
		function GetPage($Action = '') {
			return '';
		}
 	}
?>