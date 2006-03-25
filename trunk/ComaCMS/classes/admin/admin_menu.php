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
 	require_once('./classes/pagestructure.php');
 	
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
	 			case 'new':		$out .= $this->_AddMenuEntry(GetPostOrGet('menuMenuID'));
	 						break;
	 			case 'addEntry':	$this->_Menu->AddMenuEntry(GetPostOrGet('menuID'), GetPostOrGet('menuMenuID'), GetPostOrGet('menuText'), GetPostOrGet('menuLink'));
	 						$out .= $this->_HomePage();
	 						break;
	 			case 'edit':		$out .= $this->_EditMenuEntry(GetPostOrGet('menuID'));
	 						break;
	 			case 'updateEntry':	$this->_Menu->UpdateMenuEntry(GetPostOrGet('menuID'), GetPostOrGet('menuMenuID'), GetPostOrGet('menuText'), GetPostOrGet('menuLink'));
	 						$out .= $this->_HomePage();
	 						break;
	 			case 'up':		$out .= $this->_Menu->ItemMoveUp(GetPostOrGet('menuOrderid'), GetPostOrGet('menuMenuID'));
	 						$out .= $this->_HomePage();
	 						break;
	 			case 'down':		$out .= $this->_Menu->ItemMoveDown(GetPostOrGet('menuOrderid'), GetPostOrGet('menuMenuID'));
	 						$out .= $this->_HomePage();
	 						break;
	 			case 'delete':		$out .= $this->_DeleteMenuEntry(GetPostOrGet('menuID'), GetPostOrGet('menuMenuID'));
	 						break;
	 			case 'deleteSure':	$this->_Menu->DeleteMenuEntry(GetPostOrGet('menuID'));
	 						$out .= $this->_HomePage();
	 						break;
	 			default:		$out .= $this->_HomePage();
	 		}
	 		return $out; 
	 	}
	 	
	 	function _HomePage() {
	 		$adminLang = $this->_AdminLang;
	 		$menuMenuID = GetPostOrGet('menuMenuID');
	 		$menuMenuID = ($menuMenuID != 2) ? 1 : 2;
	 		$out = '';
	 		
	 		$out .= "\t\t\t<a href=\"admin.php?page=menueditor&amp;menuMenuID=$menuMenuID&amp;action=new\" class=\"button\">" . $adminLang['add_menu_entry'] . "</a>\r\n";
	 		$out .= "\t\t\t<a href=\"admin.php?page=menueditor&amp;menuMenuID=1\" class=\"button" . (($menuMenuID == 1) ? ' actual' : '') . "\">" . $adminLang['mainmenu'] . "</a>\r\n";
	 		$out .= "\t\t\t<a href=\"admin.php?page=menueditor&amp;menuMenuID=2\" class=\"button" . (($menuMenuID == 2) ? ' actual' : '') . "\">" . $adminLang['secondmenu'] . "</a><br /><br />\r\n";
	 		
	 		$out .= $this->_ShowMenu($menuMenuID);
	 		
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
	 		
	 		$numRows = mysql_num_rows($menuResult);
	 		if($numRows > 0)
	 			$out .= "\t\t\t<ol>\r\n";
	 		
	 		while ($menuEntry = mysql_fetch_object($menuResult)) {
	 			$out .= "\t\t\t\t<li class=\"page_type_text\">
					<span class=\"structure_row\">
						<strong>" . $menuEntry->menu_text . "</strong>	 			
	 					<span class=\"page_actions\">
	 						<a href=\"admin.php?page=menueditor&amp;action=edit&amp;menuID={$menuEntry->menu_id}\"><img src=\"./img/edit.png\" class=\"icon\" alt=\"{$adminLang['edit']}\" title=\"{$adminLang['edit']}\" height=\"16\" width=\"16\" /></a>
	 						<a href=\"admin.php?page=menueditor&amp;action=up&amp;menuOrderid={$menuEntry->menu_orderid}&amp;menuMenuID={$menuEntry->menu_menuid}\"><img src=\"./img/up.png\" class=\"icon\" alt=\"{$adminLang['move_up']}\" title=\"{$adminLang['move_up']}\" height=\"16\" width=\"16\" /></a>
	 						<a href=\"admin.php?page=menueditor&amp;action=down&amp;menuOrderid={$menuEntry->menu_orderid}&amp;menuMenuID={$menuEntry->menu_menuid}\"><img src=\"./img/down.png\" class=\"icon\" alt=\"{$adminLang['move_down']}\" title=\"{$adminLang['move_down']}\" height=\"16\" width=\"16\" /></a>
	 						<a href=\"admin.php?page=menueditor&amp;action=delete&amp;menuID={$menuEntry->menu_id}&amp;menuMenuID={$menuEntry->menu_menuid}\"><img src=\"./img/del.png\" class=\"icon\" alt=\"{$adminLang['delete']}\" title=\"{$adminLang['delete']}\" height=\"16\" width=\"16\" /></a>
	 					</span>
	 				</span>
	 			</li>\r\n";
	 		}
	 		if($numRows > 0)
	 			$out .= "\t\t\t</ol>";
	 		
	 		return $out;
	 	}
	 	
	 	function _AddMenuEntry($MenuID) {
	 		$out = '';
	 		$adminLang = $this->_AdminLang;
	 		$pageStructure = new Pagestructure($this->_SqlConnection, null);
	 		$pageStructure->LoadParentIDs();
	 		
	 		$out .= "\t\t\t<fieldset>
				<legend>" . $adminLang['add_menu_entry'] . "</legend>
				<form action=\"admin.php\" method=\"post\">
					<input type=\"hidden\" name=\"menuID\" value=\"$MenuID\" />
					<input type=\"hidden\" name=\"page\" value=\"menueditor\" />
					<input type=\"hidden\" name=\"action\" value=\"addEntry\" />
					<div class=\"row\">
						<label for=\"MenuID\"><strong>{$adminLang['belongs_to_menu']}:</strong> <span class=\"info\">" . $adminLang['todo'] . "</span></label>
						<select id=\"MenuID\" name=\"menuMenuID\">
							<option value=\"1\"" . (($MenuID == 1) ? ' selected="selected"' : '') . ">" . $adminLang['mainmenu'] . "</option>
							<option value=\"2\"" . (($MenuID == 2) ? ' selected="selected"' : '') . ">" . $adminLang['secondmenu'] . "</option>
						</select>
					</div>
					<div class=\"row\">
						<label for=\"menuEntryTitle\"><strong>{$adminLang['menu_entry_title']}:</strong> <span class=\"info\">" . $adminLang['todo'] . "</span></label>
						<input type=\"text\" id=\"menuEntryTitle\" name=\"menuText\" />
					</div>
					<div class=\"row\">
						<label for=\"menuEntryLink\"><strong>{$adminLang['menu_entry_link']}:</strong> <span class=\"info\">" . $adminLang['todo'] . "</span></label>
						<select id=\"menuEntryLink\" name=\"menuLink\">
							" . $pageStructure->PageStructurePulldown(0, 0, '',  -1) . "
						</select>
					</div>
					<div class=\"row\">
						<input type=\"reset\" class=\"button\" value=\"{$adminLang['reset']}\" />&nbsp;
						<input type=\"submit\" class=\"button\" value=\"{$adminLang['save']}\" />
					</div>
				</form>
			</fieldset>";
	 		
	 		return $out;
	 	}
	 	
	 	function _EditMenuEntry($MenuID) {
	 		$out = '';
	 		$adminLang = $this->_AdminLang;
	 		$pageStructure = new Pagestructure($this->_SqlConnection, null);
	 		$pageStructure->LoadParentIDs();
	 		
	 		$sql = "SELECT *
	 			FROM " . DB_PREFIX . "menu
	 			WHERE menu_id=$MenuID";
	 		$menuResult = $this->_SqlConnection->SqlQuery($sql);
	 		$menuEntry = mysql_fetch_object($menuResult);
	 		
	 		$out .= "\t\t\t<fieldset>
				<legend>" . $adminLang['edit_menu_entry'] . "</legend>
				<form action=\"admin.php\" method=\"post\">
					<input type=\"hidden\" name=\"menuID\" value=\"$MenuID\" />
					<input type=\"hidden\" name=\"page\" value=\"menueditor\" />
					<input type=\"hidden\" name=\"action\" value=\"updateEntry\" />
					<div class=\"row\">
						<label for=\"MenuID\"><strong>{$adminLang['belongs_to_menu']}:</strong> <span class=\"info\">" . $adminLang['todo'] . "</span></label>
						<select id=\"MenuID\" name=\"menuMenuID\">
							<option value=\"1\"" . (($menuEntry->menu_menuid == 1) ? ' selected="selected"' : '') . ">" . $adminLang['mainmenu'] . "</option>
							<option value=\"2\"" . (($menuEntry->menu_menuid == 2) ? ' selected="selected"' : '') . ">" . $adminLang['secondmenu'] . "</option>
						</select>
					</div>
					<div class=\"row\">
						<label for=\"menuEntryTitle\"><strong>{$adminLang['menu_entry_title']}:</strong> <span class=\"info\">" . $adminLang['todo'] . "</span></label>
						<input type=\"text\" id=\"menuEntryTitle\" name=\"menuText\" value=\"" . $menuEntry->menu_text . "\" />
					</div>
					<div class=\"row\">
						<label for=\"menuEntryLink\"><strong>{$adminLang['menu_entry_link']}:</strong> <span class=\"info\">" . $adminLang['todo'] . "</span></label>
						<select id=\"menuEntryLink\" name=\"menuLink\">
							" . $pageStructure->PageStructurePulldown(0, 0, '',  -1, $menuEntry->menu_page_id) . "
						</select>
					</div>
					<div class=\"row\">
						<input type=\"reset\" class=\"button\" value=\"" . $adminLang['reset'] . "\" />&nbsp;
						<input type=\"submit\" class=\"button\" value=\"" . $adminLang['save'] . "\" />
					</div>
				</form>
			</fieldset>";
	 		
	 		return $out;
	 	}
	 	
	 	function _DeleteMenuEntry($MenuID, $MenuMenuID) {
	 		$out = '';
	 		$adminLang = $this->_AdminLang;
	 		
	 		$sql = "SELECT *
	 			FROM " . DB_PREFIX . "menu
	 			WHERE menu_id=$MenuID";
	 		$menuResult = $this->_SqlConnection->SqlQuery($sql);
	 		$menuEntry = mysql_fetch_object($menuResult);
	 		
	 		$out .= "\t\t\t" . sprintf($adminLang['Do you really want to delete the menuentry %menuEntryTitle%?'], $menuEntry->menu_text) . "<br />
			<a href=\"admin.php?page=menueditor&amp;action=deleteSure&amp;menuID=$MenuID&amp;menuMenuID=$MenuMenuID\" class=\"button\">" . $adminLang['yes'] . "</a>
		 	<a href=\"admin.php?page=menueditor\" class=\"button\">" . $adminLang['no'] . "</a>";
	 		
	 		return $out;
	 	}
	 }
?>
