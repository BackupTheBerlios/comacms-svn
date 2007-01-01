<?php
/**
 * @package ComaCMS
 * @subpackage Page
 * @copyright (C) 2005-2007 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : page.php
 # created              : 2006-12-30
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
	 * @subpackage Page
  	 */
 	class Page {
 		
 		/**
 		 * @access private
 		 * @var Sql
 		 */
 		var $_SqlConnection;
 		
 		/**
 		 * @access private
 		 * @var Config
 		 */
 		var $_Config;
 		
 		/**
 		 * @access private
 		 * @var Language
 		 */
 		var $_Translation;
 		
 		/**
 		 * @access private
 		 * @var ComaLate
 		 */
 		var $_ComaLate;
 		
 		/**
 		 * @access public
 		 * @var string
 		 */
 		var $HTML = '';
 		
 		function Page(&$SqlConnection, &$Config, &$Translation, &$ComaLate) {
 			$this->_SqlConnection = &$SqlConnection;
 			$this->_Config = &$Config;
 			$this->_Translation = &$Translation;
 			$this->_ComaLate = &$ComaLate;
 		}
 		
 		function LoadPage($PageID) {
 			return false;
 		}
 	}
?>
