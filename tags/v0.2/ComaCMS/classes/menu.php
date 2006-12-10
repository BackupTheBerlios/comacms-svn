<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : menu.php
 # created              : 2005-01-28
 # copyright            : (C) 2005-2006 The ComaCMS-Team
 # email                : comacms@williblau.de
 #----------------------------------------------------------------------
 # This program is free software; you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation; either version 2 of the License, or
 # (at your option) any later version.
 #----------------------------------------------------------------------
 
 	/**
 	 * 
	 * @package ComaCMS
	 */
	
 	class Menu {
 		
 		/**
 		 * Connection to the MySqlDatabase
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
 		 * @param integer Menu_entry_orderid
 		 * @param integer Menu_entry_id
 		 */
 		function ItemMoveUp ($Menu_entry_orderid, $Menu_entry_id = 1) {
 			// are these paremeters really numbers?
 			if(is_numeric($Menu_entry_orderid) && is_numeric($Menu_entry_id)) {
 				// this query should return two 'rows' 
 				$sql = "SELECT *
					FROM " . DB_PREFIX . "menu_entries
					WHERE menu_entries_orderid <= $Menu_entry_orderid AND menu_entries_menuid = $Menu_entry_id
					ORDER BY menu_entries_orderid DESC
					LIMIT 0 , 2";
				$menuResult = $this->_SqlConnection->SqlQuery($sql);
				// try to switch the orderID between these 'rows'
				$this->_SwitchOrderIDs($menuResult);
 			}
 		}
 		
 		/**
 		 * Moves a MenuItem unter the following MenuItem
 		 * @param integer Menu_entry_orderid
 		 * @param integer Menu_entry_id
 		 * @return void
 		 * @access public
 		 */
 		function ItemMoveDown ($Menu_entry_orderid, $Menu_entry_id = 1) {
 			// are these paremeters really numbers?
 			if(is_numeric($Menu_entry_orderid) && is_numeric($Menu_entry_id)) {
 				$sql = "SELECT *
					FROM " . DB_PREFIX . "menu_entries
					WHERE menu_entries_orderid >= $Menu_entry_orderid AND menu_entries_menuid = $Menu_entry_id
					ORDER BY menu_entries_orderid ASC
					LIMIT 0 , 2";
				$menuResult = $this->_SqlConnection->SqlQuery($sql);
			
				$this->_SwitchOrderIDs($menuResult);
 			}
 		}
 		
 		/**
 		 * @access private
 		 * @return void
 		 * @param resource MenuResult
 		 */
 		function _SwitchOrderIDs ($MenuResult) {
 			if ($menuItem = mysql_fetch_object($MenuResult)) {
				$menuItemID1 = $menuItem->menu_entries_id;
				$MenuItemOrderID1 = $menuItem->menu_entries_orderid;
				
				if ($menuItem = mysql_fetch_object($MenuResult)) {
					$menuItemID2 = $menuItem->menu_entries_id;
					$MenuItemOrderID2 = $menuItem->menu_entries_orderid;
					
					$sql = "UPDATE " . DB_PREFIX . "menu_entries
						SET menu_entries_orderid=$MenuItemOrderID2
						WHERE menu_entries_id=$menuItemID1";
					$this->_SqlConnection->SqlQuery($sql);
						 
					$sql = "UPDATE " . DB_PREFIX . "menu_entries
						SET menu_entries_orderid=$MenuItemOrderID1
						WHERE menu_entries_id=$menuItemID2";
					$this->_SqlConnection->SqlQuery($sql);
				}
			}
 		}
 		
 		/**
 		 * Saves a new 'version' of a MenuEntry by it's ID and MenuID without changing the OrderIDs
 		 * @access public
 		 * @param integer Menu_entry_id The ID of the MenuEntry
 		 * @param integer Menu_entry_menu_id The ID of the Menu
 		 * @param string Menu_entry_menuid The Title of the MenuEntry
 		 * @param string Menu_entry_link The Link to the page which the MenuEntry refers to
 		 * @param string Menu_entry_css_id The css id for the menuentry
 		 * @return void
 		 */
 		function UpdateMenuEntry($Menu_entry_id, $Menu_entry_menuid, $Menu_entry_text, $Menu_entry_link, $Menu_entry_css_id) {
			
			if(is_numeric($Menu_entry_id) && is_numeric($Menu_entry_menuid) && $Menu_entry_text != '' && $Menu_entry_link != '') {
 				// Is it possible that this is an ID of a Page?
 				if(is_numeric($Menu_entry_link)) {
 					$sql = "SELECT *
		 				FROM " . DB_PREFIX . "pages
 						WHERE page_id=$Menu_entry_link";
 					$pageResult = $this->_SqlConnection->SqlQuery($sql);
 	
 					// is there some result?								
	 				if($page = mysql_fetch_object($pageResult)) {
 						
 						$link = "l:" . $page->page_name;
	 				
 						$sql = "SELECT *
	 						FROM " . DB_PREFIX . "menu_entries
 							WHERE menu_entries_id=$Menu_entry_id";
 						$menuResult = $this->_SqlConnection->SqlQuery($sql);
 						$menuEntry = mysql_fetch_object($menuResult);
 						$menu_entry_orderid = $menuEntry->menu_entries_orderid;
	 				
 						if($Menu_entry_menuid != $menuEntry->menu_entries_menuid) {
	 						$sql = "SELECT *
 								FROM " . DB_PREFIX . "menu_entries
 								WHERE menu_entries_menuid=$Menu_entry_menuid
 								ORDER BY menu_entries_orderid DESC
 								LIMIT 1";
 							$menuResult = $this->_SqlConnection->SqlQuery($sql);
 							if($menuEntry = mysql_fetch_object($menuResult)) {
	 							$menu_entry_orderid = $menuEntry->menu_entries_orderid + 1;
 							}
 							else {
	 							$menu_entry_orderid = 0;
 							}
 						}
		 				
 						$sql = "UPDATE " . DB_PREFIX . "menu_entries
	 						SET menu_entries_menuid='$Menu_entry_menuid', menu_entries_title='$Menu_entry_text', menu_entries_link='$link', menu_entries_page_id='{$page->page_id}', menu_entries_orderid='$menu_entry_orderid', menu_entries_css_id='$Menu_entry_css_id'
 							WHERE menu_entries_id='$Menu_entry_id'";
 						$menuResult = $this->_SqlConnection->SqlQuery($sql);
 					}
 				}
			}
		}
	 	
	 	/**
	 	 * Creates a new MenuEntry
	 	 * @access public
	 	 * @param integer Menu_entry_menu_id The ID of the Menu
	 	 * @param string Menu_entry_title The Title of the MenuEntry
	 	 * @param string Menu_entry_link The Link to the page which the MenuEntry refers to
	 	 * @param string Menu_entry_css_id The css id for the menuentry
	 	 * @return void
	 	 */
		function AddMenuEntry($Menu_entry_menuid,  $Menu_entry_title, $Menu_entry_link, $Menu_entry_css_id) {
 			if(is_numeric($Menu_entry_menuid) && $Menu_entry_title != '' && $Menu_entry_link != '') {
 				$sql = "SELECT *
	 				FROM " . DB_PREFIX . "pages
 					WHERE page_id=$Menu_entry_link";
 				$pageResult = $this->_SqlConnection->SqlQuery($sql);
 				$numRows = mysql_num_rows($pageResult);
 							
 				if($numRows = 1) {
	 				$page = mysql_fetch_object($pageResult);
 					$link = "l:" . $page->page_name;
	 				
 					$sql = "SELECT *
	 					FROM " . DB_PREFIX . "menu_entries
 						WHERE menu_entries_menuid=$Menu_entry_menuid
 						ORDER BY menu_entries_orderid DESC
	 					LIMIT 1";
 					$menuResult = $this->_SqlConnection->SqlQuery($sql);
	 				if($menuEntry = mysql_fetch_object($menuResult)) {
 						$menu_entry_orderid = $menuEntry->menu_entries_orderid + 1;
 					}
 					else {
	 					$menu_entry_orderid = 0;
 					}
	 				
 					$sql = "INSERT INTO " . DB_PREFIX . "menu_entries
						(menu_entries_title, menu_entries_link, menu_entries_orderid, menu_entries_menuid, menu_entries_page_id" . (($Menu_entry_css_id != '') ? ', menu_entries_css_id' : '') . ")
						VALUES ('$Menu_entry_title', '$link', $menu_entry_orderid, $Menu_entry_menuid, {$page->page_id}" . (($Menu_entry_css_id != '') ? ', \'' . $Menu_entry_css_id . '\'': '') . ")";
					$this->_SqlConnection->SqlQuery($sql);
 				}
 			}
 			
 		}
 		
 		/**
 		 * Creates a new Menu
 		 * @access public
 		 * @param string Menu_title The title of the new Menu
 		 * @return void
 		 */
 		function AddMenu($Menu_title) {
 			if ($Menu_title != '') {
 				$sql = "INSERT INTO " . DB_PREFIX . "menu
 					(menu_name)
 					VALUES ('$Menu_title')";
 				$this->_SqlConnection->SqlQuery($sql);
 			}
 		}
 		
 		/**
 		 * Saves a new 'version' of a Menu by it's ID 
 		 * @access public
 		 * @param integer Menu_menuid The id of the menu to update
 		 * @param string Menu_title The title of the Menu
 		 * @return void
 		 */
 		function UpdateMenu($Menu_menuid, $Menu_title, $Menu_name) {
 			if (is_numeric($Menu_menuid) && $Menu_title != '' && $Menu_name != 'DEFAULT') {
 				$sql = "UPDATE " . DB_PREFIX . "menu
 					SET menu_name='$Menu_title'
 					WHERE menu_id='$Menu_menuid'";
 				$this->_SqlConnection->SqlQuery($sql);
 			}
 		}
 		
 		/**
 		 * Deletes a MenuEntry by it's ID
 		 * @access public
 		 * @param integer Menu_entry_id The id of the Entry that should be deleted
 		 * @return void
 		 */
 		function DeleteMenuEntry ($Menu_entry_id) {
 			if(is_numeric($Menu_entry_id)) {
 				$sql = "DELETE
	 				FROM " . DB_PREFIX . "menu_entries
 					WHERE menu_entries_id=$Menu_entry_id";
 				$menuResult = $this->_SqlConnection->SqlQuery($sql);
 			}
 		}
 		
 		/**
 		 * Deletes a Menu by it's ID
 		 * @access public
 		 * @param integer Menu_menuid The id of the Menu that should be deleted
 		 * @return void
 		 */
 		function DeleteMenu ($Menu_menuid, $Menu_name) {
 			if (is_numeric($Menu_menuid) && $Menu_name != 'DEFAULT') {
 				$sql = "DELETE
 					FROM " . DB_PREFIX . "menu
 					WHERE menu_id='$Menu_menuid'";
 				$this->_SqlConnection->SqlQuery($sql);
 				$sql = "DELETE
					FROM " . DB_PREFIX . "menu_entries
					WHERE menu_entries_menuid='$Menu_menuid'";
				$this->_SqlConnection->SqlQuery($sql);
 			}
 		}
 	}
?>
