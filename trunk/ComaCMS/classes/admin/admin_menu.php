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
	 		
	 		$out = "\r\n\t\t\t<h2>" . $adminLang['menu-editor'] . "</h2>\r\n";
	 		switch ($Action) {
	 			case 'newEntry':	$out .= $this->_AddMenuEntry(GetPostOrGet('menu_menuid'));
	 						break;
	 			case 'addEntry':	$out .= $this->_Menu->AddMenuEntry(GetPostOrGet('menu_entry_menuid'), GetPostOrGet('menu_entry_title'), GetPostOrGet('menu_entry_link'), GetPostOrGet('menu_entry_css_id'));
	 						$out .= $this->_HomePage();
	 						break;
	 			case 'editEntry':	$out .= $this->_EditMenuEntry(GetPostOrGet('menu_entry_id'));
	 						break;
	 			case 'updateEntry':	$out .= $this->_Menu->UpdateMenuEntry(GetPostOrGet('menu_entry_id'), GetPostOrGet('menu_entry_menuid'), GetPostOrGet('menu_entry_title'), GetPostOrGet('menu_entry_link'), GetPostOrGet('menu_entry_css_id'));
	 						$out .= $this->_HomePage();
	 						break;
	 			case 'up':		$out .= $this->_Menu->ItemMoveUp(GetPostOrGet('menu_entry_orderid'), GetPostOrGet('menu_entry_menuid'));
	 						$out .= $this->_HomePage();
	 						break;
	 			case 'down':		$out .= $this->_Menu->ItemMoveDown(GetPostOrGet('menu_entry_orderid'), GetPostOrGet('menu_entry_menuid'));
	 						$out .= $this->_HomePage();
	 						break;
	 			case 'deleteEntry':	$out .= $this->_DeleteMenuEntry(GetPostOrGet('menu_entry_id'), GetPostOrGet('menu_entry_menuid'));
	 						break;
	 			case 'deleteEntrySure':	$out .= $this->_Menu->DeleteMenuEntry(GetPostOrGet('menu_entry_id'));
	 						$out .= $this->_HomePage();
	 						break;
	 			case 'newMenu':		$out .= $this->_AddMenu();
	 						break;
	 			case 'addMenu':		$out .= $this->_Menu->AddMenu();
	 						$out .= $this->_HomePage(GetPostOrGet('menu_menuid'));
	 						break;
	 			case 'editMenu':	$out .= $this->_EditMenu(GetPostOrGet('enu_menuid'));
	 						break;
	 			case 'deleteMenu':	$out .= $this->_DeleteMenu(GetPostOrGet('menu_menuid'));
	 						break;
	 			default:		$out .= $this->_HomePage(GetPostOrGet('menu_id'));
	 		}
	 		return $out; 
	 	}
	 	
	 	function _HomePage($Menu_menu_id = 1) {
	 		$adminLang = $this->_AdminLang;
	 		$menu_name = '';
	 		
	 		if (!is_numeric($Menu_menu_id))
	 			$Menu_menu_id = 1;
	 		
	 		$out = '';
	 		
	 		$out .= "\t\t\t<fieldset>
				<legend>Optionen</legend>
				<form action=\"admin.php\" method=\"post\">
					<input type=\"hidden\" name=\"page\" value=\"menueditor\" />
					<div class=\"row\">
						<label class=\"row\" for=\"menu_selector\"><strong>Menu:</strong>
 							<span class=\"info\">Hier k&ouml;nnen sie ein Men&uuml; zum bearbeiten w&auml;hlen.</span>
 						</label>
 						<select id=\"menu_selector\" name=\"menu_id\">";
 			$sql = "SELECT *
 				FROM " . DB_PREFIX . "menu";
 			$menuResult = $this->_SqlConnection->SqlQuery($sql);
 			while ($menu = mysql_fetch_object($menuResult)) {
 				$out .= "\r\n\t\t\t\t\t\t\t<option value=\"" . $menu->menu_id . "\"" . (($menu->menu_id == $Menu_menu_id) ? ' selected="selected"' : '') . ">" . $menu->menu_id . ". " . $menu->menu_name . "</option>";
 				if ($menu->menu_id == $Menu_menu_id) {
 					$menu_name = $menu->menu_name;
 				}
 			}
 			$out .= "\r\n\t\t\t\t\t\t</select>
 					</div>
 					<div class=\"row\">
 						<label class=\"row\" for=\"select_button\"><strong>Menu ausw&auml;hlen:</strong>
 							<span class=\"info\">Hier klicken, um das selectierte Men&uuml; zu bearbeiten.</span>
 						</label>
 						<input type=\"submit\" value=\"Ausw&auml;hlen\" class=\"button\" name=\"select_button\" />
 					</div>
 				</form>";
 			if ($Menu_menu_id != 1) { 
 				$out .= "\t\t\t\t<div class=\"row\">
 					<label class=\"row\" for=\"delete_menu_button\"><strong>Men&uuml; l&ouml;schen:</strong>
 						<span class=\"info\">Hier k&ouml;nnen Sie das aktuell ausgew&auml;hlte Men&uuml; l&ouml;schen.</span>
 					</label>
 					<a href=\"admin.php?page=menueditor&amp;action=deleteMenu&amp;menu_menuid=$Menu_menu_id\" class=\"button\">Men&uuml; l&ouml;schen</a>
 				</div>
 				<div class=\"row\">
 					<label class=\"row\" for=\"edit_menu_button\"><strong>Men&uuml; umbenennen:</strong>
 						<span class=\"info\">Hier k&ouml;nnen Sie das aktuelle Men&uuml; umbenennen.</span>
 					</label>
 					<a href=\"admin.php?page=menueditor&amp;action=editMenu&amp;menu_menuid=$Menu_menu_id\" class=\"button\">Men&uuml; umbenennen</a>
 				</div>";
 			}
 			$out .= "\t\t\t\t
 				<div class=\"row\">
 					<label class=\"row\" for=\"new_menu_button\"><strong>neues Men&uuml;:</strong>
 						<span class=\"info\">Hier k&ouml;nnen Sie neue Men&uuml;s erstellen.</span>
 					</label>
 					<a href=\"admin.php?page=menueditor&amp;action=newMenu\" class=\"button\">Neu erstellen</a>
 				</div>
 				<div class=\"row\">
 					<label class=\"row\" for=\"new_menu_entrie\"><strong>neuen Men&uuml;eintrag:</strong>
 						<span class=\"info\">Hier klicken, um dem aktuellen Men&uuml; einen neuen Eintrag zuzuf&uuml;gen.</span>
 					</label>
 					<a href=\"admin.php?page=menueditor&amp;menu_menuid=$Menu_menu_id&amp;action=newEntry\" class=\"button\">" . $adminLang['add_menu_entry'] . "</a>
 				</div>
 			</fieldset>";
	 		
	 		$out .= $this->_ShowMenu($Menu_menu_id, $menu_name);
	 		
	 		return $out;
	 	}
	 	
	 	function _ShowMenu($Menu_entry_id, $Menu_name) {
	 		$out = '';
	 		$adminLang = $this->_AdminLang;
	 		
	 		$sql = "SELECT *
	 			FROM " . DB_PREFIX . "menu_entries
	 			WHERE menu_entries_menuid=$Menu_entry_id
	 			ORDER BY menu_entries_orderid ASC";
	 		$menuResult = $this->_SqlConnection->SqlQuery($sql);
	 		
	 		$numRows = mysql_num_rows($menuResult);
	 		if($numRows > 0)
	 			$out .= "\r\n\t\t\t<fieldset>
	 			<legend>Aktuelles Men&uuml;: $Menu_name</legend>
				<ol>";
	 		
	 		while ($menuEntry = mysql_fetch_object($menuResult)) {
	 			$out .= "\r\n\t\t\t\t\t<li class=\"page_type_text\">
					<span class=\"structure_row\">
						<strong>" . $menuEntry->menu_entries_title . "</strong>	 			
	 					<span class=\"page_actions\">
	 						<a href=\"admin.php?page=menueditor&amp;action=editEntry&amp;menu_entry_id={$menuEntry->menu_entries_id}\"><img src=\"./img/edit.png\" class=\"icon\" alt=\"{$adminLang['edit']}\" title=\"{$adminLang['edit']}\" height=\"16\" width=\"16\" /></a>
	 						<a href=\"admin.php?page=menueditor&amp;action=up&amp;menu_entry_orderid={$menuEntry->menu_entries_orderid}&amp;menu_entry_menuid={$menuEntry->menu_entries_menuid}\"><img src=\"./img/up.png\" class=\"icon\" alt=\"{$adminLang['move_up']}\" title=\"{$adminLang['move_up']}\" height=\"16\" width=\"16\" /></a>
	 						<a href=\"admin.php?page=menueditor&amp;action=down&amp;menu_entry_orderid={$menuEntry->menu_entries_orderid}&amp;menu_entry_menuid={$menuEntry->menu_entries_menuid}\"><img src=\"./img/down.png\" class=\"icon\" alt=\"{$adminLang['move_down']}\" title=\"{$adminLang['move_down']}\" height=\"16\" width=\"16\" /></a>
	 						<a href=\"admin.php?page=menueditor&amp;action=deleteEntry&amp;menu_entry_id={$menuEntry->menu_entries_id}&amp;menu_entry_menuid={$menuEntry->menu_entries_menuid}\"><img src=\"./img/del.png\" class=\"icon\" alt=\"{$adminLang['delete']}\" title=\"{$adminLang['delete']}\" height=\"16\" width=\"16\" /></a>
	 					</span>
	 				</span>
	 			</li>";
	 		}
	 		if($numRows > 0)
	 			$out .= "\r\n\t\t\t\t</ol>
			</fieldset>";
	 		
	 		return $out;
	 	}
	 	
	 	function _AddMenuEntry($Menu_menuid) {
	 		$out = '';
	 		$adminLang = $this->_AdminLang;
	 		$pageStructure = new Pagestructure($this->_SqlConnection, null);
	 		$pageStructure->LoadParentIDs();
	 		
	 		$out .= "\r\n\t\t\t<fieldset>
				<legend>" . $adminLang['add_menu_entry'] . "</legend>
				<form action=\"admin.php\" method=\"post\">
					<input type=\"hidden\" name=\"page\" value=\"menueditor\" />
					<input type=\"hidden\" name=\"action\" value=\"addEntry\" />
					<div class=\"row\">
						<label for=\"menu_entry_id\">
							<strong>{$adminLang['belongs_to_menu']}:</strong>
							<span class=\"info\">" . $adminLang['todo'] . "</span>
						</label>
						<select id=\"menu_entry_id\" name=\"menu_entry_menuid\">";
			$sql = "SELECT *
				FROM " . DB_PREFIX . "menu";
			$menuResult = $this->_SqlConnection->SqlQuery($sql);
			while ($menu = mysql_fetch_object($menuResult)) {
				$out .= "\r\n\t\t\t\t\t\t\t<option value=\"" . $menu->menu_id . "\"" . (($menu->menu_id == $Menu_menuid) ? ' selected="selected"' : '') . ">" . $menu->menu_id . ". " . $menu->menu_name . "</option>";
			}
			
			$out .= "\r\n\t\t\t\t\t\t</select>
					</div>
					<div class=\"row\">
						<label for=\"menu_entry_title\">
							<strong>{$adminLang['menu_entry_title']}:</strong>
							<span class=\"info\">" . $adminLang['todo'] . "</span>
						</label>
						<input type=\"text\" id=\"menu_entry_title\" name=\"menu_entry_title\" />
					</div>
					<div class=\"row\">
						<label for=\"menu_entry_link\">
							<strong>{$adminLang['menu_entry_link']}:</strong>
							<span class=\"info\">" . $adminLang['todo'] . "</span>
						</label>
						<select id=\"menu_entry_link\" name=\"menu_entry_link\">
							<!--<option value=\"0\" selected=\"selected\">externes Ziel</option>-->
							" . $pageStructure->PageStructurePulldown(0, 0, '',  -1) . "
						</select>
					</div>
					<!--<div class=\"row\">
						<label for=\"menu_entry_extern_link\">
							<strong>{$adminLang['menu_entry_extern']}:</strong>
							<span class=\"info\">" . $adminLang['todo'] . "</span>
						</label>
						<input type=\"text\" id=\"menu_entry_extern_link\" name=\"menu_entry_extern_link\" />
					</div>-->
					<div class=\"row\">
						<label for=\"menu_entry_css_id\">
							<strong>{$adminLang['menu_entry_css']}:</strong>
							<span class=\"info\">" . $adminLang['todo'] . "</span>
						</label>
						<input type=\"text\" id=\"menu_entry_css_id\" name=\"menu_entry_css_id\" />
					</div>
					<div class=\"row\">
						<input type=\"reset\" class=\"button\" value=\"{$adminLang['reset']}\" />&nbsp;
						<input type=\"submit\" class=\"button\" value=\"{$adminLang['save']}\" />
					</div>
				</form>
			</fieldset>";
	 		
	 		return $out;
	 	}
	 	
	 	function _EditMenuEntry($Menu_entry_id) {
	 		$out = '';
	 		$adminLang = $this->_AdminLang;
	 		$pageStructure = new Pagestructure($this->_SqlConnection, null);
	 		$pageStructure->LoadParentIDs();
	 		
	 		$sql = "SELECT *
	 			FROM " . DB_PREFIX . "menu_entries
	 			WHERE menu_entries_id=$Menu_entry_id";
	 		$menuResult = $this->_SqlConnection->SqlQuery($sql);
	 		$menuEntry = mysql_fetch_object($menuResult);
	 		
	 		$out .= "\t\t\t<fieldset>
				<legend>" . $adminLang['edit_menu_entry'] . "</legend>
				<form action=\"admin.php\" method=\"post\">
					<input type=\"hidden\" name=\"menu_entry_id\" value=\"$Menu_entry_id\" />
					<input type=\"hidden\" name=\"page\" value=\"menueditor\" />
					<input type=\"hidden\" name=\"action\" value=\"updateEntry\" />
					<div class=\"row\">
						<label for=\"menu_entry_menuid\">
							<strong>{$adminLang['belongs_to_menu']}:</strong>
							<span class=\"info\">" . $adminLang['todo'] . "</span>
						</label>
						<select id=\"menu_entry_menuid\" name=\"menu_entry_menuid\">";
			
			$sql = "SELECT *
				FROM " . DB_PREFIX . "menu";
			$menuResult = $this->_SqlConnection->SqlQuery($sql);
			while ($menu = mysql_fetch_object($menuResult)) {
				$out .= "\r\n\t\t\t\t\t\t\t<option value=\"" . $menu->menu_id . "\"" . (($menu->menu_id == $Menu_entry_id) ? ' selected="selected"' : '') . ">" . $menu->menu_id . ". " . $menu->menu_name . "</option>";
			}
			
			$out .= "\r\n\t\t\t\t\t\t</select>
					</div>
					<div class=\"row\">
						<label for=\"menu_entry_title\">
							<strong>{$adminLang['menu_entry_title']}:</strong>
							<span class=\"info\">" . $adminLang['todo'] . "</span>
						</label>
						<input type=\"text\" id=\"menu_entry_title\" name=\"menu_entry_title\" value=\"" . $menuEntry->menu_entries_title . "\" />
					</div>
					<div class=\"row\">
						<label for=\"menu_entry_link\">
							<strong>{$adminLang['menu_entry_link']}:</strong>
							<span class=\"info\">" . $adminLang['todo'] . "</span>
						</label>
						<select id=\"menu_entry_link\" name=\"menu_entry_link\">
							" . $pageStructure->PageStructurePulldown(0, 0, '',  -1, $menuEntry->menu_entries_page_id) . "
						</select>
					</div>
					<div class=\"row\">
						<label for=\"menu_entry_css_id\">
							<strong>{$adminLang['menu_entry_css']}:</strong>
							<span class=\"info\">" . $adminLang['todo'] . "</span>
						</label>
						<input type=\"text\" id=\"menu_entry_css_id\" name=\"menu_entry_css_id\" value=\"" . $menuEntry->menu_entries_css_id . "\" />
					</div>
					<div class=\"row\">
						<input type=\"reset\" class=\"button\" value=\"" . $adminLang['reset'] . "\" />&nbsp;
						<input type=\"submit\" class=\"button\" value=\"" . $adminLang['save'] . "\" />
					</div>
				</form>
			</fieldset>";
	 		
	 		return $out;
	 	}
	 	
	 	function _DeleteMenuEntry($Menu_entry_id, $Menu_entry_menuid = 1) {
	 		$out = '';
	 		$adminLang = $this->_AdminLang;
	 		
	 		$sql = "SELECT *
	 			FROM " . DB_PREFIX . "menu_entries
	 			WHERE menu_entries_id=$Menu_entry_id";
	 		$menuResult = $this->_SqlConnection->SqlQuery($sql);
	 		$menuEntry = mysql_fetch_object($menuResult);
	 		
	 		$out .= "\t\t\t" . sprintf($adminLang['Do you really want to delete the menuentry %menuEntryTitle%?'], $menuEntry->menu_entries_title) . "<br />
			<a href=\"admin.php?page=menueditor&amp;action=deleteEntrySure&amp;menu_entry_id=$Menu_entry_id\" class=\"button\">" . $adminLang['yes'] . "</a>
		 	<a href=\"admin.php?page=menueditor&amp;menu_id=$Menu_entry_menuid\" class=\"button\">" . $adminLang['no'] . "</a>";
	 		
	 		return $out;
	 	}
	 }
?>
