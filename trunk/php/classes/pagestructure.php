<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: pagestructure.php				#
 # created		: 2006-01-27					#
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
	class PageStructure {
		
		var $_SqlConnection;
		var $_User;
		var $_Pages = array();
		var $_ParentIDPages = array();
		/**
		 * @param SqlConnection SqlConnection
		 * @param User User
		 */
		function PageStructure($SqlConnection, $User) {
			$this->_SqlConnection = $SqlConnection;
			$this->_User = $User;
		}
		
		/**
		 * @param integer PageID
		 * @param string PageAccess
		 * @return void
		 */
		function SetPageDeleted($PageID) {
			$sql = "UPDATE " . DB_PREFIX . "pages
				SET  page_access='deleted', page_creator='$this->_User->ID', page_date='" . mktime() . "'
				WHERE page_id='$PageID'";
			$this->_SqlConnection->SqlQuery($sql);
		}
		
		function LoadData($PageID) {
			if(empty($this->_Pages[$PageID])) {
				$sql = "SELECT *
		 			FROM " . DB_PREFIX . "pages
		 			WHERE page_id=$PageID
		 			LIMIT 0, 1";
		 		$pageResult = $this->_SqlConnection->SqlQuery($sql);
		 		if($page = mysql_fetch_object($pageResult))
			 		$this->_Pages[$PageID] = array('name' => $page->page_name, 'title' => $page->page_title);
		 	}
		}
		
		function LoadParentIDs() {
			// TODO: ORDER BY page_sortid
			$sql = "SELECT *
		 		FROM " . DB_PREFIX . "pages
		 		ORDER BY page_parent_id";
		 	$pageResult = $this->_SqlConnection->SqlQuery($sql);
	 		while($page = mysql_fetch_object($pageResult)) {
	 			$this->_ParentIDPages[$page->page_parent_id][] =
	 					array('id' => $page->page_id,
	 						'name' => $page->page_name,
	 						'type' => $page->page_type,
	 						'lang' => $page->page_lang,
	 						'title' => $page->page_title,
	 						'access' => $page->page_access);
	 		}
		}
		
		/**
		 * @param integer PageID
		 * @return boolean
		 */
		function PageExists($PageID) {
			$this->LoadData($PageID);
			return !empty($this->_Pages[$PageID]);
		}
		
		function GetPageData($PageID, $Field) {
			$this->LoadData($PageID);
			if(empty($this->_Pages[$PageID])) {
				return false;
			}
			else
				return $this->_Pages[$PageID][$Field];
		}
		
		/**
		 * @param Array PageIDs
		 * @param integer MenuID
		 */
		function GenerateMenu($PageIDs, $MenuID = 1) {
			$oderID = 0;
			if(count($PageIDs) <= 0)
				return;
			foreach($PageIDs as $pageID) {
				$sql = "SELECT *
		 			FROM " . DB_PREFIX . "pages
		 			WHERE page_id=$pageID";
		 		$pageResult = $this->_SqlConnection->SqlQuery($sql);
		 		if($page = mysql_fetch_object($pageResult)) {
		 			if($page->page_access == 'public') {
			 			$link = "l:" . $page->page_name;
			 			$sql = "INSERT INTO " . DB_PREFIX . "menu
							(menu_text, menu_link, menu_new, menu_orderid, menu_menuid, menu_page_id)
							VALUES ('$page->page_title', '$link', 'no', $oderID, $MenuID, $page->page_id)";
						$this->_SqlConnection->SqlQuery($sql);
						$oderID++;
		 			}
		 		}
			}
		}
		
		/**
		 * @param integer MenuID
		 */
		function ClearMenu($MenuID = 1) {
			$sql = "DELETE
				FROM " . DB_PREFIX . "menu
				WHERE menu_menuid=$MenuID";
			$this->_SqlConnection->SqlQuery($sql);
		}
		/** PageStructurePulldown
		 * This function generates all <option>-tags to create a pull-down-list wherec you can select a page 
		 * 
		 * @access public
		 * @param integer Topnode The ID of the root-element
		 * @param integer Deep
		 * @param string Topnumber
		 * @param integer Without The element with this ID and all its child-elemnts will be ignored
		 * @param integer Selected This element will be selected
		 * @return string
		 */
		function PageStructurePulldown($Topnode = 0, $Deep = 0, $Topnumber = '', $Without = -1, $Selected = -1) {
		 	$out = '';
			if(empty($this->_ParentIDPages[$Topnode]))
		 		return '';
		 	$number = 1;
		 	foreach($this->_ParentIDPages[$Topnode] as $page) {
		 		if($page['id'] != $Without) {
		 			$out .= "<option style=\"padding-left:" . ($Deep * 1.5) . "em;\" value=\"" . $page['id'] . "\"" . (($page['id'] == $Selected) ? ' selected="selected"' : '') . ">$Topnumber$number. " . $page['title'] . " (" . $page['name'] . ")</option>\r\n";
		 			$out .= $this->PageStructurePulldown($page['id'], $Deep + 1, $Topnumber . $number. "." ,$Without, $Selected);
		 			$number++;
		 		}
		 		
		 	}
		 	return $out;
		}
		
		function LoadInlineMenuData($PageID) {
			if(empty($this->_InlineMenus[$PageID])) {
				$sql = "SELECT *
		 			FROM " . DB_PREFIX . "inlinemenu
		 			WHERE page_id=$PageID
		 			LIMIT 0, 1";
		 		$menuResult = $this->_SqlConnection->SqlQuery($sql);
		 		if($menu = mysql_fetch_object($menuResult))
			 		$this->_InlineMenus[$PageID] = array('image' => $menu->inlinemenu_image,
									'image_thumb' => $menu->inlinemenu_image_thumb,
									'image_title' => $menu->inlinemenu_image_title,
									'html' => $menu->inlinemenu_html);
		 	}
		}
		
		function InlineMenuExists($PageID) {
			$this->LoadInlineMenuData($PageID);
			return !empty($this->_InlineMenus[$PageID]);
		}
		
		function CreateInlineMenu($PageID) {
			$sql = "INSERT INTO " . DB_PREFIX . "inlinemenu (page_id, inlinemenu_image, inlinemenu_image_thumb, inlinemenu_image_title, inlinemenu_html)
				VALUES($PageID, '', '', '', '')";
			$this->_SqlConnection->SqlQuery($sql);
			$this->_InlineMenus[$PageID] = array('image' => '',
								'image_thumb' => '',
								'image_title' => '',
								'html' => '');
		}
		
		function GetInlineMenuData($PageID, $Field) {
			$this->LoadInlineMenuData($PageID);
			if(empty($this->_InlineMenus[$PageID])) {
				return false;
			}
			else
				return $this->_InlineMenus[$PageID][$Field];
		}
		
		function SetInlineMenuImage($PageID, $ImageThumb, $Image) {
			//echo $PageID . "<br>" . $ImageThumb. "<br>" . $Image;
			$sql = "UPDATE " . DB_PREFIX . "inlinemenu
				SET inlinemenu_image_thumb='$ImageThumb', inlinemenu_image='$Image'
				WHERE page_id=$PageID";
			//echo "<br>$sql";
			$this->_SqlConnection->SqlQuery($sql);
			$this->_InlineMenus[$PageID]['image'] = $Image;
			$this->_InlineMenus[$PageID]['image_thumb'] = $ImageThumb;
		}
		
		function SetInlineMenuImageTitle($PageID, $Title) {
			$sql = "UPDATE " . DB_PREFIX . "inlinemenu
				SET inlinemenu_image_title='$Title'
				WHERE page_id=$PageID";
			$this->_SqlConnection->SqlQuery($sql);
			$this->_InlineMenus[$PageID]['image_title'] = $Title;
		}
		
		function RemoveInlineMenuImage($PageID) {
			$sql = "UPDATE " . DB_PREFIX . "inlinemenu
				SET inlinemenu_image='', inlinemenu_image_thumb=''
				WHERE page_id=$PageID";
			$this->_SqlConnection->SqlQuery($sql);
			$this->_InlineMenus[$PageID]['image'] = '';
			$this->_InlineMenus[$PageID]['image_thumb'] = '';
		}
		
	}
?>