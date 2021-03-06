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
	 require_once __ROOT__ . '/classes/module.php';
	 require_once __ROOT__ . '/classes/pagestructure.php';
	 
	/**
	 * @package ComaCMS
	 * @subpackage Sitemap
	 */
	class Module_Sitemap extends Module{
		
		/**
		 * This is the pagestructureclass to get access to pagedata
		 * @author ComaWStefan
		 * @access private
		 * @var class
		 */
		var $_Pagestructure; 
 		
 		function _Init() {
 			$this->_Pagestructure = new PageStructure($this->_SqlConnection, $this->_User, $this->_ComaLib);
 			$this->_Pagestructure->LoadParentIDs();
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
 			if(!is_integer($TopNode)) 
 				$TopNode = 0;
 			return $this->_ShowStructure($TopNode);
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
 			$topNode = GetPostOrGet('TopNode');
 			if (!is_integer($topNode))
 				$topNode = 0;
 			switch($Action) {
 				default:		$out .= $this->_ShowStructure($topNode);
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
 		 * This function returns the structure of the page recursively beginnig at $TopNode
 		 * @author ComaWStefan
 		 * @access private
 		 * @param integer TopNode This is the id of the toppage
 		 * @return string The complete structure beginning at the toppage
 		 */
 		 function _ShowStructure($TopNode = 0) {
 		 	$pages = $this->_Pagestructure->RemoveAcessDeletedPages();
 		 	$out = '';
 		 	if(!array_key_exists($TopNode, $pages))
 		 		return;
 		 	if(empty($pages[$TopNode]))
 		 		return;
 		 	$out .= "\r\n\t\t\t<ul>";
 		 	// TODO: make this configureabele by the module-call
 		 	$showLanguage = $this->_Config->Get('sitemap_show_language', '1');
 		 	foreach($pages[$TopNode] as $page) {
 		 		if ($page['access'] == 'public') {
	 		 		// blockelements
		 			$out .= "\r\n\t\t\t\t<li class=\"page_type_" . $page['type'] . "\"><span class=\"structure_row\">";
		 			// show language of the page if activated
		 			if ($showLanguage)
		 				$out .= "<span class=\"page_lang\">[" . $this->_Translation->GetTranslation($page['lang']) . "]</span>";
		 			
		 			// show pagename with link to index.php and pagetitle
		 			$out .= "<strong><a href=\"index.php?page={$page['name']}\">{$page['title']}</a></strong></span>";
		 			// show all subpages
		 			$out .= $this->_ShowStructure($page['id']);
		 			// blockelement endings
		 			$out .= "\r\n\t\t\t\t</li>";
 		 		}
 		 	}
 		 	$out .= "\r\n\t\t\t</ul>";
 		 	return $out;
 		 }
	}