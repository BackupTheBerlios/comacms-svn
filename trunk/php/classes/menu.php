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
	 * @package ComaCMS
	 */
	
 	class Menu {
 		var $_SqlConnection;
 		
 		function Menu($SqlConnection) {
 			$this->_SqlConnection = $SqlConnection;
 		}
 		
 		function ItemMoveUp ($MenuItemSortID, $MenuID = 1) {
 			$sql = "SELECT *
				FROM " . DB_PREFIX . "menu
				WHERE menu_orderid <= $MenuItemSortID AND menu_menuid = $MenuID
				ORDER BY menu_orderid DESC
				LIMIT 0 , 2";
			$menuResult = $this->_SqlConnection->SqlQuery($sql);
			
			$this->_SwitchOrderIDs($menuResult);
 		}
 		
 		function ItemMoveDown ($MenuItemSortID, $MenuID = 1) {
 			$sql = "SELECT *
				FROM " . DB_PREFIX . "menu
				WHERE menu_orderid >= $MenuItemSortID AND menu_menuid = $MenuID
				ORDER BY menu_orderid ASC
				LIMIT 0 , 2";
			$menuResult = $this->_SqlConnection->SqlQuery($sql);
			
			$this->_SwitchOrderIDs($menuResult);
 		}
 		
 		function _SwitchOrderIDs ($MenuResult) {
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
 	}
?>
