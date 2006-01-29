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
 	
 	require_once('./classes/menu.php');
 	
	/**
	 * @package ComaCMS
	 */
	 
	class Admin_Menu {
	 	
	 	/**
 		 * @var array
 		 */
 		
	 	var $_SqlConnection;
	 	var $_AdminLang;
	 	var $_Menu;
	 	
	 	function Admin_Menu($SqlConnection, $AdminLang) {
	 		$this->_SqlConnection = $SqlConnection;
	 		$this->_AdminLang = $AdminLang;
	 		$this->_Menu = new Menu($this->_SqlConnection);
	 	}
	 	
	 	function GetPage($Action = '') {
	 		$adminLang = $this->_AdminLang;
	 		
	 		$out = "\t\t\t<h2>" . $adminLang['menu-editor'] . "</h2>\r\n";
	 		$Action = strtolower($Action);
	 		switch ($Action) {
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
	 		
	 		$sql = "SELECT *
	 			FROM " . DB_PREFIX . "menu
	 			WHERE menu_menuid=$MenuID
	 			ORDER BY menu_orderid";
	 		$menuResult = $this->_SqlConnection->SqlQuery($sql);
	 		
	 		$out .= "\t\t\t<ol>\r\n";
	 		
	 		while ($menuEntry = mysql_fetch_object($menuResult)) {
	 			$out .= "\t\t\t\t<li class=\"page_type_text\">\r\n" .
	 				"\t\t\t\t\t<span class=\"structure_row\">\r\n";
	 			$out .= "\t\t\t\t\t\t<strong>" . $menuEntry->menu_text . "</strong>\r\n";
	 			$out .= "\t\t\t\t\t</span>\r\n" .
	 				"\t\t\t\t</li>\r\n";
	 		}
	 		$out .= "\t\t\t</ol>";
	 		
	 		return $out;
	 	}
	 }
?>
