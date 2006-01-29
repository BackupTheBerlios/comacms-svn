<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: menu.php					#
 # created		: 2005-01-28					#
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
	
 	class Menu {
 		var $_SqlConnection;
 		
 		function Menu($SqlConnection) {
 			$this->_SqlConnection = $SqlConnection;
 		}
 	}
?>
