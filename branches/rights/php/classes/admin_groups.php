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
	 */
	class Admin_Groups {
		
		function GetPage($action, $admin_lang) {
			$out = "\t\t\t<h3>" . $admin_lang['groups'] . "</h3><hr />\r\n";
		 	$action = strtolower($action);
		 	switch ($action) {
		 		case 'add_group':	$out .= $this->addGroup($admin_lang);
		 					break;
		 		case 'new_group':	$out .= $this->newGroup($admin_lang);
		 					break;
		 		case 'delete':	$out .= $this->deleteGroup($admin_lang);
		 					break;
		 		default:		$out .= $this->overwiev($admin_lang);
		 	}
		 	return $out;
		}
		
		function deleteGroup($admin_lang) {
			$group_id = GetPostOrGet('group_id');
			$sure = GetPostOrGet('sure');
			if($sure == 1 && is_numeric($group_id)) {
	 			$sql = "DELETE FROM " . DB_PREFIX . "groups
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
		
		function addGroup($admin_lang) {
			$group_name = GetPostOrGet('group_name');
			$group_manager = GetPostOrGet('group_manager');
			$group_description= GetPostOrGet('group_description');
			//TODO: handle errors
			$sql = "INSERT INTO " . DB_PREFIX . "groups (group_name, group_manager, group_description)
				VALUES ('$group_name', $group_manager, '$group_description')";
			db_result($sql);
			//$out = '';
			header('Location: admin.php?page=groups');
			die();
			
			
		}
		
		function newGroup($admin_lang) {
			$out = "<fieldset>
					<legend>Neue Gruppe erstellen</legend>
					<form method=\"post\" action=\"admin.php\">
						<input type=\"hidden\" name=\"page\" value=\"groups\"/>
						<input type=\"hidden\" name=\"action\" value=\"add_group\"/>
						<table>
							<tr>
								<td>Gruppenname:<span class=\"info\">TODO</span></td>
								<td><input type=\"text\" name=\"group_name\"/></td>
							</tr>
							<tr>
								<td>Beschreibung:<span class=\"info\">TODO</span></td>
								<td><textarea name=\"group_description\"></textarea></td>
							</tr>
							<tr>
								<td>Gruppenleiter:<span class=\"info\">TODO</span></td>
								<td>
									<select name=\"group_manager\">";
			$sql = "SELECT *
				FROM " . DB_PREFIX . "users
				ORDER BY user_name ASC";
			$users_result = db_result($sql);
			while($user = mysql_fetch_object($users_result)) {
				$out.= "<option value=\"$user->user_id\">$user->user_showname</option>\r\n";
			}				
			$out .= "\t\t\t\t\t\t\t</select></td>
							</tr>
							<td colspan=\"2\">
								<input type=\"reset\" class=\"button\" value=\"" . $admin_lang['reset'] . "\" />&nbsp;
								<input type=\"submit\" class=\"button\" value=\"" . $admin_lang['create'] . "\" />
							</td>
						</table>
					<form>
				</fieldset>";
			
			return $out;
		} 
		
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
						<img src=\"./img/edit.png\" class=\"icon\" height=\"16\" width=\"16\" alt=\"" . $admin_lang['edit'] . "\" title=\"" . $admin_lang['edit'] . "\"/>
						<a href=\"admin.php?page=groups&amp;action=delete&amp;group_id=$group->group_id\"><img src=\"./img/del.png\" class=\"icon\" height=\"16\" width=\"16\" alt=\"" . $admin_lang['delete'] . "\" title=\"" . $admin_lang['delete'] . "\"/></a>
					</td>
				</tr>";
			}
			$out .= "\t\t\t</table>";
			
			return $out;
		}
	}
?>