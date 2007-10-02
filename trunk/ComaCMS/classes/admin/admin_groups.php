<?php
/**
 * @package ComaCMS
 * @subpackage AdminInterface
 * @copyright (C) 2005 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : admin_groups.php
 # created              : 2005-12-04
 # copyright            : (C) 2005-2006 The ComaCMS-Team
 # email                : comacms@williblau.de
 #----------------------------------------------------------------------
 # This program is free software; you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation; either version 2 of the License, or
 # (at your option) any later version.
 #----------------------------------------------------------------------
 	
 	/**
 	 * @ignore
 	 */
 	require_once __ROOT__ . '/classes/admin/admin.php';
 	
	/**
	 * @package ComaCMS
	 */
	class Admin_Groups extends Admin{
		
		/**
		 * @param string action
		 * @access public
		 */
		function GetPage($action) {
			$out = "\t\t\t<h3>" . $this->_Translation->GetTranslation('groups') . "</h3><hr />\r\n";
		 	$action = strtolower($action);
		 	switch ($action) {
		 		case 'add_group':	$out .= $this->addGroup();
		 					break;
		 		case 'new_group':	$out .= $this->newGroup();
		 					break;
		 		case 'edit_group':	$out .= $this->editGroup();
		 					break;
		 		case 'save':		$out .= $this->saveGroup();
		 					break;
		 		case 'add_member':	$out .= $this->addMember();
		 					break;
		 		case 'remove_member':	$out .= $this->removeMember();
		 					break;
		 		case 'delete':		$out .= $this->deleteGroup();
		 					break;
		 		default:		$out .= $this->overview();
		 	}
		 	return $out;
		}
		
		/**
		 * @access private
		 */
		function removeMember() {
			$group_id = GetPostOrGet('group_id');
			$user_id = GetPostOrGet('user_id');
			if(is_numeric($group_id) && is_numeric($user_id)) {
				$sql = "SELECT group_manager
					FROM " . DB_PREFIX . "groups
					WHERE group_id = $group_id";
				$group_result = $this->_SqlConnection->SqlQuery($sql);
				if($group = mysql_fetch_object($group_result)) {
					if($group->group_manager != $user_id) {
						$sql = "DELETE FROM " . DB_PREFIX . "group_users
							WHERE group_id = $group_id AND user_id = $user_id";
						$this->_SqlConnection->SqlQuery($sql);
					}
					header("Location: admin.php?page=groups&action=edit_group&group_id=$group_id");
					die();	
				}
			}
		}
		
		/**
		 * @access private
		 */
		function addMember() {
			$group_id = GetPostOrGet('group_id');
			$user_id = GetPostOrGet('user_id');
			if($user_id == '' && is_numeric($group_id)) {
				header("Location: admin.php?page=groups&action=edit_group&group_id=$group_id");
				die();
			}
			if(is_numeric($user_id) && is_numeric($group_id)) {
				$sql = "INSERT INTO " . DB_PREFIX . "group_users (group_id, user_id)
					VALUES ($group_id, $user_id)";
				$this->_SqlConnection->SqlQuery($sql);
				header("Location: admin.php?page=groups&action=edit_group&group_id=$group_id");
				die();	
			}
			header('Location: admin.php?page=groups');
			die();
		}
		/**
		 * @access private
		 */
		function saveGroup() {
			$group_id = GetPostOrGet('group_id');
			if(is_numeric($group_id)) {
				$group_name = GetPostOrGet('group_name');
				$group_manager = GetPostOrGet('group_manager');
				$group_description= GetPostOrGet('group_description');	
				// check for a group which has aleready the group_name which the user decidet to change it into 
				$sql = "SELECT *
					FROM " . DB_PREFIX . "groups
					WHERE group_id != $group_id AND group_name = '$group_name'";
				$check_result = $this->_SqlConnection->SqlQuery($sql);
				if($check = mysql_fetch_object($check_result)) {
					header("Location: admin.php?page=groups&action=edit_group&error=name&group_name=$group_name&group_id=$group_id");
					die();
				}
				$sql = "UPDATE " . DB_PREFIX . "groups
					SET group_name='$group_name', group_manager=$group_manager, group_description='$group_description'
					WHERE group_id=$group_id";
				$this->_SqlConnection->SqlQuery($sql);
			}
			header('Location: admin.php?page=groups');
			die();
		}
		/**
		 * @access private
		 */
		function editGroup() {
			$group_id = GetPostOrGet('group_id');
			if(is_numeric($group_id)) {
				$error = (GetPostOrGet('error') == 'name' );
				$group_name = GetPostOrGet('group_name');
				$sql = "SELECT *
					FROM " . DB_PREFIX . "groups
					WHERE group_id=$group_id";
				$group_result = $this->_SqlConnection->SqlQuery($sql);
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
					$users_result = $this->_SqlConnection->SqlQuery($sql);
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
						<input type=\"submit\" class=\"button\" value=\"" . $this->_Translation->GetTranslation('save') . "\" />
					</div>
				</fieldset>
			</form>
			<form action=\"admin.php\" method=\"post\">
				<input type=\"hidden\" name=\"page\" value=\"groups\" />
				<input type=\"hidden\" name=\"action\" value=\"add_member\" />
				<input type=\"hidden\" name=\"group_id\" value=\"$group_id\" />
				<fieldset>
					<legend>Gruppenmitglieder</legend>
					<table class=\"text_table full_width\">
						<thead>
							<tr>
								<th>Benutzer</th>
								<th>Aktionen</th>
							</tr>
						</thead>";
						$sql = "SELECT user.*, link.*
							FROM ( " . DB_PREFIX. "group_users link
							LEFT JOIN " . DB_PREFIX . "users user ON user.user_id = link.user_id )
							WHERE link.group_id = $group_id";
						$mebmers_result = $this->_SqlConnection->SqlQuery($sql);
						$users_in_group = array();
						while($member = mysql_fetch_object($mebmers_result)) {
							$users_in_group[] = $member->user_id;
							$out .= "<tr>
								<td>$member->user_showname</td>
								<td>" .(($member->user_id != $group->group_manager)? "<a href=\"admin.php?page=groups&amp;action=remove_member&amp;group_id=$group_id&amp;user_id=$member->user_id\"><img src=\"./img/del.png\" class=\"icon\" height=\"16\" width=\"16\" alt=\"" . $this->_Translation->GetTranslation('delete') . "\" title=\"" . $this->_Translation->GetTranslation('delete') . "\"/></a>" : '&nbsp;') . "</td>
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
					$users_result = $this->_SqlConnection->SqlQuery($sql);
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
@access private
		 */
		function deleteGroup() {
			$group_id = GetPostOrGet('group_id');
			$sure = GetPostOrGet('sure');
			if($sure == 1 && is_numeric($group_id)) {
	 			$sql = "DELETE FROM " . DB_PREFIX . "groups
	 				WHERE group_id=$group_id";
				$this->_SqlConnection->SqlQuery($sql);
				$sql = "DELETE FROM " . DB_PREFIX . "group_users
	 				WHERE group_id=$group_id";
				$this->_SqlConnection->SqlQuery($sql);
			}
			elseif(is_numeric($group_id)) {
				$sql = "SELECT *
					FROM " . DB_PREFIX . "groups
					WHERE group_id=$group_id";
				$result = $this->_SqlConnection->SqlQuery($sql);
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
		 * @access private
		 */
		function addGroup() {
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
				$exist_result = $this->_SqlConnection->SqlQuery($sql);
				if($exist = mysql_fetch_object($exist_result)) {
					header("Location: admin.php?page=groups&action=new_group&error=name&group_name=$group_name&group_manager=$group_manager&group_description=$group_description");
					die();
				}
				// create the group 
				$sql = "INSERT INTO " . DB_PREFIX . "groups (group_name, group_manager, group_description)
					VALUES ('$group_name', $group_manager, '$group_description')";
				$this->_SqlConnection->SqlQuery($sql);
				// add the user to the group
				$group_id = mysql_insert_id();
				$sql = "INSERT INTO " . DB_PREFIX . "group_users (group_id, user_id)
					VALUES($group_id, $group_manager)";
				$this->_SqlConnection->SqlQuery($sql);
			}
			header('Location: admin.php?page=groups');
			die();
		}
		
		/**
		 * @access private
		 */
		function newGroup() {
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
			$users_result = $this->_SqlConnection->SqlQuery($sql);
			while($user = mysql_fetch_object($users_result)) {
				$out.= "<option value=\"$user->user_id\">$user->user_showname</option>\r\n";
			}				
			$out .= "\t\t\t\t\t\t\t</select></div>
						<div class=\"row\">
							<input type=\"submit\" class=\"button\" value=\"" . $this->_Translation->GetTranslation('create') . "\" />
						</div>
					</form>
				</fieldset>";
			
			return $out;
		} 
		
		/**
		 * @access private
		 */
		function overview() {
			
			$out = "<a class=\"button\" href=\"admin.php?page=groups&amp;action=new_group\">Neue Gruppe erstellen</a><br />
			\t\t\t<table class=\"text_table full_width margin_center\">
				<thead>
					<tr>
						<th>Gruppenname</th>
						<th>Beschreibung</th>
						<th>Gruppenleiter</th>
						<th>Aktionen</th>
					</tr>
				</thead>\r\n";
			$sql = "SELECT *
				FROM " . DB_PREFIX ."groups
				ORDER BY group_name ASC";
			$group_result = $this->_SqlConnection->SqlQuery($sql);
			while($group = mysql_fetch_object($group_result)) {
				$out .= "\t\t\t\t<tr>
					<td>$group->group_name</td>
					<td>" . nl2br($group->group_description) . "</td>
					<td>" . $this->_ComaLib->GetUserByID($group->group_manager) . "</td>
					<td>
						<a href=\"admin.php?page=groups&amp;action=edit_group&amp;group_id=$group->group_id\"><img src=\"./img/edit.png\" class=\"icon\" height=\"16\" width=\"16\" alt=\"" . $this->_Translation->GetTranslation('edit') . "\" title=\"" . $this->_Translation->GetTranslation('edit') . "\"/></a>
						<a href=\"admin.php?page=groups&amp;action=delete&amp;group_id=$group->group_id\"><img src=\"./img/del.png\" class=\"icon\" height=\"16\" width=\"16\" alt=\"" . $this->_Translation->GetTranslation('delete') . "\" title=\"" . $this->_Translation->GetTranslation('delete') . "\"/></a>
					</td>
				</tr>";
			}
			$out .= "\t\t\t</table>";
			
			return $out;
		}
	}
?>