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
	class Module_Sitemap extends Module{
		
		/**
		 * This are all existing pages
		 * @access private
		 */
		 var $_PagesParentIDs;
		 
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
 		 * This function returns the text for a template or a page
 		 * @author ComaWStefan
 		 * @access public
 		 * @param string Identifer
 		 * @param array Parameters Includes all Parameters for the modul
 		 * @return string Text for the template or page
 		 */
 		function UseModule($Identifer, $Parameters) {
 			$TopNode = 0;
 			// Split all parameters from parameter=value&parameter2=value2
 			$Parameters = explode('&', $Parameters);
 			// Split each parameter to name and value and save into variables using their name
 			foreach($Parameters as $parameter){
 				$parameter = explode('=', $parameter, 2);
 				if(empty($parameter[1]))
 					$parameter[1] = true;
 				$$parameter[0] = $parameter[1];
 			}
 			if(!is_int($TopNode)) 
 				$TopNode = 0;
 			return $this->_HomePage($TopNode);
 		}
 		
 		/**
 		 * This function returns the text of the actual modulpage
 		 * @author ComaWStefan
 		 * @access public
 		 * @param string Action This is the action to tell the modul what to do next
 		 * @return string Textpage of the module to be set into the template
 		 */
 		function GetPage($Action) {
 			$out = "<h2>Sitemap</h2>\r\n";
 			switch($Action) {
 				default:		$out .= $this->_HomePage();
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
 		 * This function returns the homepage of the module with the structure beginning at topnode 0
 		 * @author ComaWStefan
 		 * @access private
 		 * @return string The text of the modul
 		 */
 		function _HomePage($TopNode) {
 			$sql = "SELECT *
 				FROM " . DB_PREFIX . "pages";
 			$result = $this->_SqlConnection->SqlQuery($sql);
 			while ($page = mysql_fetch_object($result)) {
 				$this->_PagesParentIDs[$page->page_parent_id][] = array('id' => $page->page_id, 
										'name' => $page->page_name, 
										'title' => $page->page_title, 
										'type' => $page->page_type,
										'lang' => $page->page_lang,
										'access' => $page->page_access);
 			}
 			$out = '';
 			$out .= $this->_ShowStructure($TopNode);
 			return $out;
 		}
 		
 		/**
 		 * This function returns recursively the structure of the page beginnig at $TopNode
 		 * @author ComaWStefan
 		 * @access private
 		 * @param integer TopNode This is the id of the toppage
 		 * @return string The complete structure beginning at the toppage
 		 */
 		 function _ShowStructure($TopNode = 0) {
 		 	$out = '';
 		 	if(empty($this->_PagesParentIDs))
 		 		return;
 		 	if(!array_key_exists($TopNode, $this->_PagesParentIDs))
 		 		return;
 		 	$pages = $this->_PagesParentIDs[$TopNode];
 		 	if(empty($pages))
 		 		return;
 		 	$out .= "\r\n\t\t\t<ol>";
 		 	foreach($pages as $page) {
 		 		if($page['access'] != 'deleted') {
 		 			// blockelements
 		 			$out .= "\r\n\t\t\t\t<li class=\"page_type_" . $page['type'] . "\"><span class=\"structure_row\">";
 		 			// show language of the page if activated
 		 			if ($this->_Config->Get('sitemap_show_language', '1')) {
 		 				$out .= "<span class=\"page_lang\">[{$this->_Lang[$page['lang']]}]</span>";
 		 			}
 		 			// show pagename with link to index.php and pagetitle
 		 			$out .= "<strong><a href=\"index.php?page={$page['name']}\">{$page['title']}</a></strong> (" . rawurldecode($page['name']) . ")</span>";
 		 			// show all subpages
 		 			$out .= $this->_ShowStructure($page['id']);
 		 			// blockelement endings
 		 			$out .= "\r\n\t\t\t\t</li>";
 		 		}
 		 	}
 		 	$out .= "\r\n\t\t\t</ol>";
 		 	return $out;
 		 }
	}