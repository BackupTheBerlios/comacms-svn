<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2006 The ComaCMS-Team
 * This file contains (nearly) all subsites in the admin-interface
 */
 #----------------------------------------------------------------------
 # file                 : admin_pages.php
 # created              : 2005-07-12
 # copyright            : (C) 2005-2006 The ComaCMS-Team
 # email                : comacms@williblau.de
 #----------------------------------------------------------------------
 # This program is free software; you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation; either version 2 of the License, or
 # (at your option) any later version.
 #----------------------------------------------------------------------

	
/**
 *
 * string page_users()
 * returns the user-admin-page where you can add, change and delete users
 *
 */
	function page_users() {
		global $_GET, $_POST, $PHP_SELF, $translation, $actual_user_id, $actual_user_passwd_md5,$actual_user_online_id, $actual_user_online_id, $_SERVER, $user;
	
		$out  ="";
	
		if(isset($_GET['action']) || isset($_POST['action'])) {
			if(isset($_GET['action']))
				$action = $_GET['action'];
			else
				$action = $_POST['action'];
			$user_id = GetPostOrGet('user_id', 0);
			$user_name = GetPostOrGet('user_name', '');
			$user_showname = GetPostOrGet('user_showname', '');
			$user_email = GetPostOrGet('user_email', '');
			$user_icq = GetPostOrGet('user_icq', '');
			$user_admin = GetPostOrGet('user_admin', '');
			$user_password = GetPostOrGet('user_password', '');
			$user_password_confirm = GetPostOrGet('user_password_confirm' ,'');
			
			if($action == "add") {
				if($user_name == "" || $user_showname == "" || $user_password == "" || $user_password != $user_password_confirm)
					$action = "add-error";
				elseif($user_email != "" && !isEMailAddress($user_email)) 
					$action = "add-error";
				elseif($user_icq != "" && !isIcqNumber($user_icq)) 
					$action = "add-error";
				else {
					if($user_admin == "on")
						$user_admin = "y";
					else
						$user_admin = "n";
					$user_icq = str_replace("-", "", $user_icq);
					$user_password = md5($user_password);
					$sql = "INSERT INTO " . DB_PREFIX . "users
						(user_showname, user_name, user_password, user_registerdate, user_admin, user_icq, user_email)
						VALUES ('$user_showname', '$user_name', '$user_password', '" . mktime() . "', '$user_admin', '$user_icq', '$user_email')";
					db_result($sql);
				}
			}
			elseif($action == "save") {
				if($user_name == "" || $user_showname == "" || $user_password != $user_password_confirm)
					$action = "save-error";
				elseif($user_email != "" && !isEMailAddress($user_email))
					$action = "save-error";
				elseif($user_icq != "" && !isIcqNumber($user_icq))
					$action = "save-error";
				else {
					if($user_password != "")
						$user_password = ", user_password= '".md5($user_password)."'";
					if($user_admin == "on")
						$user_admin = "user_admin= 'y', ";
					else
						$user_admin = "user_admin= 'n', ";
					$user_icq = str_replace("-", "", $user_icq);
					if($user_id == $user->ID) {
						if($user_password_confirm != "")
							$actual_user_passwd_md5 = md5($user_password_confirm);
						$actual_user_name = $user_name;
						setcookie("CMS_user_cookie",$actual_user_online_id."|".$actual_user_name."|".$actual_user_passwd_md5 , time() + 14400);
					}
					$sql = "UPDATE " . DB_PREFIX . "users
					SET user_showname='$user_showname', user_name='$user_name', user_email='$user_email', $user_admin user_icq='$user_icq'$user_password
					WHERE user_id=$user_id";
					db_result($sql);
				}
			}
			elseif($action == "delete") {
				if(isset($_GET['sure']) || isset($_POST['sure'])) {
					if(isset($_GET['sure']))
						$sure = $_GET['sure'];
					else
						$sure = $_POST['sure'];
					
					if($sure == 1 && $user_id != $user->ID) {
						$sql = "SELECT *
							FROM " . DB_PREFIX . "users
							WHERE user_id=$user_id";
						$result = db_result($sql);
						$user_data = mysql_fetch_object($result);
						$sql = "DELETE FROM " . DB_PREFIX . "users
							WHERE user_id=$user_id";
						db_result($sql);
						$out .= "Der Benutzer &quot;" . $user_data->user_showname . "&quot; ist nun unwiederuflich gel&ouml;scht worden!<br />";
					}
				}
				else {
					$sql = "SELECT *
						FROM " . DB_PREFIX . "users
						WHERE user_id=$user_id";
					$result = db_result($sql);
					$user = mysql_fetch_object($result);
					$out .= "Den Benutzer &quot;" . $user->user_showname . "&quot; unwiederruflich l&ouml;schen?<br />
				<a href=\"admin.php?page=users&amp;action=delete&amp;user_id=" . $user_id . "&amp;sure=1\" title=\"Wirklich L&ouml;schen\" class=\"button\">" . $translation->GetTranslation('yes') . "</a>
				<a href=\"admin.php?page=users\" title=\"Nicht L&ouml;schen\" class=\"button\">" . $translation->GetTranslation('no') . "</a>";
					
					return $out;
				}
			}
			if($action == "edit" || $action == "new" || $action == "add-error" || $action == "save-error") {
				if($user_id != 0 || $action == "new" || $action == "add-error" || $action == "save-error") {
					if($user_id != 0) {
						$sql = "SELECT *
							FROM " . DB_PREFIX . "users
							WHERE user_id=$user_id";
						$user_result = db_result($sql);
						if(($user = mysql_fetch_object($user_result)) || $action == "new") {
							if($action != "save-error") {
								$user_showname = $user->user_showname;
								$user_name = $user->user_name;
								$user_email = $user->user_email;
								$user_icq = $user->user_icq;
								$user_admin = $user->user_admin;
							}
						}
					}
					$out .= "\t\t\t<form action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"post\">
				<input type=\"hidden\" name=\"page\" value=\"users\"/>\r\n";
					if($action == "new" || $action == "add-error")
						$out .= "\t\t\t\t<input type=\"hidden\" name=\"action\" value=\"add\"/>\r\n";
					else
						$out .= "\t\t\t\t<input type=\"hidden\" name=\"action\" value=\"save\"/>
				<input type=\"hidden\" name=\"user_id\" value=\"".$user_id."\"/>\r\n";
					$out.= "\t\t\t\t<fieldset><legend>Benutzer</legend>
					<div class=\"row\">
						<label><strong>Anzeigename:</strong>";
					if($action == "add-error" || $action == "save-error" && $user_showname == "")
						$out .= "\t\t\t\t\t\t\t<span class=\"error\">Der Anzeigename darf nicht leer sein.</span>\r\n";
					$out .= "\t\t\t\t\t\t\t<span class=\"info\">Der Name wird immer angezeigt, wenn der Benutzer z.B. einen News-Eintrag geschrieben hat.(Notwendig)</span>
						</label>
							<input type=\"text\" name=\"user_showname\" value=\"".$user_showname."\" />
							</div>
						<div class=\"row\">
						<label><strong>Nick:</strong>\r\n";
					if($action == "add-error" || $action == "save-error" && $user_name == "")
						$out .= "\t\t\t\t\t\t\t<span class=\"error\">Der Nick muss angegeben werden.</span>\r\n";		
					$out .= "\t\t\t\t\t\t\t<span class=\"info\">Mit dem Nick kann sich der Benutzer einloggen, so muss er nicht seinen unter Umst&auml;nden komplizierten Namen,der angezeigt wird, eingeben muss. (Notwendig)</span>
						</label>
							<input type=\"text\" name=\"user_name\" value=\"".$user_name."\" />
						</div>
						<div class=\"row\">
						<label><strong>E-Mail:</strong>\r\n";
					if($action == "add-error" || $action == "save-error" && $user_email != "" && !isEMailAddress($user_email))
						$out .= "\t\t\t\t\t\t\t<span class=\"error\">Die Angegebene E-Mail-Adresse ist ung&uuml;ltig.</span>\r\n";		
					$out .= "\t\t\t\t\t\t\t<span class=\"info\">&Uuml;ber die Egl-Mail-Adresse wird der Benutzer kontaktiert. Sie ist also notwendig.</span>
						</label>
							<input type=\"text\" name=\"user_email\" value=\"".$user_email."\" />
						</div>
						<div class=\"row\">
						<label><strong>ICQ:</strong>\r\n";
					if(($action == "add-error" || $action == "save-error") && ($user_icq != "" && !isIcqNumber($user_icq)))
						$out .= "\t\t\t\t\t\t\t<span class=\"error\">Die Angegebene ICQ-Nummer ist ung&uuml;ltig.</span>\r\n";		
					$out .= "\t\t\t\t\t\t\t<span class=\"info\">Die ICQ Nummer kann angegben werden, ist aber nicht dirngend notwendig.</span>
						</label>
							<input type=\"text\" name=\"user_icq\" value=\"".$user_icq."\" maxlength=\"12\" />
						</div>
						<div class=\"row\">
						<label><strong>Passwort:</strong>\r\n";
					if(($action == "add-error" || $action == "save-error") && $user_password != "" && $user_password_confirm != "" && $user_password != $user_password_confirm) {
						$out .= "\t\t\t\t\t\t\t<span class=\"error\">Das Passwort und seine Wiederholung sind ungleich</span>\r\n";
						$user_password = "";
						$user_password_confirm = "rep-wrong";
					}
					elseif($action == "add-error" && $user_password == "") {
						$out .= "\t\t\t\t\t\t\t<span class=\"error\">Das Passwort fehlt.</span>\r\n";
						$user_password_confirm = "";
					}
					elseif($action == "save-error" && $user_password_confirm != "" && $user_password == "") {
						$out .= "\t\t\t\t\t\t\t<span class=\"error\">Das Passwort fehlt obwohl die Wiederholung angegeben war.</span>\r\n";
						$user_password_confirm = "";
					}
					if($action == "add-error" && $user_password_confirm == "" && $user_password != "")
						$user_password = "";
					$out .= "\t\t\t\t\t\t\t<span class=\"info\">Mit diesem Passwort kann sich der Benutzer in die gesch&auml;tzten Bereiche einloggen. (";
					if($action == "save-error" || $action == "edit")
						$out .= "Wenn beide Felder f&uuml;r das Passwort leer gelassen werden, wird das Passwort nicht ver&auml;ndert.";
					elseif($action == "add-error" || $action == "new")
						$out .= "Notwendig";
					$out .= ")</span>
						</label>
							<input type=\"password\" name=\"user_password\" value=\""."\" />
						</div>
						<div class=\"row\">
						<label><strong>Passwort wiederholen:</strong>\r\n";
					if (($action == "add-error" || $action == "save-error") && $user_password == "" && $user_password_confirm == "rep-wrong") {
						$out .= "\t\t\t\t\t\t\t<span class=\"error\">Das Passwort und seine Wiederholung sind ungleich</span>\r\n";
						$user_password = "";
						$user_password_confirm = "";
					}
					elseif($action == "add-error" && $user_password_confirm == "")
						$out .= "\t\t\t\t\t\t\t<span class=\"error\">Die Wiederholung des Passwortes fehlt.</span>\r\n";
					elseif($action == "save-error" && $user_password != "" && $user_password_confirm == "")
						$out .= "\t\t\t\t\t\t\t<span class=\"error\">Die Wiederholung des Passwortes fehlt.</span>\r\n";
					$out .= "\t\t\t\t\t\t\t<span class=\"info\">Durch eine Wiederholung wird sichergestellt, dass man sich bei der Eingabe nicht vertippt hat.";
					if($action == "add-error" || $action == "add")
						$out .= "(Notwendig)";
					$out .= "</span>
						</label>
							<input type=\"password\" name=\"user_password_confirm\" value=\""."\" />
						</div>
						<div class=\"row\">
						<label><strong>Administrator:</strong>
							<span class=\"info\">Ist ein Benutzer Administrator so hat er keinerlei Einschr&auml;nkungen in seinem Handeln. <strong>Nur ausw&auml;hlen wenn es wirklich Notwendig ist.</strong></span>
						</label>
							<input type=\"checkbox\" name=\"user_admin\"";
					if($user_admin == "y" || $user_admin == "on")
						$out .= " checked=\"true\"";
					$out .= "/>
						</div>
						<div class=\"row\">
							<input type=\"submit\" class=\"button\" value=\"";
							if($action == "new")
								$out .= $translation->GetTranslation('create');
							else
								$out .= $translation->GetTranslation('save');
						$out .= "\" />
						</div>
				</fieldset>
			</form>";
						return $out;
					}
				}
			}
			$out .= "\t\t\t<table class=\"text_table full_width\">
				<tr>
					<th>" . $translation->GetTranslation('name') . "</th>
					<th>K&uuml;rzel</th>
					<th>Email</th>
					<th>Admin</th>
					<th>Aktionen</th>
				</tr>\r\n";

			$users_result = db_result("SELECT * FROM " . DB_PREFIX . "users");
			while($user_db = mysql_fetch_object($users_result))
			{
				$out .= "\t\t\t\t<tr>
					<td>$user_db->user_showname</td>
					<td>$user_db->user_name</td>
					<td>$user_db->user_email</td>
					<td>";
					if($user_db->user_admin == 'y')
						$out .= $translation->GetTranslation('yes');
					else
						$out .= $translation->GetTranslation('no');
					$out .= "</td>
					<td><a href=\"".$PHP_SELF."?page=users&amp;action=edit&amp;user_id=".$user_db->user_id."\" ><img src=\"./img/edit.png\" height=\"16\" width=\"16\" alt=\"" . $translation->GetTranslation('edit') . "\" title=\"" . $translation->GetTranslation('edit') . "\"/></a>";
					
					if($user->ID == $user_db->user_id)
						$out .= "&nbsp;";
					else
						$out .= "<a href=\"".$PHP_SELF."?page=users&amp;action=delete&amp;user_id=".$user_db->user_id."\" ><img src=\"./img/del.png\" height=\"16\" width=\"16\" alt=\"" . $translation->GetTranslation('delete') . "\" title=\"" . $translation->GetTranslation('delete') . "\"/></a>";
					$out .= "</td>
				</tr>\r\n";
			}
			//<tr><td colspan="7"><a href="<?php echo $PHP_SELF."?newuser=y"; " />Neuen User hinzuf&uuml;gen</a></td></tr>
			$out .= "\t\t\t</table>
			<a href=\"" . $PHP_SELF . "?page=users&amp;action=new\" title=\"Einen neuen Benutzer erstellen\" class=\"button\">Neuen Benutzer erstellen</a>";
			//( if(!isset($pw)) { $pw = "1"; } if(!isset($pwwdh)) { $pwwdh= "1"; } if($pw!=$pwwdh) { echo "<h3>Die Wiederhohlung des Passwortes ist fehlerhaft...<br>Aus diesem Grund wurde der Eintrag nicht gespeichert.</h3>"; } 
	
		return $out;
	}
?>
