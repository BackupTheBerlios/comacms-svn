<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2007 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : filename.php
 # created              : 2006-12-17
 # copyright            : (C) 2005-2007 The ComaCMS-Team
 # email                : comacms@williblau.de
 #----------------------------------------------------------------------
 # This program is free software; you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation; either version 2 of the License, or
 # (at your option) any later version.
 #----------------------------------------------------------------------
 
 	/**
 	 * @package ComaCMS
 	 */
 	class PagePreview {
 		
 		/**
 		 * A config link
 		 * @access private
 		 * @var Config A config link
 		 */
 		var $_Config;
 		
 		/**
 		 * Initializes the PagePreview class
 		 * @param Config Config The config-class for config-requests
 		 * @return class PagePreview functions
 		 */
 		function PagePreview(&$Config) {
 			$this->_Config = &$Config;
 		}
 		
 		/**
 		 * Saves a choosen style to config
 		 * @access public
 		 * @param string Style Name of the coosen style
 		 * @return void
 		 */
 		function SaveStyle($Style) {
 			if(file_exists("./styles/" . $Style . "/config.php")) {
				$config = array();
				include("./styles/" . $Style . "/config.php");
				if (!empty($config['longname']) && file_exists('./styles/' . $Style . '/' . $config['template'])) {
					$this->_Config->Save('style', $Style);
				}
 			}
 			else
 				return;
 		}
 		
 		/**
 		 * Gets all existing styles into an array
 		 * @param string StyleFolder The folder to search in for styles
 		 * @param string Style The actual style
 		 * @return array All styles in that directory
 		 */
 		function GetStyles($StyleFolder, $Style) {
			
			if (empty($StyleFolder))
				$StyleFolder = __ROOT__ . '/styles/';
 		 	// Get all styles 			
 			$styles = array();
			
			// read the available styles
			$folder = dir($StyleFolder);
			while($entry = $folder->read()) {
				// check if the style really exists
				if($entry != "." && $entry != ".." && file_exists($StyleFolder . $entry . "/config.php")) {
					$config = array();
					include($StyleFolder . $entry . "/config.php");
					// mark the selected style as selected in the list
					if($entry == $Style)
						$styles[] = array('ENTRY_VALUE' => $entry,
											'ENTRY_SELECTED' => ' selected="selected"',
											'ENTRY_LONGNAME' => $config['longname']); 
					else
						$styles[] = array('ENTRY_VALUE' => $entry,
											'ENTRY_SELECTED' => '',
											'ENTRY_LONGNAME' => $config['longname']);
				}
			}
			$folder->close();
			
			return $styles;
 		}
 	}
?>