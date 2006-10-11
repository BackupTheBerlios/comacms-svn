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
	 	
	 	/**
	 	 * Connection to the MySqlDatabase
	 	 * @access private
	 	 * @var Sql 
	 	 */
	 	var $_SqlConnection;
	 	
	 	/**
	 	 * Menufunktions
	 	 * @access private
	 	 * @var Menu MenuClass
	 	 */
	 	var $_Menu;
	 	
	 	/**
	 	 * Link to the Adminlanguagearray
	 	 * @access private
	 	 * @var AdminLang Language Array for the Adminpages
	 	 */
	 	var $_AdminLang;
	 	
	 	/**
	 	 * Initializes the AdminMenu class
	 	 * @access public
	 	 * @param Sql SqlConnection Connection to the MySqlDatabase
	 	 * @param AdminLang AdminLang Language Array for the Adminpages
	 	 * @package ComaCMS
	 	 * @return void part of the Adminmenu
	 	 */
	 	function Admin_Menu($SqlConnection, $AdminLang) {
	 		$this->_SqlConnection = $SqlConnection;
	 		$this->_AdminLang = $AdminLang;
	 		$this->_Menu = new Menu($this->_SqlConnection);
	 	}
	 	
	 	/**
	 	 * Gets HTML out from the different parts of the Menuengine
	 	 * @access public
	 	 * @param string Action parts name of the Menuengine
	 	 * @return string HTML Code of the menu part
	 	 */
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
	 			case 'addMenu':		$out .= $this->_Menu->AddMenu(GetPostOrGet('menu_title'));
	 						$out .= $this->_HomePage();
	 						break;
	 			case 'editMenu':	$out .= $this->_EditMenu(GetPostOrGet('menu_menuid'));
	 						break;
	 			case 'updateMenu':	$out .= $this->_Menu->UpdateMenu(GetPostOrGet('menu_menuid'), GetPostOrGet('menu_title'));
	 						$out .= $this->_HomePage();
	 						break;
	 			case 'deleteMenu':	$out .= $this->_DeleteMenu(GetPostOrGet('menu_menuid'));
	 						break;
	 			case 'deleteMenuSure':	$out .= $this->_Menu->DeleteMenu(GetPostOrGet('menu_menuid'));
	 						$out .= $this->_HomePage();
	 						break;
	 			default:		$out .= $this->_HomePage(GetPostOrGet('menu_id'));
	 		}
	 		return $out; 
	 	}
	 	
	 	/**
	 	 * Returs the HomePage of the menueditor
	 	 * @access private
	 	 * @param integer Menu_menuid Gives the actual menu_id to the function
	 	 * @return string The ready HTML Code of the menu HomePage
	 	 */
	 	function _HomePage($Menu_menuid = 1) {
	 		$adminLang = $this->_AdminLang;
	 		$menu_name = '';
	 		
	 		if (!is_numeric($Menu_menuid))
	 			$Menu_menuid = 1;
	 		
	 		$out = '';
	 		
	 		$out .= "\t\t\t<fieldset>
				<legend>{$adminLang['options']}</legend>
				<form action=\"admin.php\" method=\"post\">
					<input type=\"hidden\" name=\"page\" value=\"menueditor\" />
					<div class=\"row\">
						<label class=\"row\" for=\"menu_selector\"><strong>{$adminLang['menu']}:</strong>
 							<span class=\"info\">{$adminLang['here_you_can_choose_a_menu_to_edit']}.</span>
 						</label>
 						<select id=\"menu_selector\" name=\"menu_id\">";
 			$sql = "SELECT *
 				FROM " . DB_PREFIX . "menu";
 			$menuResult = $this->_SqlConnection->SqlQuery($sql);
 			$zaehler = 0;
 			while ($menu = mysql_fetch_object($menuResult)) {
 				$zaehler += 1;
 				$out .= "\r\n\t\t\t\t\t\t\t<option value=\"" . $menu->menu_id . "\"" . (($menu->menu_id == $Menu_menuid) ? ' selected="selected"' : '') . ">$zaehler. {$menu->menu_name}</option>";
 				if ($menu->menu_id == $Menu_menuid) {
 					$menu_name = $menu->menu_name;
 				}
 			}
 			$out .= "\r\n\t\t\t\t\t\t</select>
 					</div>
 					<div class=\"row\">
 						<label class=\"row\" for=\"select_button\"><strong>{$adminLang['choose_menu']}:</strong>
 							<span class=\"info\">{$adminLang['click_here_to_edit_the_actual_menu']}.</span>
 						</label>
 						<input type=\"submit\" value=\"{$adminLang['choose_menu']}\" class=\"button\" name=\"select_button\" />
 					</div>
 				</form>
 				<div class=\"row\">
 					<label class=\"row\" for=\"new_menu_button\"><strong>{$adminLang['new_menu']}:</strong>
 						<span class=\"info\">{$adminLang['here_you_can_create_a_new_menu']}.</span>
 					</label>
 					<a href=\"admin.php?page=menueditor&amp;action=newMenu\" class=\"button\">{$adminLang['create_new']}</a>
 				</div>";
 			if ($Menu_menuid != 1) { 
 				$out .= "\t\t\t\t<div class=\"row\">
 					<label class=\"row\" for=\"edit_menu_button\"><strong>{$adminLang['edit_menu']}:</strong>
 						<span class=\"info\">{$adminLang['here_you_can_edit_the_actual_menu']}.</span>
 					</label>
 					<a href=\"admin.php?page=menueditor&amp;action=editMenu&amp;menu_menuid=$Menu_menuid\" class=\"button\">{$adminLang['edit_menu']}</a>
 				</div>
 				<div class=\"row\">
 					<label class=\"row\" for=\"delete_menu_button\"><strong>{$adminLang['delete_menu']}:</strong>
 						<span class=\"info\">{$adminLang['here_you_can_delete_the_actual_menu']}.</span>
 					</label>
 					<a href=\"admin.php?page=menueditor&amp;action=deleteMenu&amp;menu_menuid=$Menu_menuid\" class=\"button\">{$adminLang['delete_menu']}</a>
 				</div>";
 			}
 			$out .= "\t\t\t\t
 				<div class=\"row\">
 					<label class=\"row\" for=\"new_menu_entrie\"><strong>{$adminLang['add_menu_entry']}:</strong>
 						<span class=\"info\">{$adminLang['here_you_can_add_a_new_menu_entry_to_the_actual_menu']}.</span>
 					</label>
 					<a href=\"admin.php?page=menueditor&amp;menu_menuid=$Menu_menuid&amp;action=newEntry\" class=\"button\">{$adminLang['add_menu_entry']}</a>
 				</div>
 			</fieldset>";
	 		
	 		$out .= $this->_ShowMenu($Menu_menuid, $menu_name);
	 		
	 		return $out;
	 	}
	 	
	 	/**
	 	 * Returns the entries of the selected Menu
	 	 * @access private
	 	 * @param integer Menu_menuid The id of the selected Menu
	 	 * @param string Menu_name The name of the selected Menu
	 	 * @return string HTML list of the entries defined in the selected Menu
	 	 */
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
	 			<legend>{$adminLang['menu_actual']}: $Menu_name</legend>
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
	 	
	 	/**
	 	 * Returns a form to add a new Menuentry to the selected Menu
	 	 * @access private
	 	 * @param integer Menu_menuid The id of the selected Menu
	 	 * @return string HTML code of an addForm
	 	 */
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
							<span class=\"info\">{$adminLang['this_is_the_menu_the_new_entry_should_be_added_to']}.</span>
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
							<span class=\"info\">{$adminLang['this_is_the_title_of_the_menuentry_that_will_be_shown_in_the_menu']}.</span>
						</label>
						<input type=\"text\" id=\"menu_entry_title\" name=\"menu_entry_title\" />
					</div>
					<div class=\"row\">
						<label for=\"menu_entry_link\">
							<strong>{$adminLang['menu_entry_link']}:</strong>
							<span class=\"info\">{$adminLang['choose_here_the_page_to_which_the_link_should_refer']}</span>
						</label>
						<select id=\"menu_entry_link\" name=\"menu_entry_link\">
							" . $pageStructure->PageStructurePulldown(0, 0, '',  -1) . "
						</select>
					</div>
					<div class=\"row\">
						<label for=\"menu_entry_css_id\">
							<strong>{$adminLang['menu_entry_css']}:</strong>
							<span class=\"info\">{$adminLang['type_in_here_the_css_id_for_the_menuentry_if_you_need_one_for_it']}</span>
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
	 	
	 	/**
	 	 * Returns a form to edit a menuentry
	 	 * @access private
	 	 * @param integer Menu_entry_id The id of the menuentry to edit
	 	 * @return string HTML code of the form
	 	 */
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
							<span class=\"info\">{$adminLang['this_is_the_title_of_the_menuentry_that_will_be_shown_in_the_menu']}</span>
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
							<span class=\"info\">{$adminLang['this_is_the_title_of_the_menuentry_that_will_be_shown_in_the_menu']}</span>
						</label>
						<input type=\"text\" id=\"menu_entry_title\" name=\"menu_entry_title\" value=\"" . $menuEntry->menu_entries_title . "\" />
					</div>
					<div class=\"row\">
						<label for=\"menu_entry_link\">
							<strong>{$adminLang['menu_entry_link']}:</strong>
							<span class=\"info\">{$adminLang['choose_here_the_page_to_which_the_link_should_refer']}</span>
						</label>
						<select id=\"menu_entry_link\" name=\"menu_entry_link\">
							" . $pageStructure->PageStructurePulldown(0, 0, '',  -1, $menuEntry->menu_entries_page_id) . "
						</select>
					</div>
					<div class=\"row\">
						<label for=\"menu_entry_css_id\">
							<strong>{$adminLang['menu_entry_css']}:</strong>
							<span class=\"info\">{$adminLang['type_in_here_the_css_id_for_the_menuentry_if_you_need_one_for_it']}</span>
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
	 	
	 	/**
	 	 * Make sure, that the menuentry should really be deleted
	 	 * @access private
	 	 * @param integer Menu_entry_id id of the menuentry that should be deleted
	 	 * @param integer Menu_entry_menuid id of the actual selected Menu to go back there afterwards
	 	 * @return string HTML code
	 	 */
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
	 	
	 	/**
	 	 * Form to add a new Menu
	 	 * @access private
	 	 * @return string HTML code of the form
	 	 */
	 	function _AddMenu() {
	 		$out = '';
	 		$adminLang = $this->_AdminLang;
	 		
	 		$out .= "\r\n\t\t\t<fieldset>
				<legend>Ein neues Men&uuml; hinzuf&uuml;gen</legend>
				<form action=\"admin.php\" method=\"post\">
					<input type=\"hidden\" name=\"page\" value=\"menueditor\" />		
					<input type=\"hidden\" name=\"action\" value=\"addMenu\" />
					<div class=\"row\">
						<label for=\"menu_title\">
							<strong>{$adminLang['menu_title']}:</strong>
							<span class=\"info\">{$adminLang['type_here_the_title_of_the_menu']}.</span>
						</label>
						<input type=\"text\" id=\"menu_title\" name=\"menu_title\" />
					</div>
					<div class=\"row\">
						<input type=\"reset\" class=\"button\" value=\"{$adminLang['reset']}\" />&nbsp;
						<input type=\"submit\" class=\"button\" value=\"{$adminLang['save']}\" />
					</div>
				</form>
			</fieldset>";
			
			return $out;
	 	}
	 	
	 	/**
	 	 * Form to edit a new Menu
	 	 * @access private
	 	 * @param integer Menu_menuid The id of the menu to edit
	 	 * @return string HTML code of the form
	 	 * @todo Information about DEFAULT menu
	 	 */
	 	function _editMenu($Menu_menuid) {
	 		$out = '';
	 		$adminLang = $this->_AdminLang;
	 		
	 		$sql = "SELECT *
	 			FROM " . DB_PREFIX . "menu
	 			WHERE menu_id=$Menu_menuid";
	 		$menuResult = $this->_SqlConnection->SqlQuery($sql);
	 		
	 		if ($menu = mysql_fetch_object($menuResult)) {
	 			$out .= "\r\n\t\t\t<fieldset>
				<legend>Ein neues Men&uuml; hinzuf&uuml;gen</legend>
				<form action=\"admin.php\" method=\"post\">
					<input type=\"hidden\" name=\"page\" value=\"menueditor\" />		
					<input type=\"hidden\" name=\"action\" value=\"updateMenu\" />
					<input type=\"hidden\" name=\"menu_menuid\" value=\"$Menu_menuid\" />		
					<div class=\"row\">
						<label for=\"menu_title\">
							<strong>Men&uuml;titel:</strong>
							<span class=\"info\">{$adminLang['type_here_the_title_of_the_menu']}.</span>
						</label>
						<input type=\"text\"" . (($Menu_menuid == 1) ? ' disabled="disabled"' : '') . " id=\"menu_title\" name=\"menu_title\" value=\"{$menu->menu_name}\" />
					</div>
					<div class=\"row\">
						<input type=\"reset\" class=\"button\" value=\"{$adminLang['reset']}\" />&nbsp;
						<input type=\"submit\" class=\"button\" value=\"{$adminLang['save']}\" />
					</div>
				</form>
			</fieldset>";
	 		}
	 		
			return $out;
	 	}
	 	
	 	/**
	 	 * Question if a menu should really be deleted
	 	 * @access private
	 	 * @param integer Menu_menuid The id of the menu that should be deleted
	 	 * @return string HTML code
	 	 */
	 	function _deleteMenu($Menu_menuid) {
	 		$out = '';
	 		$adminLang = $this->_AdminLang;
	 		
	 		if (is_numeric($Menu_menuid) && $Menu_menuid != 1) {
	 			$sql = "SELECT *
	 				FROM " . DB_PREFIX . "menu
	 				WHERE menu_id='$Menu_menuid'";
	 			$menuResult = $this->_SqlConnection->SqlQuery($sql);
	 			
	 			if ($menu = mysql_fetch_object($menuResult)) {
	 				$out .= "\r\n\t\t\t" . sprintf($adminLang['shall_the_menu_%menutitle%_really_be_deleted?'], $menu->menu_name) . "<br />
			<a href=\"admin.php?page=menueditor&amp;action=deleteMenuSure&amp;menu_menuid=$Menu_menuid\" class=\"button\">{$adminLang['yes']}</a>
			<a href=\"admin.php?page=menueditor&amp;menu_id=$Menu_menuid\" class=\"button\">{$adminLang['no']}</a>";
	 			}
	 			else {
	 				$out .= "Men&uuml; konnte nicht gefunden werden. <br /> <a href=\"admin.php?page=menueditor&amp;menu_id=1\" class=\"button\">{$adminLang['back']}</a>";
	 			}
	 		}
	 		elseif  ($Menu_menuid == 1) {
	 			$out .= "Das DEFAULT Men&uuml; kann aus technischen Gr&uuml;nden nicht gel&ouml;scht werden. <br />
					<a href=\"admin.php?page=menueditor&amp;menu_id=1\" class=\"button\">{$adminLang['back']}</a>";
	 		} 
			
			return $out;
	 	}
	 }
?>
