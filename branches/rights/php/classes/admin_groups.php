<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: admin_groups.php				#
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
	 * @package ComaCMS
	 * @todo manage group-members
	 */
	class Admin_Groups {
		
		/**
		 * @param string action
		 * @param array admin_lang
		 * @access public
		 */
		function GetPage($action, $admin_lang) {
			$out = "\t\t\t<h3>" . $admin_lang['groups'] . "</h3><hr />\r\n";
		 	$action = strtolower($action);
		 	switch ($action) {
		 		case 'add_group':	$out .= $this->addGroup($admin_lang);
		 					break;
		 		case 'new_group':	$out .= $this->newGroup($admin_lang);
		 					break;
		 		case 'edit_group':	$out .= $this->editGroup($admin_lang);
		 					break;
		 		case 'save':		$out .= $this->saveGroup($admin_lang);
		 					break;
		 		case 'add_member':	$out .= $this->addMember($admin_lang);
		 					break;
		 		case 'remove_member':	$out .= $this->removeMember($admin_lang);
		 					break;
		 		case 'delete':		$out .= $this->deleteGroup($admin_lang);
		 					break;
		 		default:		$out .= $this->overwiev($admin_lang);
		 	}
		 	return $out;
		}
		
		/**
		 * @param array admin_lang
		 * @access private
		 */
		function removeMember($admin_lang) {
			$group_id = GetPostOrGet('group_id');
			$user_id = GetPostOrGet('user_id');
			if(is_numeric($group_id) && is_numeric($user_id)) {
				$sql = "SELECT group_manager
					FROM " . DB_PREFIX . "groups
					WHERE group_id = $group_id";
				$group_result = db_result($sql);
				if($group = mysql_fetch_object($group_result)) {
					if($group->group_manager != $user_id) {
						$sql = "DELETE FROM " . DB_PREFIX . "group_users
							WHERE group_id = $group_id AND user_id = $user_id";
						db_result($sql);
					}
					header("Location: admin.php?page=groups&action=edit_group&group_id=$group_id");
					die();	
				}
			}
		}
		
		/**
		 * @param array admin_lang
		 * @access private
		 */
		function addMember($admin_lang) {
			$group_id = GetPostOrGet('group_id');
			$user_id = GetPostOrGet('user_id');
			if($user_id == '' && is_numeric($group_id)) {
				header("Location: admin.php?page=groups&action=edit_group&group_id=$group_id");
				die();
			}
			if(is_numeric($user_id) && is_numeric($group_id)) {
				$sql = "INSERT INTO " . DB_PREFIX . "group_users (group_id, user_id)
					VALUES ($group_id, $user_id)";
				db_result($sql);
				header("Location: admin.php?page=groups&action=edit_group&group_id=$group_id");
				die();	
			}
			header('Location: admin.php?page=groups');
			die();
		}
		/**
		 * @param array admin_lang
		 * @access private
		 */
		function saveGroup($admin_lang) {
			$group_id = GetPostOrGet('group_id');
			if(is_numeric($group_id)) {
				$group_name = GetPostOrGet('group_name');
				$group_manager = GetPostOrGet('group_manager');
				$group_description= GetPostOrGet('group_description');	
				// check for a group which has aleready the group_name which the user decidet to change it into 
				$sql = "SELECT *
					FROM " . DB_PREFIX . "groups
					WHERE group_id != $group_id AND group_name = '$group_name'";
				$check_result = db_result($sql);
				if($check = mysql_fetch_object($check_result)) {
					header("Location: admin.php?page=groups&action=edit_group&error=name&group_name=$group_name&group_id=$group_id");
					die();
				}
				$sql = "UPDATE " . DB_PREFIX . "groups
					SET group_name='$group_name', group_manager=$group_manager, group_description='$group_description'
					WHERE group_id=$group_id";
				db_result($sql);
			}
			header('Location: admin.php?page=groups');
			die();
		}
		/**
		 * @param array admin_lang
		 * @access private
		 */
		function editGroup($admin_lang) {
			$group_id = GetPostOrGet('group_id');
			if(is_numeric($group_id)) {
				$error = (GetPostOrGet('error') == 'name' );
				$group_name = GetPostOrGet('group_name');
				$sql = "SELECT *
					FROM " . DB_PREFIX . "groups
					WHERE group_id=$group_id";
				$group_result = db_result($sql);
				if($group = mysql_fetch_object($group_result)) {
					$out = "\t\t\t<form action=\"admin.php\" method=\"post\">
				<input type=\"hidden\" name=\"page\" value=\"groups\" />
				<input type=\"hidden\" name=\"action\" value=\"save\" />
				<input type=\"hidden\" name=\"group_id\" value=\"$group_id\" />
				<fieldset>
					<legend>Gruppeneigenschaften</legend>
					<div class=\"row\">
						<label class=\"row\" for=\"group_name\">
							Gruppenname:\r\n";
					if($error)
						$out .= "\t\t\t\t\t<span class=\"error\">Der Name &quot;$group_name&quot; ist bereits für eine andere Gruppe Vergeben!</span>\r\n";
					$out .= "\t\t\t\t\t<span class=\"info\">Das ist der Name der Gruppe. Er muss eindeutig sein und kann nicht doppelt vorkommen.</span>
						</label>
						<input type=\"text\" id=\"group_name\" name=\"group_name\" value=\"$group->group_name\"/>
					</div>
					<div class=\"row\">
						<label class=\"row\" for=\"group_description\">
							Beschreibung:
							<span class=\"info\">TODO</span>
						</label>
						<textarea id=\"group_description\" name=\"group_description\">$group->group_description</textarea>
					</div>
					<div class=\"row\">
						<label class=\"row\" for=\"group_manager\">
							Gruppenmanager:
							<span class=\"info\">TODO</span>
						</label>
						<select name=\"group_manager\" id=\"group_manager\">";
					// list all registered users (alphabetic)
					$sql = "SELECT user.*, link.*
						FROM ( " . DB_PREFIX. "group_users link
						LEFT JOIN " . DB_PREFIX . "users user ON user.user_id = link.user_id )
						WHERE link.group_id = $group_id
						ORDER BY user.user_name ASC";
					$users_result = db_result($sql);
					while($user = mysql_fetch_object($users_result)) {
						$out.= "\t\t\t\t\t\t\t<option value=\"$user->user_id\"";
						// select the actual group-manager
						if($user->user_id == $group->group_manager)
							$out.= " selected=\"selected\"";	
						$out.= ">$user->user_showname</option>\r\n";
					}	
					$out .= "\t\t\t\t\t\t</select>
					</div>
					<div class=\"row\">
						<input type=\"submit\" class=\"button\" value=\"" . $admin_lang['save'] . "\" />
						<input type=\"reset\" class=\"button\" value=\"" . $admin_lang['reset'] . "\" />
					</div>
				</fieldset>
			</form>
			<form action=\"admin.php\" method=\"post\">
				<input type=\"hidden\" name=\"page\" value=\"groups\" />
				<input type=\"hidden\" name=\"action\" value=\"add_member\" />
				<input type=\"hidden\" name=\"group_id\" value=\"$group_id\" />
				<fieldset>
					<legend>Gruppenmitglieder</legend>
					<table class=\"tablestyle\">
						<thead>
							<tr>
								<td>Benutzer</td>
								<td>Aktionen</td>
							</tr>
						</thead>";
						$sql = "SELECT user.*, link.*
							FROM ( " . DB_PREFIX. "group_users link
							LEFT JOIN " . DB_PREFIX . "users user ON user.user_id = link.user_id )
							WHERE link.group_id = $group_id";
						$mebmers_result = db_result($sql);
						$users_in_group = array();
						while($member = mysql_fetch_object($mebmers_result)) {
							$users_in_group[] = $member->user_id;
							$out .= "<tr>
								<td>$member->user_showname</td>
								<td>" .(($member->user_id != $group->group_manager)? "<a href=\"admin.php?page=groups&amp;action=remove_member&amp;group_id=$group_id&amp;user_id=$member->user_id\"><img src=\"./img/del.png\" class=\"icon\" height=\"16\" width=\"16\" alt=\"" . $admin_lang['delete'] . "\" title=\"" . $admin_lang['delete'] . "\"/></a>" : '&nbsp;') . "</td>
								</tr>\r\n";
						}
					$out .= "\t\t\t\t\t</table><br />
					<div class=\"row\">
						<label for=\"user_id\" class=\"row\">
							Benutzer hinzufügen:
							<span class=\"info\">Hier können Benutzer zur Gruppe hinzugefügt werden.</span>
						</label>
						<select name=\"user_id\" id=\"user_id\">";
					$sql = "SELECT *
						FROM " . DB_PREFIX . "users
						ORDER BY user_name ASC";
					$users_result = db_result($sql);
					$user_count = 0;
					while($user = mysql_fetch_object($users_result)) {
						if(!in_array($user->user_id, $users_in_group)) {
							$out.= "\t\t\t\t\t\t\t<option value=\"$user->user_id\">$user->user_showname</option>\r\n";
							$user_count++;
						}
					}
					if($user_count == 0)
						$out.= "\t\t\t\t\t\t\t<option value=\"no_user\" selected=\"selected\" disabled=\"disabled\">Keine Benutzer verfügbar</option>\r\n";
					$out .= "\t\t\t\t\t\t</select>
					</div>
					<div class=\"row\">
						<input type=\"submit\" class=\"button\" value=\"Benutzer Hinzufügen\"/>
					</div>
				</fieldset>
				
			</form>";
					return $out;
				}
			}
			header('Location: admin.php?page=groups');
			die();
		}
		
		/**
		 * @param array admin_lang
		 * @access private
		 */
		function deleteGroup($admin_lang) {
			$group_id = GetPostOrGet('group_id');
			$sure = GetPostOrGet('sure');
			if($sure == 1 && is_numeric($group_id)) {
	 			$sql = "DELETE FROM " . DB_PREFIX . "groups
	 				WHERE group_id=$group_id";
				db_result($sql);
				$sql = "DELETE FROM " . DB_PREFIX . "group_users
	 				WHERE group_id=$group_id";
				db_result($sql);
			}
			elseif(is_numeric($group_id)) {
				$sql = "SELECT *
					FROM " . DB_PREFIX . "groups
					WHERE group_id=$group_id";
				$result = db_result($sql);
				if($group = mysql_fetch_object($result)) {
					$out = "Die Gruppe &quot;" . $group->group_name . "&quot; wirklich löschen?<br />
			<a class=\"button\" href=\"admin.php?page=groups&amp;action=delete&amp;group_id=" . $group_id . "&amp;sure=1\" title=\"Wirklich Löschen\">ja</a> &nbsp;&nbsp;&nbsp;&nbsp;
			<a class=\"button\" href=\"admin.php?page=groups\" title=\"Nicht Löschen\">nein</a>";
			
					return $out;
				}
			}
			header('Location: admin.php?page=groups');
			die();
		}
		
		/**
		 * @param array admin_lang
		 * @access private
		 */
		function addGroup($admin_lang) {
			// get the needed vars
			$group_name = GetPostOrGet('group_name');
			$group_manager = GetPostOrGet('group_manager');
			$group_description= GetPostOrGet('group_description');
			if($group_name == '') {
				// go back there is no group name!
				header("Location: admin.php?page=groups&action=new_group&error=empty_name&group_manager=$group_manager&group_description=$group_description");
				die();
			}
			else if(is_numeric($group_manager)) {// is this a valid call?
				// check that there is no group with the same name
				$sql = "SELECT *	
					FROM " . DB_PREFIX . "groups
					WHERE group_name='$group_name'";
				$exist_result = db_result($sql);
				if($exist = mysql_fetch_object($exist_result)) {
					header("Location: admin.php?page=groups&action=new_group&error=name&group_name=$group_name&group_manager=$group_manager&group_description=$group_description");
					die();
				}
				// create the group 
				$sql = "INSERT INTO " . DB_PREFIX . "groups (group_name, group_manager, group_description)
					VALUES ('$group_name', $group_manager, '$group_description')";
				db_result($sql);
				// add the user to the group
				$group_id = mysql_insert_id();
				$sql = "INSERT INTO " . DB_PREFIX . "group_users (group_id, user_id)
					VALUES($group_id, $group_manager)";
				db_result($sql);
			}
			header('Location: admin.php?page=groups');
			die();
		}
		
		/**
		 * @param array admin_lang
		 * @access private
		 */
		function newGroup($admin_lang) {
			// TODO: handle errors!
			$out = "<fieldset>
					<legend>Neue Gruppe erstellen</legend>
					<form method=\"post\" action=\"admin.php\">
						<input type=\"hidden\" name=\"page\" value=\"groups\"/>
						<input type=\"hidden\" name=\"action\" value=\"add_group\"/>
						<div class=\"row\">
							<label class=\"row\" for=\"group_name\">Gruppenname:<span class=\"info\">TODO</span></label>
							<input type=\"text\" id=\"group_name\" name=\"group_name\"/>
						</div>
						<div class=\"row\">
							<label class=\"row\" for=\"group_description\">
								Beschreibung:<span class=\"info\">TODO</span>
							</label>
							<textarea id=\"group_description\" name=\"group_description\"></textarea>
						</div>
						<div class=\"row\">
							<label class=\"row\" for=\"group_manager\">
								Gruppenleiter:<span class=\"info\">TODO</span>
							</label>
							<select id=\"group_manager\" name=\"group_manager\">";
			$sql = "SELECT *
				FROM " . DB_PREFIX . "users
				ORDER BY user_name ASC";
			$users_result = db_result($sql);
			while($user = mysql_fetch_object($users_result)) {
				$out.= "<option value=\"$user->user_id\">$user->user_showname</option>\r\n";
			}				
			$out .= "\t\t\t\t\t\t\t</select></div>
						<div class=\"row\">
							<input type=\"reset\" class=\"button\" value=\"" . $admin_lang['reset'] . "\" />&nbsp;
							<input type=\"submit\" class=\"button\" value=\"" . $admin_lang['create'] . "\" />
						</div>
					</form>
				</fieldset>";
			
			return $out;
		} 
		
		/**
		 * @param array admin_lang
		 * @access private
		 */
		function overwiev($admin_lang) {
			
			$out = "<a class=\"button\" href=\"admin.php?page=groups&amp;action=new_group\">Neue Gruppe erstellen</a><br />
			\t\t\t<table class=\"tablestyle\">
				<thead>
					<tr>
						<td>Gruppenname</td>
						<td>Beschreibung</td>
						<td>Gruppenleiter</td>
						<td>Aktionen</td>
					</tr>
				</thead>\r\n";
			$sql = "SELECT *
				FROM " . DB_PREFIX ."groups
				ORDER BY group_name ASC";
			$group_result = db_result($sql);
			while($group = mysql_fetch_object($group_result)) {
				$out .= "\t\t\t\t<tr>
					<td>$group->group_name</td>
					<td>" . nl2br($group->group_description) . "</td>
					<td>" . getUserByID($group->group_manager) . "</td>
					<td>
						<a href=\"admin.php?page=groups&amp;action=edit_group&amp;group_id=$group->group_id\"><img src=\"./img/edit.png\" class=\"icon\" height=\"16\" width=\"16\" alt=\"" . $admin_lang['edit'] . "\" title=\"" . $admin_lang['edit'] . "\"/></a>
						<a href=\"admin.php?page=groups&amp;action=delete&amp;group_id=$group->group_id\"><img src=\"./img/del.png\" class=\"icon\" height=\"16\" width=\"16\" alt=\"" . $admin_lang['delete'] . "\" title=\"" . $admin_lang['delete'] . "\"/></a>
					</td>
				</tr>";
			}
			$out .= "\t\t\t</table>";
			
			return $out;
		}
	}
?>