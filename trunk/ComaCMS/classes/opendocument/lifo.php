<?php
/**
 * @package ComaCMS
 * @subpackage Opendocument Importer
 * @copyright (C) 2005-2007 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : lifo.php
 # created              : 2007-01-24
 # copyright            : (C) 2005-2007 The ComaCMS-Team
 # email                : comacms@williblau.de
 #----------------------------------------------------------------------
 # This program is free software; you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation; either version 2 of the License, or
 # (at your option) any later version.
 #----------------------------------------------------------------------
	 /**
	  * LIFO
	  * A Last In First Out String management
	  * @package ComaCMS
	  * @subpackage Opendocument Importer
	  */
	 class LIFO {
		/**
		 * @access private
		 */
		var $_Data = array();
		
		/** Add
		 * @access public
		 * @param string $Data
		 * @return void
		 */
		function Add($Data) {
			$this->_Data[] = $Data;
		}
		
		/** Get
		 * Removes the item which was added at last
		 * @access public
		 * @return string
		 */
		function Get() {
			return array_pop($this->_Data);
		}
	}
?>
