<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: admin_menu.php				#
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
 	 * @ignore
 	 */
 	require_once('./classes/admin/admin.php');
 	require_once('./classes/menu.php');
 	
	/**
	 * @package ComaCMS
	 */
	 
	class Admin_Menu extends Admin{
	 	
	 	var $_Menu;
	 	
	 	function Admin_Menu($SqlConnection, $AdminLang) {
	 		$this->_SqlConnection = $SqlConnection;
	 		$this->_AdminLang = $AdminLang;
	 		$this->_Menu = new Menu($this->_SqlConnection);
	 	}
	 	
	 	function GetPage($Action = '') {
	 		$adminLang = $this->_AdminLang;
	 		
	 		$out = "\t\t\t<h2>" . $adminLang['menu-editor'] . "</h2>\r\n";
	 		switch ($Action) {
	 			case 'edit':		$out .= $this->_EditMenuEntry(GetPostOrGet('menu_id'));
	 						break;
	 			case 'up':		$out .= $this->_Menu->_MoveUp(GetPostOrGet('menu_orderid'), GetPostOrGet('menu_id'));
	 						$out .= $this->_HomePage();
	 						break;
	 			case 'down':		$out .= $this->_Menu->_MoveDown(GetPostOrGet('menu_orderid'), GetPostOrGet('menu_id'));
	 						$out .= $this->_HomePage();
	 						break;
	 			default:		$out .= $this->_HomePage();
	 		}
	 		return $out; 
	 	}
	 	
	 	function _HomePage() {
	 		$adminLang = $this->_AdminLang;
	 		$menuID = GetPostOrGet('menu_id');
	 		$menuID = ($menuID != 2) ? 1 : 2;
	 		$out = '';
	 		
	 		$out .= "\t\t\t<a href=\"admin.php?page=menueditor&amp;menu_id=1\" class=\"button" . (($menuID == 1) ? ' actual' : '') . "\">" . $adminLang['mainmenu'] . "</a>\r\n";
	 		$out .= "\t\t\t<a href=\"admin.php?page=menueditor&amp;menu_id=2\" class=\"button" . (($menuID == 2) ? ' actual' : '') . "\">" . $adminLang['secondmenu'] . "</a><br /><br />\r\n";
	 		
	 		$out .= $this->_ShowMenu($menuID);
	 		
	 		return $out;
	 	}
	 	
	 	function _ShowMenu($MenuID) {
	 		$out = '';
	 		$adminLang = $this->_AdminLang;
	 		
	 		$sql = "SELECT *
	 			FROM " . DB_PREFIX . "menu
	 			WHERE menu_menuid=$MenuID
	 			ORDER BY menu_orderid";
	 		$menuResult = $this->_SqlConnection->SqlQuery($sql);
	 		
	 		$out .= "\t\t\t<ol>\r\n";
	 		
	 		while ($menuEntry = mysql_fetch_object($menuResult)) {
	 			$out .= "\t\t\t\t<li class=\"page_type_text\">
					<span class=\"structure_row\">
						<strong>" . $menuEntry->menu_text . "</strong>	 			
	 					<span class=\"page_actions\">
	 						<a href=\"admin.php?page=menueditor&amp;action=edit&amp;menu_id=" . $menuEntry->menu_id . "\"><img src=\"./img/edit.png\" class=\"icon\" alt=\"" . $adminLang['edit']. "\" title=\"" . $adminLang['edit'] . "\" height=\"16\" width=\"16\"></a>
	 						<a href=\"admin.php?page=menueditor&amp;action=up&amp;menu_orderid=" . $menuEntry->menu_orderid . "&amp;menu_id=" . $menuEntry->menu_menuid . "\"><img src=\"./img/up.png\" class=\"icon\" alt=\"" . $adminLang['move_up'] . "\" title=\"" . $adminLang['move_up'] . "\" height=\"16\" width=\"16\"></a>
	 						<a href=\"admin.php?page=menueditor&amp;action=down&amp;menu_orderid=" . $menuEntry->menu_orderid . "&amp;menu_id=" . $menuEntry->menu_menuid . "\"><img src=\"./img/down.png\" class=\"icon\" alt=\"" . $adminLang['move_down'] . "\" title=\"" . $adminLang['move_down'] . "\" height=\"16\" width=\"16\"></a>
	 						<a href=\"admin.php?page=menueditor&amp;action=delete&amp;menu_id=" . $menuEntry->menu_id . "\"><img src=\"./img/del.png\" class=\"icon\" alt=\"" . $adminLang['delete'] . "\" title=\"" . $adminLang['delete'] . "\" height=\"16\" width=\"16\"></a>
	 					</span>
	 				</span>
	 			</li>\r\n";
	 		}
	 		$out .= "\t\t\t</ol>";
	 		
	 		return $out;
	 	}
	 	
	 	function _EditMenuEntry($MenuID) {
	 		$out = '';
	 		$adminLang = $this->_AdminLang;
	 		
	 		$sql = "SELECT *
	 			FROM " . DB_PREFIX . "menu
	 			WHERE menu_id=$MenuID";
	 		$menuResult = $this->_SqlConnection->SqlQuery($sql);
	 		$menuEntry = mysql_fetch_object($menuResult);
	 		
	 		$out .= "\t\t\t<fieldset>
				<legend>" . $adminLang['edit_menu_entry'] . "</legend>
				<form action=\"admin.php\" method=\"post\">
					<div class=\"row\">
						<label for=\"menuID\">" . $adminLang['belongs_to_menu'] . "<span class=\"info\">" . $adminLang['todo'] . "</span></label>
						<select id=\"menuID\" name=\"menuID\">
							<option value=\"1\"" . (($menuEntry->menu_menuid == 1) ? ' selected="selected"' : '') . ">" . $adminLang['mainmenu'] . "</option>
							<option value=\"2\"" . (($menuEntry->menu_menuid == 2) ? ' selected="selected"' : '') . ">" . $adminLang['secondmenu'] . "</option>
						</select>
					</div>
				</form>
			</fieldset>";
	 		
	 		return $out;
	 	}
	 }
?>
