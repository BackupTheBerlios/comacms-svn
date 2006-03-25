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
 	 * 
	 * @package ComaCMS
	 */
	
 	class Menu {
 		
 		/**
 		 * @access private
 		 * @var Sql
 		 */
 		var $_SqlConnection;
 		
 		/**
 		 * Initializes the Menu-Class
 		 * @access public
 		 * @param Sql SqlConnection
 		 */
 		function Menu(&$SqlConnection) {
 			$this->_SqlConnection = &$SqlConnection;
 		}
 		
 		/**
 		 * Moves a MenuItem above the previous MenuItem
 		 * @param integer MenuItemSortID
 		 * @param integer MenuID
 		 */
 		function ItemMoveUp ($MenuItemSortID, $MenuID = 1) {
 			// are these paremeters really numbers?
 			if(is_numeric($MenuItemSortID) && is_numeric($MenuID)) {
 				// this query should return two 'rows' 
 				$sql = "SELECT *
					FROM " . DB_PREFIX . "menu
					WHERE menu_orderid <= $MenuItemSortID AND menu_menuid = $MenuID
					ORDER BY menu_orderid DESC
					LIMIT 0 , 2";
				$menuResult = $this->_SqlConnection->SqlQuery($sql);
				// try to switch the orderID between these 'rows'
				$this->_SwitchSortIDs($menuResult);
 			}
 		}
 		
 		/**
 		 * Moves a MenuItem unter the following MenuItem
 		 * @param integer MenuItemSortID
 		 * @param integer MenuID
 		 * @return void
 		 * @access public
 		 */
 		function ItemMoveDown ($MenuItemSortID, $MenuID = 1) {
 			// are these paremeters really numbers?
 			if(is_numeric($MenuItemSortID) && is_numeric($MenuID)) {
 				$sql = "SELECT *
					FROM " . DB_PREFIX . "menu
					WHERE menu_orderid >= $MenuItemSortID AND menu_menuid = $MenuID
					ORDER BY menu_orderid ASC
					LIMIT 0 , 2";
				$menuResult = $this->_SqlConnection->SqlQuery($sql);
			
				$this->_SwitchSortIDs($menuResult);
 			}
 		}
 		
 		/**
 		 * @access private
 		 * @return void
 		 * @param resource MenuResult
 		 */
 		function _SwitchSortIDs ($MenuResult) {
 			if ($menuItem = mysql_fetch_object($MenuResult)) {
				$menuItemID1 = $menuItem->menu_id;
				$MenuItemSortID1 = $menuItem->menu_orderid;
				
				if ($menuItem = mysql_fetch_object($MenuResult)) {
					$menuItemID2 = $menuItem->menu_id;
					$MenuItemSortID2 = $menuItem->menu_orderid;
					
					$sql = "UPDATE " . DB_PREFIX . "menu
						SET menu_orderid=$MenuItemSortID2
						WHERE menu_id=$menuItemID1";
					$this->_SqlConnection->SqlQuery($sql);
						 
					$sql = "UPDATE " . DB_PREFIX . "menu
						SET menu_orderid=$MenuItemSortID1
						WHERE menu_id=$menuItemID2";
					$this->_SqlConnection->SqlQuery($sql);
				}
			}
 		}
 		
 		/**
 		 * Saves a new 'version' of a MenuEntry by it's ID and MenuID without changing the SortIDs
 		 * @access public
 		 * @param integer MenuID The ID of the MenuEntry
 		 * @param integer MenuMenuID The ID of the Menu (in most cases 1 or 2)
 		 * @param string MenuText The Text the MenuEntry displays
 		 * @param string MenuLink The Link to the page which the MenuEntry refers to
 		 * @return void
 		 */
 		function UpdateMenuEntry($MenuID, $MenuMenuID, $MenuText, $MenuLink) {
			
			if(is_numeric($MenuID) && is_numeric($MenuMenuID) && $MenuText != '' && $MenuLink != '') {
 				// Is it possible that this is an ID of a Page?
 				if(is_numeric($MenuLink)) {
 					$sql = "SELECT *
		 				FROM " . DB_PREFIX . "pages
 						WHERE page_id=$MenuLink";
 					$pageResult = $this->_SqlConnection->SqlQuery($sql);
 	
 					// is there some result?								
	 				if($page = mysql_fetch_object($pageResult)) {
 						
 						$link = "l:" . $page->page_name;
	 				
 						$sql = "SELECT *
	 						FROM " . DB_PREFIX . "menu
 							WHERE menu_id=$MenuID";
 						$menuResult = $this->_SqlConnection->SqlQuery($sql);
 						$menuEntry = mysql_fetch_object($menuResult);
 						$menuOrderID = $menuEntry->menu_orderid;
	 				
 						if($MenuMenuID != $menuEntry->menu_menuid) {
	 						$sql = "SELECT *
 								FROM " . DB_PREFIX . "menu
 								WHERE menu_menuid=$MenuMenuID
 								ORDER BY menu_orderid DESC
 								LIMIT 1";
 							$menuResult = $this->_SqlConnection->SqlQuery($sql);
 							if($menuEntry = mysql_fetch_object($menuResult)) {
	 							$menuOrderID = $menuEntry->menu_orderid + 1;
 							}
 							else {
	 							$menuOrderID = 0;
 							}
 						}
		 				
 						$sql = "UPDATE " . DB_PREFIX . "menu
	 						SET menu_menuid='$MenuMenuID', menu_text='$MenuText', menu_link='$link', menu_page_id='$MenuLink', menu_orderid='$menuOrderID'
 							WHERE menu_id='$MenuID'";
 						$menuResult = $this->_SqlConnection->SqlQuery($sql);
 					}
 				}
			}
		}
	 	
	 	/**
	 	 * Creates a new MenuEntry
	 	 * @access public
	 	 * @param integer MenuID
	 	 * @param integer MenuMenuID
	 	 * @param string MenuText
	 	 * @param string MenuLink
	 	 * @return void
	 	 */
		function AddMenuEntry($MenuID, $MenuMenuID, $MenuText, $MenuLink) {
 			if(is_numeric($MenuID) && is_numeric($MenuMenuID) && $MenuText != '' && $MenuLink != '') {
 				$sql = "SELECT *
	 				FROM " . DB_PREFIX . "pages
 					WHERE page_id=$MenuLink";
 				$pageResult = $this->_SqlConnection->SqlQuery($sql);
 				$numRows = mysql_num_rows($pageResult);
 							
 				if($numRows = 1) {
	 					$page = mysql_fetch_object($pageResult);
 					$link = "l:" . $page->page_name;
	 				
 					$sql = "SELECT *
	 					FROM " . DB_PREFIX . "menu
 						WHERE menu_menuid=$MenuMenuID
 						ORDER BY menu_orderid DESC
	 					LIMIT 1";
 					$menuResult = $this->_SqlConnection->SqlQuery($sql);
	 				if($menuEntry = mysql_fetch_object($menuResult)) {
 						$menuOrderID = $menuEntry->menu_orderid + 1;
 					}
 					else {
	 					$menuOrderID = 0;
 					}
	 				
 					$sql = "INSERT INTO " . DB_PREFIX . "menu
						(menu_text, menu_link, menu_new, menu_orderid, menu_menuid, menu_page_id)
						VALUES ('$MenuText', '$link', 'no', $menuOrderID, $MenuMenuID, $page->page_id)";
					$this->_SqlConnection->SqlQuery($sql);
 				}
 			}
 			
 		}
 		
 		/**
 		 * Deletes a MenuEntry by it's ID
 		 * @param integer MenuID
 		 * @return void
 		 * @access public
 		 */
 		function DeleteMenuEntry ($MenuID) {
 			if(is_numeric($MenuID)) {
 				$sql = "DELETE
	 				FROM " . DB_PREFIX . "menu
 					WHERE menu_id=$MenuID";
 				$menuResult = $this->_SqlConnection->SqlQuery($sql);
 			}
 		}
 	}
?>
