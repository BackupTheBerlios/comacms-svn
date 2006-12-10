<?php
/**
 * @package ComaCMS
 * @subpackage Sitemap
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : sitemap_module.php
 # created              : 2006-12-08
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
	 require_once('classes/module.php');
	 
	/**
	 * @package ComaCMS
	 * @subpackage Sitemap
	 */
	class Sitemap_Module extends Module{
		
		/**
		 * This function initializes the Sitemapmoduleclass
		 * @author ComaWStefan
		 * @access public
		 * @param sqlConnection SqlConnection This is the ComaSqlConnectionclass
		 * @param user User This is the ComaUserclass for the actual user
		 * @param language Lang This is the ComaLanguageArray
		 * @param config Config This is the ComaConfigclass containing all configurations
		 * @param comaLate ComaLate This is the outputclass ComaLate
		 * @param comaLib ComaLib This is the ComaLibClass containing some necessary functions
		 * @return class SitemapModule
		 */
		function Module_Sitemap(&$SqlConnection, &$User, &$Lang, &$Config, &$ComaLate, &$ComaLib) {
 			$this->_SqlConnection = &$SqlConnection;
 			$this->_User = &$User;
 			$this->_Config = &$Config;
 			$this->_Lang = &$Lang;
 			$this->_ComaLate = &$ComaLate;
 			$this->_ComaLib = &$ComaLib;
 		}
 		
 		/**
 		 * This function returns the text of the actual modulpage
 		 * @author ComaWStefan
 		 * @access public
 		 * @param string Action This is the action to tell the modul what to do next
 		 * @return string Textpage of the module to be set into the template
 		 */
 		function GetPage($Action) {
 			$out = '';
 			switch($Action) {
 				default:		$out .= $this->_homePage();
 							break;
 			}
 			return $out;
 		}
 		
 		/**
 		 * This function returns the title of the modul shown in the title of the browse
 		 * @author ComaWStefan
 		 * @access public
 		 * @return string The title of the modul
 		 */
 		function GetTitle() {
 			return 'Sitemap';
 		}
 		
 		/**
 		 * This function returns the text of the home page of the module
 		 * @author ComaWStefan
 		 * @access private
 		 * @return string The text of the modul
 		 */
 		function _homePage() {
 			$out = '';
 			
 			return $out;
 		}
	}