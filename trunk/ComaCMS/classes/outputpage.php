<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2007 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : outputpge.php
 # created              : 2005-09-01
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
	class OutputPage {
		
		/**
		 * @access public
		 * @var string
		 */
		var $Text;
		
		/**
		 * @access public
		 * @var string
		 */
		var $Template;
		
		/**
		 * @access public
		 * @var string
		 */
		var $Templatefolder;
		
		/**
		 * @access public
		 * @var string This value will replace the template tag [text]
		 */
		var $Title;
		
		/**
		 * @access public
		 * @var string 
		 */
		var $Position;
		
		/**
		 * @access public
		 * @var integer
		 */
		var $PageID = -1;
		
		/**
		 * @access private
		 * @var Sql
		 */
		var $_SqlConnection;
		
		 		/**
 		 * @var Language
 		 * @access private
 		 */
 		var $_Translation;
 		
 		/**
 		 * @var Config
 		 * @access private
 		 */
 		var $_Config;
 		
 		/**
 		 * @var User
 		 * @access private
 		 */
 		var $_User;
 		
 		/**
 		 * @var ComaLate
 		 * @access private
 		 */
 		 var $_ComaLate;
		
		var $Language = 'en';
	
		/**
		 * @access public
		 * @return void
		 */
		function Outputpage(&$SqlConnection, &$Config, &$Translation, &$ComaLate, &$User) {
			$this->_SqlConnection = &$SqlConnection;
			$this->_Config = &$Config;
			$this->_ComaLate = &$ComaLate;
			$this->_Translation = &$Translation;
			$this->_User = &$User;
		}
		
		/**
		 * @return string
		 */	
		function PositionOfPage($pageID = 0, $between = ' > ', $link = true) {
			$sql = "SELECT *
				FROM " . DB_PREFIX . "pages
				WHERE page_id=$pageID";
			$actual_result = $this->_SqlConnection->SqlQuery($sql);
			$actual = mysql_fetch_object($actual_result);
			$parent_id = $actual->page_parent_id;
			$way_to_root = '';		
			
			while($parent_id != 0) {
				$sql = "SELECT *
					FROM " . DB_PREFIX . "pages
					WHERE page_id=$parent_id";
				$parent_result = $this->_SqlConnection->SqlQuery($sql);
				$parent = mysql_fetch_object($parent_result);
				$parent_id = $parent->page_parent_id;
				$page_title = $parent->page_title;
				if($link)
					$page_title = '<a href="index.php?page=' . $parent->page_name . '">' . $page_title . '</a>';
				$way_to_root = $page_title . $between . $way_to_root;
			}
			
			if($link)
				$actual_page_title = '<a href="index.php?page=' . $actual->page_name . '">' . $actual->page_title . '</a>';
			$this->Position = $way_to_root . $actual_page_title;
		}
		
		/**
		 * @return array Menuentries
		 */
		function GenerateMenu($menuid = 1) {
			$menu = array();
			$sql = "SELECT *
				FROM " . DB_PREFIX . "menu_entries
				WHERE menu_entries_menuid='$menuid'
				ORDER BY menu_entries_orderid ASC";
			$menuResult = $this->_SqlConnection->SqlQuery($sql);
			while($menuItem = mysql_fetch_object($menuResult)) {
			
				$link = $menuItem->menu_entries_link;
					if(substr($link, 0, 2) == 'l:')
						$link = 'index.php?page=' . substr($link, 2);
					if(substr($link, 0, 2) == 's:')
						$link = 'special.php?page=' . substr($link, 2);
					if(substr($link, 0, 2) == 'a:')
						$link = 'admin.php?page=' . substr($link, 2);
					if(substr($link, 0, 2) == 'd:')
						$link = 'download.php?file_id=' . substr($link, 2);
						
				$menu[] = array('LINK_TEXT' => $menuItem->menu_entries_title,
								'LINK' => $link,
								'CSS_ID' => (($menuItem->menu_entries_css_id != '') ? ' id="' . $menuItem->menu_entries_css_id . '"' : ''),
								'LINK_STYLE' => (($menuItem->menu_entries_page_id == $this->PageID) ? ' class="actual"' : " "));
			}
			return $menu;
		}
		
		/**
		 * @return void
		 */
		function LoadPage($pagename) {
			$load_old = false;
			$change = GetPostOrGet('change');
			if(is_numeric($change) && $this->_User->IsLoggedIn && $change != 0)
				$load_old = true;
			else
				$change = 0;
			if($load_old)
				$sql = "SELECT *
					FROM " . DB_PREFIX . "pages_history
					WHERE page_id=$pagename
					ORDER BY page_date ASC
					LIMIT " . ($change - 1) . ",1";
			else
				$sql = "SELECT *
						FROM " . DB_PREFIX . "pages
						WHERE page_name='$pagename' AND page_lang='{$this->_Translation->OutputLanguage}'";
			$page_result = $this->_SqlConnection->SqlQuery($sql);
			if(!($page_data =  mysql_fetch_object($page_result))) {
				$sql = "SELECT *
						FROM " . DB_PREFIX . "pages
						WHERE page_name='$pagename'";
				$page_result = $this->_SqlConnection->SqlQuery($sql);
				if(!($page_data =  mysql_fetch_object($page_result))) {
					$sql = "SELECT *
							FROM " . DB_PREFIX . "pages
							WHERE page_id='$pagename'";	
					$page_result = $this->_SqlConnection->SqlQuery($sql);
					if(!($page_data =  mysql_fetch_object($page_result))) {
						header("Location: special.php?page=404&want=$pagename");
						die();
					}
				}
			}
			if(!$load_old && $page_data->page_access == 'deleted' && !$this->_User->AccessRghts->delete) {
				header("Location: special.php?page=410&want=$pagename"); //HTTP 410 Gone
				die();
			}
			
			//TODO: generate a warning if an 'old' page is shown
			$this->Title = $page_data->page_title;
			$this->PositionOfPage($page_data->page_id);
			$this->PageID = $page_data->page_id;
			$this->Language = $page_data->page_lang;
			if($page_data->page_type == 'text') {
				include(__ROOT__ . '/classes/page/page_text.php');
				$page = new Page_Text($this->_SqlConnection, $this->_Config, $this->_Translation, $this->_ComaLate, $this->_User);
				if(!is_numeric($change))
					$change = 0;
				$page->LoadPageFromRevision($page_data->page_id, $change);
				$this->Text = $page->HTML;
			}
			elseif($page_data->page_type == 'gallery') {
				include(__ROOT__ . '/classes/page/page_gallery.php');
				$page = new Page_Gallery($this->_SqlConnection, $this->_Config, $this->_Translation, $this->_ComaLate, $this->_User);
				$page->LoadPage($page_data->page_id);
				$this->Text = $page->HTML;
			}
			if($load_old || $page_data->page_access == 'deleted')
				$this->Text = "\n<div class=\"warning\">Sie befinden sich auf einer Seite, die so wie Sie sie sehen, nicht mehr existiert.</div>\n\n" . $this->Text;
	
		}
	
	}
?>