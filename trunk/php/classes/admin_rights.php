<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: admin_rights.php				#
 # created		: 2005-12-04					#
 # copyright		: (C) 2005 The ComaCMS-Team			#
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
 	 require_once('./classes/auth_user.php');
 	 require_once('./classes/auth_group.php');
 	/**
	 * @package ComaCMS
	 */
	class Admin_Rights {
		
		/**
		 * @param string action
		 * @param array admin_lang
		 * @access public
		 */
		function GetPage($action, $admin_lang) {
			$out = "\t\t\t<h3>" . $admin_lang['rights'] . "</h3><hr />\r\n";
		 	$action = strtolower($action);
		 	switch ($action) {
		 		case 'reset_user':	$out .= $this->_resetUser($admin_lang);
		 					break;
		 		case 'edit_user':	$out .= $this->editUser($admin_lang);
		 					break;
		 		case 'edit_group':	$out .= $this->editGroup($admin_lang);
		 					break;
		 		case 'save_user':	$out .= $this->saveUser($admin_lang);
		 					break;
		 		case 'save_group':	$out .= $this->saveGroup($admin_lang);
		 					break;
		 		default:		$out .= $this->_overview($admin_lang);
		 	}
		 	return $out;
		}
		
		/**
		 * @param array admin_lang
		 * @access private
		 */
		function _resetUser($admin_lang) {
			$userID = GetPostOrGet('user_id');
			if(is_numeric($userID)) {
				$sql = "DELETE FROM " . DB_PREFIX . "auth
					WHERE auth_group_id = 0
					AND auth_user_id = $userID
					AND auth_page_id = 0";
				db_result($sql);
			}
			return $this->_overview($admin_lang);
		}
		
		/**
		 * @param array admin_lang
		 * @access private
		 */
		function saveUser($admin_lang) {
			$user_id = GetPostOrGet('user_id');
			if(is_numeric($user_id)) {
				$auth = new User_Auth($user_id);
				if(!$auth->is_admin) {
					$auth_view = GetPostOrGet('auth_view');
					$auth_edit = GetPostOrGet('auth_edit');
					$auth_delete = GetPostOrGet('auth_delete');
					$auth_new_sub = GetPostOrGet('auth_new_sub');
					$auth->view = ($auth_view == 'true');
					$auth->edit = ($auth_edit == 'true');
					$auth->delete = ($auth_delete == 'true');
					$auth->new_sub = ($auth_new_sub == 'true');
					$auth->Save();
				}		
			}
			header('Location: admin.php?page=rights');
			die();
		}
		
		function saveGroup($admin_lang) {
			$group_id = GetPostOrGet('group_id');
			if(is_numeric($group_id)) {
				
				$auth = new Group_Auth($group_id);
				$auth_view = GetPostOrGet('auth_view');
				$auth_edit = GetPostOrGet('auth_edit');
				$auth_delete = GetPostOrGet('auth_delete');
				$auth_new_sub = GetPostOrGet('auth_new_sub');
				$auth->view = ($auth_view == 'true');
				$auth->edit = ($auth_edit == 'true');
				$auth->delete = ($auth_delete == 'true');
				$auth->new_sub = ($auth_new_sub == 'true');
				$auth->Save();
						
			}
			header('Location: admin.php?page=rights');
			die();
		}
		
		function editGroup($admin_lang) {
			$group_id = GetPostOrGet('group_id');
			if(is_numeric($group_id)) {
				
			
			$auth = new Group_Auth($group_id);
			
			$edit_for_str = sprintf($admin_lang['default_rights_for_the_group_%group%'], getGroupByID($group_id));	
				
			$out = "\t\t\t<form action=\"admin.php\" method=\"post\">
				<input type=\"hidden\" name=\"page\" value=\"rights\"/>
				<input type=\"hidden\" name=\"action\" value=\"save_group\"/>
				<input type=\"hidden\" name=\"group_id\" value=\"$group_id\"/>
				<fieldset>
					<legend>$edit_for_str</legend>
					<div class=\"row\">
						<label class=\"row\">
							" . $admin_lang['view_right'] . ":
							<span class=\"info\">
									" . $admin_lang['todo'] . "
							</span>
						</label>
						<div><input type=\"checkbox\" class=\"checkbox\" name=\"auth_view\" value=\"true\" " . (($auth->view) ? 'checked="checked"' : '') . "/></div>
					</div>
					<div class=\"row\">
						<label class=\"row\">
							" . $admin_lang['edit_right'] . ":
							<span class=\"info\">
									" . $admin_lang['todo'] . "
							</span>
						</label>
						<div><input type=\"checkbox\" class=\"checkbox\" name=\"auth_edit\" value=\"true\" " . (($auth->edit) ? 'checked="checked"' : '') . "/></div>
					</div>
					<div class=\"row\">
						<label class=\"row\">
							" . $admin_lang['delete_right'] . ":
							<span class=\"info\">
									" . $admin_lang['todo'] . "
							</span>
						</label>
						<div><input type=\"checkbox\" class=\"checkbox\" name=\"auth_delete\" value=\"true\" " . (($auth->delete) ? 'checked="checked"' : '') . "/></div>
					</div>
					<div class=\"row\">
						<label class=\"row\">
							" . $admin_lang['add_new_subpage_right'] . ":
							<span class=\"info\">
									" . $admin_lang['todo'] . "
							</span>
						</label>
						<div><input type=\"checkbox\" class=\"checkbox\" name=\"auth_new_sub\" value=\"true\" " . (($auth->new_sub) ? 'checked="checked"' : '') . "/></div>
					</div>
					<div class=\"row\">
						<input type=\"submit\" class=\"button\" value=\"" . $admin_lang['apply'] . "\" />
						<input type=\"reset\" class=\"button\" value=\"" . $admin_lang['reset'] . "\" />
					</div>
				</fieldset>
			</form>";
			
				return $out;
			}
			else {
				header('Location: admin.php?page=rights');
				die();	
			}
		}
		
		function editUser($admin_lang) {
			$user_id = GetPostOrGet('user_id');
			if(!is_numeric($user_id)) {
				$user_id = 0;
			}
			$auth = new User_Auth($user_id);
			$edit_for_str = $admin_lang['default_rights_for_users_and_groups']; 
			if($user_id != 0)
				$edit_for_str = sprintf($admin_lang['default_rights_for_%user%'], getUserByID($user_id));	
				
			$out = "\t\t\t<form action=\"admin.php\" method=\"post\">
				<input type=\"hidden\" name=\"page\" value=\"rights\"/>
				<input type=\"hidden\" name=\"action\" value=\"save_user\"/>
				<input type=\"hidden\" name=\"user_id\" value=\"$user_id\"/>
				<fieldset>
					<legend>$edit_for_str</legend>
					<div class=\"row\">
						<label class=\"row\">
							" . $admin_lang['view_right'] . ":
							<span class=\"info\">
									" . $admin_lang['todo'] . "
							</span>
						</label>
						<div><input type=\"checkbox\" class=\"checkbox\" name=\"auth_view\" value=\"true\" " . (($auth->view) ? 'checked="checked"' : '') . (($auth->is_admin) ? ' disabled="disabled"' : '') . "/></div>
					</div>
					<div class=\"row\">
						<label class=\"row\">
							" . $admin_lang['edit_right'] . ":
							<span class=\"info\">
									" . $admin_lang['todo'] . "
							</span>
						</label>
						<div><input type=\"checkbox\" class=\"checkbox\" name=\"auth_edit\" value=\"true\" " . (($auth->edit) ? 'checked="checked"' : '') . (($auth->is_admin) ? ' disabled="disabled"' : '') . "/></div>
					</div>
					<div class=\"row\">
						<label class=\"row\">
							" . $admin_lang['delete_right'] . ":
							<span class=\"info\">
									" . $admin_lang['todo'] . "
							</span>
						</label>
						<div><input type=\"checkbox\" class=\"checkbox\" name=\"auth_delete\" value=\"true\" " . (($auth->delete) ? 'checked="checked"' : '') . (($auth->is_admin) ? ' disabled="disabled"' : '') . "/></div>
					</div>
					<div class=\"row\">
						<label class=\"row\">
							" . $admin_lang['add_new_subpage_right'] . ":
							<span class=\"info\">
									" . $admin_lang['todo'] . "
							</span>
						</label>
						<div><input type=\"checkbox\" class=\"checkbox\" name=\"auth_new_sub\" value=\"true\" " . (($auth->new_sub) ? 'checked="checked"' : '') . (($auth->is_admin) ? ' disabled="disabled"' : '') . "/></div>
					</div>
					<div class=\"row\">
						<input type=\"submit\" class=\"button\" value=\"" . $admin_lang['apply'] . "\" />
						<input type=\"reset\" class=\"button\" value=\"" . $admin_lang['reset'] . "\" />
						" .(($auth->has_own) ? "<a href=\"admin.php?page=rights&amp;action=reset_user&amp;user_id=$user_id\" class=\"button\">Eintrag entfernen</a>" : '') . "
					</div>
				</fieldset>
			</form>";
			
			return $out;
		}
		
		function usersOverview($admin_lang) {
			$out = "\t\t\t<form action=\"admin.php\" method=\"post\">
				<input type=\"hidden\" name=\"page\" value=\"rights\">
				<input type=\"hidden\" name=\"action\" value=\"edit_user\">
				<fieldset>
					<legend>" . $admin_lang['select_user'] . "</legend>
					<div class=\"row\">
						<label class=\"row\" for=\"user_id\">
							" . $admin_lang['user'] . ":
							<span class=\"info\">
									" . $admin_lang['the_rights_of_which_user_should_be_edited?'] . "
							</span>
						</label>
						<div>
							<select name=\"user_id\" id=\"user_id\">\r\n";
			$sql = "SELECT *
				FROM " . DB_PREFIX . "users
				ORDER BY user_name ASC";
			$users_result = db_result($sql);
			while($user = mysql_fetch_object($users_result)) {
				$out.= "\t\t\t\t\t\t\t\t<option value=\"$user->user_id\">$user->user_showname</option>\r\n";
			}	
			$out .= "\t\t\t\t\t\t\t</select>
						</div>
					</div>
					<div class=\"row\">
						<input type=\"submit\" class=\"button\" value=\"" . $admin_lang['edit'] . "\" />
					</div>
					<div class=\"row\">
						<a href=\"admin.php?page=rights&amp;action=edit_user\">" . $admin_lang['default_rights_for_users'] . "</a>
					</div>
				</fieldset>
			</form>";
			
			return $out;
		}
		
		function _overview($admin_lang) {
			$out = "\t\t\t<form action=\"admin.php\" method=\"post\">
				<input type=\"hidden\" name=\"page\" value=\"rights\" />
				<input type=\"hidden\" name=\"action\" value=\"edit_user\" />
				<fieldset>
					<legend>" . $admin_lang['user_rights'] . "</legend>
					
					<div class=\"row\">
						<label class=\"row\" for=\"user_id\">
							" . $admin_lang['user'] . ":
							<span class=\"info\">
								" . $admin_lang['the_rights_of_which_user_should_be_edited?'] . "
							</span>
						</label>
						<div>
							<select name=\"user_id\" id=\"user_id\">\r\n";
			$sql = "SELECT *
				FROM " . DB_PREFIX . "users
				ORDER BY user_name ASC";
			$users_result = db_result($sql);
			while($user = mysql_fetch_object($users_result)) {
				$out.= "\t\t\t\t\t\t\t\t<option value=\"$user->user_id\">$user->user_showname</option>\r\n";
			}	
			$out .= "\t\t\t\t\t\t\t</select>
						</div>
					</div>
					<div class=\"row\">
						<input type=\"submit\" class=\"button\" value=\"" . $admin_lang['edit'] . "\" />
					</div>
				</fieldset>
			</form>
			<form action=\"admin.php\" method=\"post\">
				<input type=\"hidden\" name=\"page\" value=\"rights\" />
				<input type=\"hidden\" name=\"action\" value=\"edit_group\" />
				<fieldset>
					<legend>" . $admin_lang['group_rights'] . "</legend>
					<div class=\"row\">
						<label class=\"row\" for=\"group_id\">
							" . $admin_lang['group'] . ":
							<span class=\"info\">
								" . $admin_lang['the_rights_of_which_group_should_be_edited?'] . "
							</span>
						</label>
						<div>
							<select name=\"group_id\" id=\"group_id\">\r\n";
			$sql = "SELECT *
				FROM " . DB_PREFIX . "groups
				ORDER BY group_name ASC";
			$group_result = db_result($sql);
			while($group = mysql_fetch_object($group_result)) {
				$out.= "\t\t\t\t\t\t\t\t<option value=\"$group->group_id\">$group->group_name</option>\r\n";
			}	
			$out .= "\t\t\t\t\t\t\t</select>
						</div>
					</div>
					<div class=\"row\">
						<input type=\"submit\" class=\"button\" value=\"" . $admin_lang['edit'] . "\" />
					</div>
				</fieldset>
			</form>
			<a href=\"admin.php?page=rights&amp;action=edit_user\" class=\"button\">" . $admin_lang['edit_default_rights_for_users_and_groups'] . "</a>
			
				<!--</li>
				<li><a href=\"admin.php?page=rights&amp;action=groups\">" . $admin_lang['group_rights'] . "</a>
					<span class=\"info\">bla</span>
				</li>
				<li><a href=\"admin.php?page=rights&amp;action=pages\">" . $admin_lang['page_rights'] . "</a>
						<span class=\"info\">bla</span>
				</li>
			</ul>-->";
			return $out;
		}
		
	}
?>