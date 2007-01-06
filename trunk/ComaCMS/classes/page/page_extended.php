<?php
/**
 * @package ComaCMS
 * @subpackage Page
 * @copyright (C) 2005-2007 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : page_extended.php
 # created              : 2007-01-03
 # copyright            : (C) 2005-2007 The ComaCMS-Team
 # email                : comacms@williblau.de
 #----------------------------------------------------------------------
 # This program is free software; you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation; either version 2 of the License, or
 # (at your option) any later version.
 #----------------------------------------------------------------------
 	/**
	 * 
	 */
	require_once __ROOT__ . '/classes/page/page.php';
 	/**
 	 * @package ComaCMS
	 * @subpackage Page
 	 */
	class Page_Extended extends Page {
 		
 		/**
 		 * @access private
 		 * @var array
 		 */
 		var $_PagesData;
 		/**
 		 * @access private
 		 */
 		var $HTML;
 		
 		/**
 		 * @return array
 		 */
 		function GetPageData($PageID) {
 			return array();
 		}
 		
 		/**
 		 * @return boolean
 		 */
 		 
 		function LoadPageData($PageID) {
 			return false;
 		}
 		
 		/**
 		 * @return boolean
 		 */
 		function PageExists($PageID) {
 			return false;
 		}
 		
 		/**
 		 * @return string
 		 */
 		function GetEditPage($PageID) {
 			return '';
 		}
 		
 		/**
 		 * @param integer $PageID
 		 * @return string/boolean If this value is an empty string everything was well
 		 */
 		function GetSavePage($PageID) {
 			return false;
 		}
 		
 		/**
 		 * @param ingeger $PageID
 		 * @return boolean
 		 */
 		function LogPage($PageID) {
 			return false;
 		}
	}
?>
