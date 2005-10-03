<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: admin_pages.php				#
 # created		: 2005-07-12					#
 # copyright		: (C) 2005 The ComaCMS-Team			#
 # email		: comacms@williblau.de				#
 #----------------------------------------------------------------------#
 # This program is free software; you can redistribute it and/or modify	#
 # it under the terms of the GNU General Public License as published by	#
 # the Free Software Foundation; either version 2 of the License, or	#
 # (at your option) any later version.					#
 #----------------------------------------------------------------------#

	/**
	 * This file contains (nearly) all subsites in the admin-interface
	 */
	
	/**
	 * returns the AdminControl-page with a list of useful details about the page
	 * and a list of all visitors
	 */

	function page_admincontrol() {
		global $admin_lang, $config;
		//
		// get the coutnt of all pages
		//
		$sitedata_result = db_result("SELECT page_id FROM " . DB_PREFIX . "pages_text");
		$page_count = mysql_num_rows($sitedata_result);
		//
		// get the count of all registered users
		//
		$users_result = db_result("SELECT user_id FROM " . DB_PREFIX . "users");
		$users_count = mysql_num_rows($users_result);
		//
		// get the size of all tables with the prefix DB_PREFIX
		//
		$table_infos_result = db_result("SHOW TABLE STATUS");
		$data_size = 0;
		while($table_infos = mysql_fetch_object($table_infos_result)) {
			if(substr($table_infos->Name, 0, strlen(DB_PREFIX)) == DB_PREFIX)
				$data_size += $table_infos->Data_length + $table_infos->Index_length;
		}
		$installdate = $config->Get('install_date');
		if($installdate == '') {
			$config->Save('install_date', mktime());
			$installdate = mktime();
		}
		
		$out = "<h3>AdminControl</h3><hr />
	<table>
		<tr><td>" . $admin_lang['online since'] . "</td><td>". date("d.m.Y",$installdate) . "</td></tr>
		<tr><td>" . $admin_lang['registered users'] . "</td><td>$users_count</td></tr>
		<tr><td>" . $admin_lang['created pages'] . "</td><td>$page_count</td></tr>
		<tr><td>" . $admin_lang['database size'] . "</td><td>" . kbormb($data_size) . "</td></tr>
	</table>
	
	<h3>Aktuelle Besucher</h3><hr />
	<table>
		<tr>
			<td>".$admin_lang['name']."</td>
			<td>".$admin_lang['page']."</td>
			<td>".$admin_lang['last action']."</td>
			<td>".$admin_lang['language']."</td>
			<td>".$admin_lang['ip']."</td>
			<td>".$admin_lang['host']."</td>
		</tr>";
		//output all visitors surfing on the site
		$sql = "SELECT userid, page, lastaction, lang, ip, host
			FROM " . DB_PREFIX . "online
			WHERE lastaction >= " . (mktime() - 300) . "";
		$users_online_result = db_result($sql);
		while($users_online = mysql_fetch_object($users_online_result)) {
			if($users_online->userid == 0)
				$username  = $admin_lang['not registered'];
			else
				$username = getUserById($users_online->userid);
			$out .= "\t\t\t<tr>
			<td>".$username."</td>
			<td><a href=\"index.php?page=".$users_online->page."\">".$users_online->page."</a></td>
			<td>" . date("d.m.Y H:i:s", $users_online->lastaction)."</td>
			<td>" . $admin_lang[$users_online->lang] . "</td>
			<td>" . $users_online->ip . "</td>
			<td>" . $users_online->host . "</td>
		</tr>\r\n";
		}

		$out .= "</table>";
	
		return $out;
	}

/*****************************************************************************
 *
 *  string page_sitepreview()
 *  returns the Sitepreview-page where you can see the 'real' site in a iframe
 *
 *****************************************************************************/

	function page_sitepreview() {
		global $admin_lang;
		
		$out = '<h3>' . $admin_lang['sitepreview'].'</h3><hr /><iframe src="index.php" class="sitepreview"></iframe>';
		return $out;
	}
/*****************************************************************************
 *
 * string page_menueeditor()
 * returns the Menueeditor-page whith the followong functionalitys:
 * -$action=up -> changes the place in the menue order with the item before the selected
 * -$action=down -> changes the place in the menue order with the item after the selected
 * -$action=delete -> removes the item from the menue but it asks if it's sure that the item should be deleted
 * -$action=add -> adds a new item at the end of the menue
 *
 * it is possible to choose between two menues by $menue_id
 *
 *****************************************************************************/

	function page_menueeditor() {
		global $_GET,$_POST, $admin_language;
	
		$menue_id = 0;
		$out = "\t\t\t<h3>Menueeditor</h3><hr />	
			<ul>\r\n";
	
		if(isset($_GET['menue_id']) || isset($_POST['menue_id'])) {
			if(isset($_GET['menue_id']))
				$menue_id = $_GET['menue_id'];
			else
				$menue_id = $_POST['menue_id'];
		}
		//
		// write the 'coose menue'  to make it able to switch betwen both possible menues
		//
		if($menue_id == "2")
			$out .= "\t\t\t\t<li><a href=\"admin.php?page=menueeditor&amp;menue_id=1\">Menü 1</a></li>
				<li><u>Menü 2</u></li>\r\n";
		else {
			$out .= "\t\t\t\t<li><u>Menü 1</u></li>
				<li><a href=\"admin.php?page=menueeditor&amp;menue_id=2\">Menü 2</a></li>\r\n";
			$menue_id = 1;
		}
		$out .= "\t\t\t</ul>\r\n";
	
		if(isset($_GET['action']) || isset($_POST['action'])) {
			if(isset($_GET['action']))
				$action = $_GET['action'];
			else
				$action = $_POST['action'];
		
			if(isset($_GET['id']))
				$id = $_GET['id'];
			elseif(isset($_POST['id']))
				$id = $_POST['id'];
			else
				$id = 0;
			//
			// put the item one position higher
			//
			if($action == "up") {
				$_result = db_result("SELECT * FROM ".DB_PREFIX."menue WHERE id=".$id."");
				$_data = mysql_fetch_object($_result);
				$id1 = $_data->id;
				//
				// get the orderid to find the follownig menue item
				//
				$orderid1 = $_data->orderid;
			
				$_result2 = db_result("SELECT * FROM ".DB_PREFIX."menue WHERE orderid <".$orderid1." AND menue_id=".$menue_id." ORDER BY orderid DESC");
				$_data2 = mysql_fetch_object($_result2);
				
				//
				// switch the orderids to cange the order of this two menue items
				//
				if($_data2 != null) {
					$id2 = $_data2->id;
					$orderid2 = $_data2->orderid;
					db_result("UPDATE ".DB_PREFIX."menue SET orderid= ".$orderid2." WHERE id=".$id1);
					db_result("UPDATE ".DB_PREFIX."menue SET orderid= ".$orderid1." WHERE id=".$id2);
				}
			}
			//
			// put the item one position lower
			//
			elseif($action == "down") {
				$_result = db_result("SELECT * FROM ".DB_PREFIX."menue WHERE id=".$id."");
				$_data = mysql_fetch_object($_result);
				$id1 = $_data->id;
				$orderid1 = $_data->orderid;
			
				$_result2 = db_result("SELECT * FROM ".DB_PREFIX."menue WHERE orderid >".$orderid1." AND menue_id=".$menue_id." ORDER BY orderid ASC");
				$_data2 = mysql_fetch_object($_result2);
			
				if($_data2 != null) {
					$id2 = $_data2->id;
					$orderid2 = $_data2->orderid;
					db_result("UPDATE ".DB_PREFIX."menue SET orderid= ".$orderid2." WHERE id=".$id1);
					db_result("UPDATE ".DB_PREFIX."menue SET orderid= ".$orderid1." WHERE id=".$id2);
				}
			}
			//
			// remove the selected item
			//
			elseif($action == "delete") {
				if(isset($_GET['sure']) || isset($_POST['sure'])) {
					if(isset($_GET['sure']))
						$sure = $_GET['sure'];
					else
						$sure = $_POST['sure'];
					if($sure == 1)
						db_result("DELETE FROM ".DB_PREFIX."menue WHERE id=".$id."");
				}
				else {
					$_result = db_result("SELECT * FROM ".DB_PREFIX."menue WHERE id=".$id."");
					$_data = mysql_fetch_object($_result);
					$out .= "\t\t\t<div class=\"error\">Soll der Link ".$_data->text."(".$_data->link.") wirklich gelöscht werden?<br />
			<a href=\"admin.php?page=menueeditor&amp;action=delete&amp;menue_id=".$menue_id."&amp;id=".$id."&amp;sure=1\" title=\"Wirklich Löschen?\">Ja</a> &nbsp;&nbsp;&nbsp; <a href=\"admin.php?page=menueeditor&amp;menue_id=".$menue_id."\" title=\"Nein! nicht löschen\">Nein</a></div>";
					
					return $out;
				}
			}
			//
			// add a new item
			//
			elseif($action == "add") {
				if(isset($_GET['intern_link']))
					$intern_link = $_GET['intern_link'];
				else
					$intern_link = $_POST['intern_link'];
			
				if(isset($_GET['extern_link']))
					$extern_link = $_GET['extern_link'];
				else
					$extern_link = $_POST['extern_link'];
			
				if(isset($_GET['caption']))
					$caption = $_GET['caption'];
				else
					$caption = $_POST['caption'];
				$new_window = "";
				if(isset($_GET['new_window']))
					$new_window = $_GET['new_window'];
				elseif(isset($_POST['new_winow']))
					$new_window = $_POST['new_window'];	
				
				if($intern_link == "")
					$link = $extern_link;
				else
					$link = "l:".$intern_link;
				if($link != "" && $caption != "") {
					if($new_window == "on")
						$new = "yes";
					else
						$new = "no";
					$menue_result = db_result("SELECT orderid FROM ".DB_PREFIX."menue WHERE menue_id = ".$menue_id." ORDER BY orderid DESC");
					$menue_data = mysql_fetch_object($menue_result);
					if($menue_data != null)
						$ordid = $menue_data->orderid + 1;
					else
						$ordid = 0;
				
					db_result("INSERT INTO ".DB_PREFIX."menue (text, link, new, orderid,menue_id) VALUES ('".$caption."', '".$link."', '".$new."', ".$ordid.",$menue_id)");
				}
			}
		}
	
		$out .= "\t\t\t<table class=\"linktable\">
				<thead>
					<tr>
						<td>Text</td>
						<td>Link</td>
						<td>Aktionen</td>
					</tr>
				</thead>
				<tbody>\r\n";
		//
		// add the menueedit part where you can select
		//
		$out .= menue_edit_view($menue_id);
		$out .= "\t\t\t\t</tbody>
			</table><br />\r\n";
		$out .= "\t\t\t<form method=\"get\" action=\"admin.php\">
				<input type=\"hidden\" name=\"page\" value=\"menueeditor\" />
				<input type=\"hidden\" name=\"action\" value=\"add\" />
				<input type=\"hidden\" name=\"menue_id\" value=\"".$menue_id."\" />
				<table>
					<tr>
						<td>Bezeichnung:</td>
						<td><input type=\"text\" name=\"caption\" /></td>
					</tr>
					<tr>
						<td>Interner Link:</td>
						<td>
							<select name=\"intern_link\">
								<option value=\"\">externer Link</option>\r\n";
		//
		// list all available pages 
		//
		$site_result = db_result("SELECT * FROM ".DB_PREFIX."pages_content WHERE page_visible!='deleted' ORDER BY page_name ASC");
		while($site_data = mysql_fetch_object($site_result))
			$out.= "\t\t\t\t\t\t\t\t<option value=\"".$site_data->page_name."\">".$site_data->page_title."(".$site_data->page_name.")</option>\r\n";
			
		$out .="\t\t\t\t\t\t\t</select>
						</td>
					</tr>
					<tr>
						<td>Externer Link:</td>
						<td><input type=\"text\" name=\"extern_link\" /></td>
					</tr>
					<tr>
						<td>Neue Fenster:</td>
						<td><input type=\"checkbox\" name=\"new_window\" /></td>
					</tr>
					<tr>
						<td colspan=\"2\"><input type=\"submit\" value=\"Hinzufügen\" /></td>
					</tr>
				</table>
			</form>";
	
		return $out;
	}
/*****************************************************************************
 *
 * string page_sitesytle()
 * returns the sitestyle(changer)-page with a preview-iframe and a form which make it able o change the style
 *
 *****************************************************************************/
	function page_sitestyle() {
		global $internal_style, $extern_save, $extern_style;
		
		$out = "<script type=\"text/javascript\" language=\"JavaScript\" src=\"./system/functions.js\"></script>";
	
		if(!isset($extern_save))
			$extern_style = $internal_style;
	
		if(isset($extern_save)) {
			if(file_exists("./styles/$extern_style/mainpage.php")) {
				$sql = "UPDATE " . DB_PREFIX . "config
					SET config_value= '$extern_style'
					WHERE config_name='style'";
				db_result($sql);
			}
		}
	
		$out .= "<iframe id=\"previewiframe\" src=\"./index.php?style=".$extern_style."\" class=\"stylepreview\"></iframe>
		<form action=\"admin.php\" method=\"get\">
			<input type=\"hidden\" name=\"page\" value=\"sitestyle\" />
			<label for=\"stylepreviewselect\">Style:
				<select id=\"stylepreviewselect\" name=\"style\" size=\"1\">";
	
		$verz = dir("./styles/");
		//
		// read the available styles
		//
		while($entry = $verz->read()) {
			//
			// check if the style really exists
			//
			if($entry != "." && $entry != ".." && file_exists("./styles/".$entry."/mainpage.php")) {
				//
				// mark the selected style as selected in the list
				//
				if($entry == $extern_style)
					$out .= "\t\t\t\t\t<option value=\"".$entry."\" selected=\"selected\">".$entry."</option>\r\n";	
				else
					$out .= "\t\t\t\t\t<option value=\"".$entry."\">".$entry."</option>\r\n";
			}
		}
		$verz->close();
	
		$out .= "</select>
			</label>

			<input type=\"submit\" value=\"Vorschau\" onclick=\"preview_style();return false;\" name=\"preview\" />
			<input type=\"submit\" value=\"Speichern\" name=\"save\" />

		</form>";
		
		return $out;
	}

/*****************************************************************************
 *
 * string page_news()
 * returns the news-admin-page where you can write,change and delete news-entries
 *
 *****************************************************************************/
 
	function page_news() {
		global $_GET, $_POST, $actual_user_showname, $actual_user_id;
		
		$out = "";
		$action = "";
		$id = 0;
		$text = "";
		$title = "";

		if(isset($_GET['action']) || isset($_POST['action'])) {
			if(isset($_GET['action']))
				$action = $_GET['action'];
			else
				$action = $_POST['action'];
		
			if(isset($_GET['id']))
				$id = $_GET['id'];
			elseif(isset($_POST['id']))
				$id = $_POST['id'];
		
	
			if(isset($_GET['text']) || isset($_POST['text'])) {
				if(isset($_GET['text']))
					$text = $_GET['text'];
				else
					$text = $_POST['text'];
			}

			if(isset($_GET['title']) || isset($_POST['title'])) {
				if(isset($_GET['title']))
					$title = $_GET['title'];
					else
					$title = $_POST['title'];
			}
			//
			// delete the selected entrie
			//
			if($action == "delete") {
				if(isset($_GET['sure']) || isset($_POST['sure'])) {
					if(isset($_GET['sure']))
						$sure = $_GET['sure'];
					else
						$sure = $_POST['sure'];
				
					if($sure == 1)
						db_result("DELETE FROM " . DB_PREFIX . "news WHERE id=" . $id);
				}
				else {
					$result = db_result("SELECT * FROM " . DB_PREFIX . "news WHERE id=" . $id);
					$row = mysql_fetch_object($result);
					$out .= "Den News Eintrag &quot;" . $row->title . "&quot; wirklich löschen?<br />
				<a href=\"admin.php?page=news&amp;action=delete&amp;id=" . $id . "&amp;sure=1\" title=\"Wirklich Löschen\">ja</a> &nbsp;&nbsp;&nbsp;&nbsp;
				<a href=\"admin.php?page=news\" title=\"Nicht Löschen\">nein</a>";
				
					return $out;
				}
			}
			//
			// add a new entrie
			//
			elseif($action == "new") {
				if($text != "" && $title != "")
					db_result("INSERT INTO ".DB_PREFIX."news (title, text, date, userid) VALUES ('".$title."', '".$text."', '".mktime()."', '$actual_user_id')");
			}
			//
			// update the selected entrie
			//
			elseif($action == "update") { 
				if($text != "" && $title != "" && $id != 0)
					db_result("UPDATE ".DB_PREFIX."news SET title= '".$title."', text= '".$text."' WHERE id=".$id);
			}
		}
		//
		// don't show the add new form if it is sure that the user wants to edit a news entrie
		//
		if($action != "edit") {
			$out .= "\t\t<form method=\"post\" action=\"admin.php\">
			<input type=\"hidden\" name=\"page\" value=\"news\" />
			<input type=\"hidden\" name=\"action\" value=\"new\" />
			Titel: <input type=\"text\" name=\"title\" maxlength=\"60\" value=\"\" /><br />
			<textarea cols=\"60\" rows=\"6\" name=\"text\"></textarea><br />
			Eingelogt als " . $actual_user_showname . " &nbsp;<input type=\"submit\" value=\"Senden\" /><br />
		</form>";
		}
			$out .= "\t\t<form method=\"post\" action=\"admin.php\">
			<input type=\"hidden\" name=\"page\" value=\"news\" />
			<input type=\"hidden\" name=\"action\" value=\"update\" />
			<table>\r\n";
		//
		// write all news entries
		//
		$result = db_result("SELECT * FROM ".DB_PREFIX."news ORDER BY date DESC");
		while($row = mysql_fetch_object($result)) {
			//
			// show an editform for the selected entrie
			//
			if($id == $row->id && $action == "edit") {
				$out .= "\t\t\t\t<tr>
					<td colspan=\"2\" id=\"newsid" . $row->id . "\">
						<input type=\"hidden\" name=\"id\" value=\"".$row->id."\" />
						<input type=\"submit\" value=\"Speichern\" />
						&nbsp;<a href=\"admin.php?page=news&amp;action=delete&amp;id=".$row->id."\" title=\"Löschen\">Löschen</a>
					</td>
				</tr>
				<tr>
					<td>
						<input type=\"text\" name=\"title\" value=\"".$row->title."\" />
					</td>
					<td>
						".date("d.m.Y H:i:s", $row->date)."
					</td>
				</tr>
				<tr>
					<td colspan=\"2\">
						<textarea name=\"text\" cols=\"60\" rows=\"6\">".$row->text."</textarea>
					</td>
				</tr>
				<tr>
					<td colspan=\"2\">
						" . getUserByID($row->userid) . "
					</td>
				</tr>";
			}
			//
			// show only the entrie
			//
			else {
				$out .= "\t\t\t\t<tr>
					<td colspan=\"2\">
						<a id=\"newsid".$row->id."\" ></a>
						<a href=\"admin.php?page=news&amp;action=edit&amp;id=".$row->id."#newsid".$row->id."\" title=\"Bearbeiten\">Bearbeiten</a>
						&nbsp;<a href=\"admin.php?page=news&amp;action=delete&amp;id=".$row->id."\" title=\"Löschen\">Löschen</a>
					</td>
				</tr>
				<tr>
					<td>
						<b>".$row->title."</b>
					</td>
					<td>
						".date("d.m.Y H:i:s", $row->date)."
					</td>
				</tr>
				<tr>
					<td colspan=\"2\">
						".nl2br($row->text)."
					</td>
				</tr>
				<tr>
					<td colspan=\"2\">
						".getUserByID($row->userid)."
					</td>
				</tr>";
			}
		}
		$out .= "\t\t\t</table>
		</form>\r\n";
	
		return $out;
	}

/*****************************************************************************
 *
 * string page_users()
 * returns the user-admin-page where you can add, change and delete users
 *
 *****************************************************************************/
	function page_users() {
		global $_GET, $_POST, $PHP_SELF, $admin_lang, $actual_user_id, $actual_user_passwd_md5,$actual_user_online_id, $actual_user_online_id, $_SERVER, $user;
	
		$out  ="";
	
		if(isset($_GET['action']) || isset($_POST['action'])) {
			if(isset($_GET['action']))
				$action = $_GET['action'];
			else
				$action = $_POST['action'];
			$user_id = "0";
			$user_name = "";
			$user_showname = "";
			$user_email = "";
			$user_icq = "";
			$user_admin = "";
			$user_password = "";
			$user_password_confirm = "";
			
			if(isset($_GET['user_id']))
				$user_id = $_GET['user_id'];
			elseif(isset($_POST['user_id']))
				$user_id = $_POST['user_id'];
			
			if(isset($_GET['user_name']))
				$user_name = $_GET['user_name'];
			elseif(isset($_POST['user_name']))
				$user_name = $_POST['user_name'];
			
			if(isset($_GET['user_showname']))
				$user_showname = $_GET['user_showname'];
			elseif(isset($_POST['user_showname']))
				$user_showname = $_POST['user_showname'];
			
			if(isset($_GET['user_email']))
				$user_email = $_GET['user_email'];
			elseif(isset($_POST['user_email']))
				$user_email = $_POST['user_email'];
			
			if(isset($_GET['user_icq']))
				$user_icq = $_GET['user_icq'];
			elseif(isset($_POST['user_icq']))
				$user_icq = $_POST['user_icq'];
			
			if(isset($_GET['user_admin']))
				$user_admin = $_GET['user_admin'];
			elseif(isset($_POST['user_admin']))
				$user_admin = $_POST['user_admin'];
				
			if(isset($_GET['user_password']))
				$user_password = $_GET['user_password'];
			elseif(isset($_POST['user_password']))
				$user_password = $_POST['user_password'];
			
			if(isset($_GET['user_password_confirm']))
				$user_password_confirm = $_GET['user_password_confirm'];
			elseif(isset($_POST['user_password_confirm']))
				$user_password_confirm = $_POST['user_password_confirm'];
				
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
						$user = mysql_fetch_object($result);
						$sql = "DELETE FROM " . DB_PREFIX . "users
							WHERE user_id=$user_id";
						db_result($sql);
						$out .= "Der Benutzer &quot;" . $user->user_showname . "&quot; ist nun unwiederuflich gelöscht worden!<br />";
					}
				}
				else {
					$sql = "SELECT *
						FROM " . DB_PREFIX . "users
						WHERE user_id=$user_id";
					$result = db_result($sql);
					$user = mysql_fetch_object($result);
					$out .= "Den Benutzer &quot;" . $user->user_showname . "&quot; unwiederruflich löschen?<br />
				<a href=\"admin.php?page=users&amp;action=delete&amp;user_id=" . $user_id . "&amp;sure=1\" title=\"Wirklich Löschen\">" . $admin_lang['yes'] . "</a> &nbsp;&nbsp;&nbsp;&nbsp;
				<a href=\"admin.php?page=users\" title=\"Nicht Löschen\">" . $admin_lang['no'] . "</a>";
					
					return $out;
				}
			}
			if($action == "edit" || $action == "new" || $action == "add-error" || $action == "save-error") {
				if($user_id != "0" || $action == "new" || $action == "add-error" || $action == "save-error") {
					$sql = "SELECT *
						FROM " . DB_PREFIX . "users
						WHERE user_id=$user_id";
					$user_result = db_result($sql);
					if(($user = mysql_fetch_object($user_result)) || $action == "new") {
						if($user != null && $action != "save-error") {
							$user_showname = $user->user_showname;
							$user_name = $user->user_name;
							$user_email = $user->user_email;
							$user_icq = $user->user_icq;
							$user_admin = $user->user_admin;
						}
						$out .= "\t\t\t<form action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"post\">
				<input type=\"hidden\" name=\"page\" value=\"users\"/>\r\n";
						if($action == "new" || $action == "add-error")
							$out .= "\t\t\t\t<input type=\"hidden\" name=\"action\" value=\"add\"/>\r\n";
						else
							$out .= "\t\t\t\t<input type=\"hidden\" name=\"action\" value=\"save\"/>
				<input type=\"hidden\" name=\"user_id\" value=\"".$user_id."\"/>\r\n";
						$out.= "\t\t\t\t<table>
					<tr>
						<td>
							Anzeigename:\r\n";
						if($action == "add-error" || $action == "save-error" && $user_showname == "")
							$out .= "\t\t\t\t\t\t\t<span class=\"error\">Der Anzeigename darf nicht leer sein.</span>\r\n";
						$out .= "\t\t\t\t\t\t\t<span class=\"info\">Der Name wird immer angezeigt, wenn der Benutzer z.B. einen News-Eintrag geschrieben hat.(Notwendig)</span>
						</td>
						<td>
							<input type=\"text\" name=\"user_showname\" value=\"".$user_showname."\" />
						</td>
					</tr>
					<tr>
						<td>
							Nick:\r\n";
						if($action == "add-error" || $action == "save-error" && $user_name == "")
							$out .= "\t\t\t\t\t\t\t<span class=\"error\">Der Nick muss angegeben werden.</span>\r\n";		
						$out .= "\t\t\t\t\t\t\t<span class=\"info\">Mit dem Nick kann sich der Benutzer einloggen, so muss er nicht seinen unter Umständen komplizierten Namen,der angezeigt wird, eingeben muss.(Notwendig)</span>
						</td>
						<td>
							<input type=\"text\" name=\"user_name\" value=\"".$user_name."\" />
						</td>
					</tr>
					<tr>
						<td>
							E-Mail:\r\n";
						if($action == "add-error" || $action == "save-error" && $user_email != "" && !isEMailAddress($user_email))
							$out .= "\t\t\t\t\t\t\t<span class=\"error\">Die Angegebene E-Mail-Adresse ist ungültig.</span>\r\n";		
						$out .= "\t\t\t\t\t\t\t<span class=\"info\">Über die E-Mail-Adresse wird der Benutzer kontaktiert. Sie ist also notwendig.</span>
						</td>
						<td>
							<input type=\"text\" name=\"user_email\" value=\"".$user_email."\" />
						</td>
					</tr>
					<tr>
						<td>
							ICQ:\r\n";
						if(($action == "add-error" || $action == "save-error") && ($user_icq != "" && !isIcqNumber($user_icq)))
							$out .= "\t\t\t\t\t\t\t<span class=\"error\">Die Angegebene ICQ-Nummer ist ungültig.</span>\r\n";		
						$out .= "\t\t\t\t\t\t\t<span class=\"info\">Die ICQ Nummer kann angegben werden, ist aber nicht dirngend notwendig.</span>
						</td>
						<td>
							<input type=\"text\" name=\"user_icq\" value=\"".$user_icq."\" maxlength=\"12\" />
						</td>
					</tr>
					<tr>
						<td>
							Passwort:\r\n";
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
						$out .= "\t\t\t\t\t\t\t<span class=\"info\">Mit diesem Passwort kann sich der Benutzer in die geschützten Bereiche einloggen. (";
						if($action == "save-error" || $action == "edit")
							$out .= "Wenn beide Felder für das Passwort leer gelassen werden, wird das Passwort nicht verändert.";
						elseif($action == "add-error" || $action == "add")
							$out .= "Notwendig";
						$out .= ")</span>
						</td>
						<td>
							<input type=\"password\" name=\"user_password\" value=\""."\" />
						</td>
					</tr>
					<tr>
						<td>
							Passwort wiederholen:\r\n";
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
						</td>
						<td>
							<input type=\"password\" name=\"user_password_confirm\" value=\""."\" />
						</td>
					</tr>
					<tr>
						<td>
							Administrator:
							<span class=\"info\">Ist ein Benutzer Administrator so hat er keinerlei Einschränkungen in seinem Handeln.<strong>Nur auswählen wenn es wirklich Notwendig ist.</strong></span>
						</td>
						<td>
							<input type=\"checkbox\" name=\"user_admin\"";
						if($user_admin == "y" || $user_admin == "on")
							$out .= " checked=\"true\"";
						$out .= "/>
						</td>
					</tr>
					<tr>
						<td colspan=\"2\">
							<input type=\"reset\" class=\"button\" value=\"" . $admin_lang['reset'] . "\" />&nbsp;
							<input type=\"submit\" class=\"button\" value=\"";
								if($action == "new")
									$out .= $admin_lang['create'];
								else
									$out .= $admin_lang['save'];
							$out .= "\" />
						</td>
					</tr>
				</table>
			</form>";
							return $out;
						}
					}
				}
			}
			$out .= "\t\t\t<table>
				<tr>
					<td>id</td>
					<td>" . $admin_lang['name'] . "</td>
					<td>Kürzel</td>
					<td>email</td>
					<td>Admin</td>
					<td colspan=\"2\">Aktionen</td>
				</tr>\r\n";

			$users_result = db_result("SELECT * FROM " . DB_PREFIX . "users");
			while($user_db = mysql_fetch_object($users_result))
			{
				$out .= "\t\t\t\t<tr>
					<td>#".$user_db->user_id."</td>
					<td>$user_db->user_showname</td>
					<td>$user_db->user_name</td>
					<td>$user_db->user_email</td>
					<td>";
					if($user_db->user_admin == 'y')
						$out .= $admin_lang['yes'];
					else
						$out .= $admin_lang['no'];
					$out .= "</td>
					<td><a href=\"".$PHP_SELF."?page=users&amp;action=edit&amp;user_id=".$user_db->user_id."\" ><img src=\"./img/edit.png\" height=\"16\" width=\"16\" alt=\"" . $admin_lang['edit'] . "\" title=\"" . $admin_lang['edit'] . "\"/></a></td>
					<td>";
					
					if($user->ID == $user_db->user_id)
						$out .= "&nbsp;";
					else
						$out .= "<a href=\"".$PHP_SELF."?page=users&amp;action=delete&amp;user_id=".$user_db->user_id."\" ><img src=\"./img/del.jpg\" height=\"16\" width=\"16\" alt=\"" . $admin_lang['delete'] . "\" title=\"" . $admin_lang['delete'] . "\"/></a>";
					$out .= "</td>
				</tr>\r\n";
			}
			//<tr><td colspan="7"><a href="<?php echo $PHP_SELF."?newuser=y"; " />Neuen User hinzuf&uuml;gen</a></td></tr>
			$out .= "\t\t\t</table>
			<a href=\"" . $PHP_SELF . "?page=users&amp;action=new\" title=\"Einen neuen Benutzer erstellen\">Neuen Benutzer erstellen</a>";
			//( if(!isset($pw)) { $pw = "1"; } if(!isset($pwwdh)) { $pwwdh= "1"; } if($pw!=$pwwdh) { echo "<h3>Die Wiederhohlung des Passwortes ist fehlerhaft...<br>Aus diesem Grund wurde der Eintrag nicht gespeichert.</h3>"; } 
	
		return $out;
	}
	
/*****************************************************************************
 *
 * string page_preferences()
 * returns the preferences-admin-page where you can change or add some entries
 *
 *****************************************************************************/
	function page_preferences() {
		global $setting, $_SERVER, $admin_lang, $extern_action, $_GET, $_POST;
		/**
		 * Load the template file for the preferences
		 */
		include('./system/settings.php');
		$out = '';
		if(!isset($extern_action))
			$extern_action = '';
		if($extern_action == 'save') {
			$tosave = array();
			foreach($_GET as $key => $value) {
				//$out .= $key."<br />";
				if(substr($key, 0, 8) == 'setting_'){
					$name = substr($key, 8);
					$_n = 'internal_' . $name;
					global $$_n;
					if($$_n != $_GET[$key])
						$tosave[$name] = $value;
				}
			
			}
			foreach($_POST as $key => $value) {
				//$out .= $key."<br />";
				if(substr($key, 0, 8) == 'setting_'){
					$name = substr($key, 8);
					$_n = 'internal_' . $name;
					global $$_n;
					if($$_n != $_POST[$key])
						$tosave[$name] = $value;
				}
			}
			foreach($tosave as $key => $value) {
				$_n = 'internal_' . $key;
				
				if($$_n == '') {
					$out .= "new: $key<br />";
					$sql = "SELECT *
						FROM " . DB_PREFIX . "config
						WHERE config_name='$key'";
					$exists_result = db_result($sql);
					if($exists = mysql_fetch_object($exists_result)) {
						$sql = "UPDATE " . DB_PREFIX . "config
							SET config_value = '$value'
							WHERE config_name = '$key'
							LIMIT 1";
						db_result($sql);
					}
					else {
						$sql = "INSERT INTO " . DB_PREFIX . "config (config_name , config_value )
							VALUES ( '$key', '$value')";
						db_result($sql);
					}
				}
				else {
					$out .= "update: $key<br />";
					$sql = "UPDATE " . DB_PREFIX . "config
						SET config_value = '$value'
						WHERE config_name = '$key' LIMIT 1";
					db_result($sql);
				}
			}
		}
		else {
			$out .= "\t\t\t<form action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"post\">
				<input type=\"hidden\" name=\"page\" value=\"preferences\" />
				<input type=\"hidden\" name=\"action\" value=\"save\" />
				<table>\r\n";
			foreach($setting as $key => $value) {
				$__intern = 'internal_'.$key;
				global $$__intern;
				//$out .= $value[0] . "=" . $value[2]."-" . $$__n . "<br />";
				$_value = $$__intern;
				if($_value == '')
					$_value = $value[2];
				$out .= "\t\t\t\t\t<tr>
						<td>
							" . $value[0] . ":
							<span class=\"info\" >" . $value[1] . "</span>
						</td>
						<td>\r\n";
				if($key == 'default_page') {
					$out .= "\t\t\t\t\t\t<select name=\"setting_" . $key . "\">\r\n";
					$sql = "SELECT page_name, page_title, page_id
						FROM " . DB_PREFIX . "pages
						WHERE page_access='public'";
					$pages_result = db_result($sql);
					while($page_names = mysql_fetch_object($pages_result))
						$out .= "\t\t\t\t\t\t\t<option value=\"" . $page_names->page_id . "\" " . (($_value == $page_names->page_id) ? " selected=\"selected\"" : "") . ">" . $page_names->page_title . " (" . $page_names->page_name . ")</option>\r\n";
					$out .= "\t\t\t\t\t\t</select>\r\n";
				}
				else
					$out .= "\t\t\t\t\t\t<input name=\"setting_" . $key . "\" value=\"" . $_value . "\" />\r\n";
				$out .= "\t\t\t\t\t</td>
					</tr>\r\n";
			}
			$out .= "\t\t\t\t\t<tr>
						<td colspan=\"2\">
						<input type=\"reset\" class=\"button\" value=\"" . $admin_lang['reset'] . "\" />
						<input type=\"submit\" class=\"button\" value=\"" . $admin_lang['save'] . "\" />
						</td>
					</tr>
				</table>
			</form>\r\n";
		}
		
		return $out;
	}

/*****************************************************************************
 *
 * string page_gallery_editor()
 * returns the gallery-admin-page where you can add, change and delete gallerys
 *
 *****************************************************************************/
	function page_gallery_editor() {
		global $admin_lang, $extern_action, $_SERVER, $extern_images, $extern_gallery_name, $extern_gallery_title, $actual_user_id;
		
		if(!isset($extern_action))
			$extern_action = '';
		if(!isset($extern_gallery_name))
			$extern_gallery_name = '';
		if(!isset($extern_gallery_title))
			$extern_gallery_title = '';
		if($extern_images === null)
			$extern_images = array();
			
		$out = "<h3>" . $admin_lang['gallery editor'] . "</h3><hr />\r\n";
		if($extern_action == 'select') {
		
			$out .= "Bilder auswählen
				<form  action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"post\">
					<input type=\"hidden\" name=\"page\" value=\"gallery_editor\"/>
					<input type=\"hidden\" name=\"action\" value=\"new\" />
					<table>
						<tr>
							<td>
								Bilder:
								<span class=\"info\">TODO</span>
							</td>
						<td>
					";
				$sql = "SELECT *
					FROM " . DB_PREFIX . "files
					WHERE file_type LIKE 'image/%'
					ORDER BY file_name ASC";
				$images_result = db_result($sql);
				while($image = mysql_fetch_object($images_result)) {
					$thumb = str_replace('/upload/', '/thumbnails/', $image->file_path);
					preg_match("'^(.*)\.(gif|jpe?g|png|bmp)$'i", $thumb, $ext);
					//echo $thumb."<br />";
					if(strtolower($ext[2]) == 'gif')
						$thumb .= '.png';
					
					$succes = true;
					$imgmax = 100;
					if(!file_exists($thumb))
						$succes = generateThumb($image->file_path, $imgmax);
					if(file_exists($thumb) || $succes) {
						$sizes = getimagesize($thumb);
						$margin_top = round(($imgmax - $sizes[1]) / 2);
						$margin_bottom = $imgmax - $sizes[1] - $margin_top;
						$out .= "<div class=\"imageblock\">
						<a href=\"" . generateUrl($image->file_path) . "\">
						<img style=\"margin-top:" . $margin_top . "px;margin-bottom:" . $margin_bottom . "px;width:" . $sizes[0] . "px;height:" . $sizes[1] . "px;\" src=\"" . generateUrl($thumb) . "\" alt=\"$thumb\" /></a><br />
						<input type=\"checkbox\" name=\"images[]\" value=\"$image->file_id\"/>Auswählen</div>";
					}
				}
			$out .= "</td>
				</tr>
				<tr>
					<td colspan=\"2\">
						<input class=\"button\" type=\"reset\" value=\"Auswahl rückgängig machen\" />&nbsp;
						<input class=\"button\" type=\"Submit\" value=\"Als Gallerie Zusammenfassen\"/>
					</td>
				</tr>
			</table>
		</form>";
			return $out;
		}
		elseif($extern_action == 'add') {
			//
			// TODO: check for correct inputs
			//
			$page_text = implode(',', $extern_images);
			
			foreach($extern_images as $id) {
				$sql = "SELECT file_path
					FROM " . DB_PREFIX . "files
					WHERE file_id=$id";
				$image_result = db_result($sql);
				$image = mysql_fetch_object($image_result);
				$page_text .=  "\r\n" . $image->file_path;
			}
			//$out .= $page_text;
			$sql = "INSERT INTO " . DB_PREFIX . "pages_content (page_name, page_type, page_title, page_text, page_lang, page_html, page_parent_id, page_creator, page_created, page_visible)
					VALUES ('$extern_gallery_name', 'gallery', '$extern_gallery_title', '$page_text', '', '', '0', '$actual_user_id', '" . mktime() . "','public')";
			db_result($sql);
			
		}
		elseif($extern_action == 'overview') {
			$out .= generatePagesTree(0, "\t\t\t", '', true, true,'gallery');
		}
		elseif($extern_action == 'new') {
			//$images = array();
			
			
			$out .= "<form method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "\">
			<input type=\"hidden\" name=\"page\" value=\"gallery_editor\" />
			<input type=\"hidden\" name=\"action\" value=\"add\" />
			<table>
			<tr>
				<td>
					Titel:
					<span class=\"info\">TODO</span>
				</td>
				<td>
					<input type=\"text\" name=\"gallery_title\"/>
				</td>
			</tr>
			<tr>
				<td>
					Name:
					<span class=\"info\">TODO</span>
				</td>
				<td>
					<input type=\"text\" name=\"gallery_name\"/>
				</td>
			</tr>
			<tr>
				<td class=\"topdesc\">
					Bilder:
					<span class=\"info\">TODO</span>
				</td>
				<td>";
			foreach($extern_images as $id) {
				$sql = "SELECT file_path
					FROM " . DB_PREFIX . "files
					WHERE file_id=$id";
				$image_result = db_result($sql);
				$image = mysql_fetch_object($image_result);
				$thumb = str_replace('/upload/', '/thumbnails/', $image->file_path);
				preg_match("'^(.*)\.(gif|jpe?g|png|bmp)$'i", $thumb, $ext);
				//echo $thumb."<br />";
				if(strtolower($ext[2]) == 'gif')
					$thumb .= '.png';
				
				$succes = true;
				$imgmax = 100;
				if(!file_exists($thumb))
					$succes = generateThumb($image->file_path, $imgmax);
				if(file_exists($thumb) || $succes) {
					$sizes = getimagesize($thumb);
					$margin_top = round(($imgmax - $sizes[1]) / 2);
					$margin_bottom = $imgmax - $sizes[1] - $margin_top;
					$out .= "<div class=\"imageblock\">
							<a href=\"" . generateUrl($image->file_path) . "\">
								<img style=\"margin-top:" . $margin_top . "px;margin-bottom:" . $margin_bottom . "px;width:" . $sizes[0] . "px;height:" . $sizes[1] . "px;\" src=\"" . generateUrl($thumb) . "\" alt=\"$thumb\" />
							</a>
							<br />
							<input type=\"checkbox\" name=\"images[]\" value=\"$id\" checked=\"checked\"/>Auswählen
						</div>";
				}
			}
			$out .= "</td>
			</tr>
			<tr>
				<td colspan=\"2\">
					<a class=\"button\" href=\"" . $_SERVER['PHP_SELF'] . "?page=gallery_editor&amp;action=select\">Zurück</a><input class=\"button\" type=\"submit\" value=\"Erstellen\"/>
				</td>
			</tr>
		</table>
		</form>";
		}
		else {
			$out .= "Bilder Verwalten<br />
				&nbsp;-Hinzufügen/Hochladen<br />
				&nbsp;-Bearbeiten<br />
				&nbsp;-Löschen<br />
			<a href=\"" . $_SERVER['PHP_SELF'] . "?page=gallery_editor&amp;action=select\">Neue Gallerie</a><br />
			<a href=\"" . $_SERVER['PHP_SELF'] . "?page=gallery_editor&amp;action=overview\">Übersicht</a><br />
				&nbsp;-Infos<br />
				&nbsp;-Bearbeiten<br />
				&nbsp;-Löschen";
			
		}
		
		return $out;
	}
/*****************************************************************************
 *
 *  string page_dates()
 *  returns the date-admin-page where you can write,change and delete topic-entries
 *
 *****************************************************************************/
 	function page_dates() {
 		global $admin_lang, $extern_action, $extern_sure, $extern_topic, $extern_date, $extern_place, $extern_id, $_SERVER, $actual_user_id, $actual_user_showname, $user;
		
		if(!isset($extern_action))
			$extern_action = '';
		
		$out = "\t\t\t<h3>" . $admin_lang['dates'] . "</h3><hr />\r\n";
		
		//
		// delete the selected entrie
		//
		if($extern_action == "delete") {
			if(isset($extern_sure)) {
							
				if($extern_sure == 1)
					db_result("DELETE FROM " . DB_PREFIX . "dates WHERE date_id=" . $extern_id);
			}
			else {
				$result = db_result("SELECT * FROM " . DB_PREFIX . "dates WHERE date_id=" . $extern_id);
				$row = mysql_fetch_object($result);
				$out .= "Den News Eintrag &quot;" . $row->date_topic . "&quot; wirklich löschen?<br />
			<a href=\"admin.php?page=dates&amp;action=delete&amp;id=" . $extern_id . "&amp;sure=1\" title=\"Wirklich Löschen\">ja</a> &nbsp;&nbsp;&nbsp;&nbsp;
			<a href=\"admin.php?page=dates\" title=\"Nicht Löschen\">nein</a>";
			
				return $out;
			}
		}
		//
		// add a new entrie
		//
		elseif($extern_action == "new") {
			if($extern_topic != "" && $extern_place != "" && $extern_date != "") {
				$date = explode(".", $extern_date);
				db_result("INSERT INTO ".DB_PREFIX."dates (date_topic, date_place, date_date, date_creator) VALUES ('".$extern_topic."', '".$extern_place."', '".mktime(0, 0, 0, $date[1], $date[0], $date[2])."', '$user->ID')");
			}	
		}
		//
		// update the selected entrie
		//
		elseif($extern_action == "update") { 
			if($extern_topic != "" && $extern_place != "" && $extern_date != "" && $extern_id != 0) {
				$date = explode(".", $extern_date);
				db_result("UPDATE ".DB_PREFIX."dates SET date_topic= '".$extern_topic."', date_place= '".$extern_place."', date_date='".mktime(0, 0, 0, $date[1], $date[0], $date[2])."' WHERE date_id=".$extern_id);
			}
		}
		
		if($extern_action != "edit") {
			$out .= "\t\t\t<form method=\"post\" action=\"admin.php\">
				<input type=\"hidden\" name=\"page\" value=\"dates\" />
				<input type=\"hidden\" name=\"action\" value=\"new\" />
				<table>
					<tr>
						<td>" . $admin_lang['date'] . ": <span class=\"info\">Dies ist das Datum, an dem die Veranstaltung stattfindet (Format: TT.MM.YYYY, Beispiel: 05.11.2005)</span></td>
						<td><input type=\"text\" name=\"date\" maxlength=\"10\" value=\"\" /></td>
					</tr>
					<tr>
						<td>" . $admin_lang['location'] . ": <span class=\"info\">Gemeint ist hier der Ort an welchem die Veranstaltung stattfindet.</span></td>
						<td><input type=\"text\" name=\"place\" maxlength=\"60\" value=\"\" /></td>
					</tr>
					<tr>
						<td>" . $admin_lang['topic'] . ": <span class=\"info\">Dies ist die Beschreibung des Termins</span></td>
						<td><input type=\"text\" name=\"topic\" maxlength=\"150\" /></td>
					</tr>
					<tr>
						<td>Eingelogt als " . $user->Showname . " &nbsp;</td><td><input type=\"submit\" class=\"button\" value=\"Senden\" />&nbsp;<input type=\"reset\" class=\"button\" value=\"Zurücksetzen\" /></td>
					</tr>
				</table>
				<br />
			</form>\r\n";
		}
			$out .= "\t\t\t<form method=\"post\" action=\"admin.php\">
				<input type=\"hidden\" name=\"page\" value=\"dates\" />
				<input type=\"hidden\" name=\"action\" value=\"update\" />
				<table>
					<tr>
						<td>" . $admin_lang['date'] . ":</td>
						<td>" . $admin_lang['location'] . ":</td>
						<td>" . $admin_lang['topic'] . ":</td>
						<td>" . $admin_lang['creator'] . ":</td>
						<td>" . $admin_lang['actions'] . ":</td>
					</tr>\r\n";
		//
		// write all news entries
		//
		$result = db_result("SELECT * FROM " . DB_PREFIX . "dates ORDER BY date_date ASC");
		while($row = mysql_fetch_object($result)) {
			//
			// show an editform for the selected entrie
			//
			if($extern_id == $row->date_id && $extern_action == "edit") {
				$out .= "\t\t\t\t\t<tr id=\"dateid" . $row->date_id . "\">
						<td>
							<input type=\"hidden\" name=\"id\" value=\"".$row->date_id."\" />
							<input type=\"text\" name=\"date\" maxlength=\"10\" value=\"" . date("d.m.Y", $row->date_date) . "\" />
						</td>
						<td>
							<input type=\"text\" name=\"place\" maxlength=\"60\" value=\"" . $row->date_place . "\" />
						</td>
						<td>
							<input type=\"text\" name=\"topic\" value=\"" . $row->date_topic . "\" maxlength=\"150\" />
						</td>
						<td>
							" . getUserByID($row->date_creator) . "
						</td>
						<td>
							<input type=\"submit\" value=\"Speichern\" class=\"button\" />
							&nbsp;<a href=\"admin.php?page=dates&amp;action=delete&amp;id=".$row->date_id."\" title=\"Löschen\">Löschen</a>
						</td>
					</tr>";
			}
			//
			// show only the entrie
			//
			else {
				$out .= "\t\t\t\t\t<tr ID=\"dateid" . $row->date_id . "\">
						<td>
							" . date("d.m.Y", $row->date_date) . "
						</td>
						<td>
							" . $row->date_place . "
						</td>
						<td>
							" . nl2br($row->date_topic) . "
						</td>
						<td>
							" . getUserByID($row->date_creator) . "
						</td>
						<td colspan=\"2\">
							<a href=\"admin.php?page=dates&amp;action=edit&amp;id=".$row->date_id."#dateid".$row->date_id."\" title=\"Bearbeiten\">Bearbeiten</a>
							&nbsp;<a href=\"admin.php?page=dates&amp;action=delete&amp;id=".$row->date_id."\" title=\"Löschen\">Löschen</a>
						</td>
					</tr>\r\n";
			}
		}
		$out .= "\t\t\t\t</table>
			</form>";
	
		return $out;
 	}
/*****************************************************************************
 *
 *  string page_articles()
 *  returns the articles-admin-page where you can write,change and delete article-entries
 *
 *****************************************************************************/
 
 	function page_articles() {
 		global $admin_lang, $extern_action, $extern_sure, $extern_title, $extern_text, $extern_image, $extern_description, $extern_id, $_SERVER, $actual_user_id, $actual_user_showname, $user;
		
		if(!isset($extern_action))
			$extern_action = '';
		
		$out = "\t\t\t<h3>" . $admin_lang['articles'] . "</h3><hr />\r\n";
		
		//
		// delete the selected entrie
		//
		if($extern_action == "delete") {
			if(isset($extern_sure)) {
							
				if($extern_sure == 1)
					db_result("DELETE FROM " . DB_PREFIX . "articles WHERE article_id=" . $extern_id);
			}
			else {
				$result = db_result("SELECT * FROM " . DB_PREFIX . "articles WHERE article_id=" . $extern_id);
				$row = mysql_fetch_object($result);
				$out .= "Den News Eintrag &quot;" . $row->article_title . "&quot; wirklich löschen?<br />
			<a href=\"admin.php?page=articles&amp;action=delete&amp;id=" . $extern_id . "&amp;sure=1\" title=\"Wirklich Löschen\">ja</a> &nbsp;
			<a href=\"admin.php?page=articles\" title=\"Nicht Löschen\">nein</a>";
			
				return $out;
			}
		}
		//
		// add a new entrie
		//
		elseif($extern_action == "new") {
			if($extern_title != "" && $extern_description != "" && $extern_text) {
				$sql = "INSERT INTO ".DB_PREFIX."articles
					(article_title, article_description, article_text, article_html, article_creator, article_date)
					VALUES ('$extern_title', '$extern_description', '$extern_text', '" . convertToPreHtml($extern_text) . "', '$user->ID', '" . mktime() . "')";
				db_result($sql);
			}	
		}
		//
		// update the selected entrie
		//
		elseif($extern_action == "update") { 
			if($extern_title != "" && $extern_description != "" && $extern_text != "" && $extern_id != 0) {
				$sql = "UPDATE ".DB_PREFIX."articles SET 
					article_title= '$extern_title', 
					article_description= '$extern_description', 
					article_text= '$extern_text',
					article_html= '" . convertToPreHtml($extern_text) . "',
					article_date= '" . mktime() . "' 
					WHERE article_id=".$extern_id;
				db_result();
			}
		}
		
		if($extern_action != "edit") {
			$out .= "\t\t\t<form method=\"post\" action=\"admin.php\">
				<input type=\"hidden\" name=\"page\" value=\"articles\" />
				<input type=\"hidden\" name=\"action\" value=\"new\" />
				<table>
					<tr>
						<td>Titel: <span class=\"info\">Hier den Titel des Artikels eingeben</span></td>
						<td><input type=\"text\" name=\"title\" maxlength=\"100\" value=\"\" /></td>
					</tr>
					<tr>
						<td>Beschreibung: <span class=\"info\">Hier eine Zusammenfassung in einem Satz eingeben.</span></td>
						<td><input type=\"text\" name=\"description\" maxlength=\"200\" value=\"\" /></td>
					</tr>
					<tr>
						<td>Text: <span class=\"info\">Hier den gesammten Text des Artikels eingeben.</span></td>
						<td><textarea cols=\"60\" rows=\"6\" name=\"text\"></textarea></td>
					</tr>
					<tr>
						<td>Eingelogt als " . $user->Showname . " &nbsp;</td><td><input type=\"submit\" class=\"button\" value=\"Senden\" />&nbsp;<input type=\"reset\" class=\"button\" value=\"Zurücksetzen\" /></td>
					</tr>
				</table>
				<br />
			</form>\r\n";
		}
		
		if(isset($extern_id) && $extern_action == "edit") {
			$sql = "SELECT * FROM " . DB_PREFIX . "articles WHERE article_id=$extern_id";
			$result = db_result($sql);
			$row = mysql_fetch_object($result);
			$out .= "\t\t\t<form method=\"post\" action=\"admin.php\">
				<input type=\"hidden\" name=\"page\" value=\"articles\" />
				<input type=\"hidden\" name=\"action\" value=\"update\" />
				<table>
					<tr>
						<td>Titel: <span class=\"info\">Hier den Titel des Artikels eingeben</span></td>
						<td><input type=\"text\" name=\"title\" maxlength=\"10\" value=\"" . $row->article_title . "\" /></td>
					</tr>
					<tr>
						<td>Beschreibung: <span class=\"info\">Hier eine Zusammenfassung in einem Satz eingeben.</span></td>
						<td><input type=\"text\" name=\"description\" maxlength=\"60\" value=\"" . $row->article_description . "\" /></td>
					</tr>
					<tr>
						<td>Text: <span class=\"info\">Hier den gesammten Text des Artikels eingeben.</span></td>
						<td><textarea cols=\"60\" rows=\"6\" name=\"text\">" . $row->article_text . "</textarea></td>
					</tr>
					<tr>
						<td>Eingelogt als " . $user->Showname . " &nbsp;</td><td><input type=\"submit\" class=\"button\" value=\"Speichern\" />&nbsp;<input type=\"reset\" class=\"button\" value=\"Zurücksetzen\" /></td>
					</tr>
				</table>
				<br />
			</form>\r\n";
		}
		
		
			$out .= "\t\t\t<form method=\"post\" action=\"admin.php\">
				<input type=\"hidden\" name=\"page\" value=\"articles\" />
				<input type=\"hidden\" name=\"action\" value=\"update\" />
				<table>
					<tr>
						<td>Titel:</td>
						<td>Beschreibung:</td>
						<td>Text:</td>
						<td>" . $admin_lang['date'] . "</td>
						<td>" . $admin_lang['creator'] . ":</td>
						<td>" . $admin_lang['actions'] . ":</td>
					</tr>\r\n";
		//
		// write all news entries
		//
		$result = db_result("SELECT * FROM " . DB_PREFIX . "articles");
		while($row = mysql_fetch_object($result)) {
			if($extern_id == $row->article_id && $extern_action == "edit") {	}
			//
			// show only the entrie
			//
			else {
				$out .= "\t\t\t\t\t<tr ID=\"dateid" . $row->article_id . "\">
						<td>
							" . $row->article_title . "
						</td>
						<td>
							" . $row->article_description . "
						</td>
						<td>
							" . $row->article_html . "
						</td>
						<td>
							" . date("d.m.Y", $row->article_date) . "
						</td>
						<td>
							" . getUserByID($row->article_creator) . "
						</td>
						<td colspan=\"2\">
							<a href=\"admin.php?page=articles&amp;action=edit&amp;id=" . $row->article_id . "#dateid" . $row->article_id . "\" title=\"Bearbeiten\">Bearbeiten</a>
							&nbsp;<a href=\"admin.php?page=articles&amp;action=delete&amp;id=" . $row->article_id . "\" title=\"Löschen\">Löschen</a>
						</td>
					</tr>\r\n";
			}
		}
		$out .= "\t\t\t\t</table>
			</form>";
	
		return $out;
 	}
 	
 /*****************************************************************************
 *
 * string page_inlinemenu()
 * returns the inlinemenu-admin-page where you can add,change and delete inlinemenus
 *
 *****************************************************************************/
 	function page_inlinemenu() {
 		global $extern_action, $_SERVER, $admin_lang, $extern_page_id, $extern_sure, $extern_inlinemenu_id, $extern_image_path, $extern_entrie_type, $extern_entrie_text, $extern_entrie_link, $extern_image_path, $extern_entrie_id;
 		
		$out = '<h3>' . $admin_lang['inlinemenu'] . '</h3><hr />';
 		if($extern_action == 'new') {
 			$sql = "SELECT *
				FROM " . DB_PREFIX . "pages_content
				WHERE page_id=$extern_page_id";
			$page_result = db_result($sql);
			$page = mysql_fetch_object($page_result);
			$sql = "INSERT INTO " . DB_PREFIX . "inlinemenu (inlinemenu_image)
				VALUES('')";
			$res = db_result($sql);
			//$rr = mysql_fetch_object($r);
			$lastid =  mysql_insert_id();
			$sql = "UPDATE " . DB_PREFIX . "pages_content
				SET page_inlinemenu=$lastid
				WHERE page_id='$extern_page_id'";
			db_result($sql);
			header("Location: " . $_SERVER['PHP_SELF'] . "?page=inlinemenu");
			
 		}
 		elseif($extern_action == 'edit') {
 			$sql = "SELECT cont.*, inline.*
				FROM ( " . DB_PREFIX. "pages_content cont
				LEFT JOIN " . DB_PREFIX . "inlinemenu inline ON inline.inlinemenu_id = cont.page_inlinemenu )
				WHERE inline.inlinemenu_id=$extern_inlinemenu_id";
 			$imenu_result = db_result($sql);
 			$imenu = mysql_fetch_object($imenu_result);
			if($extern_image_path == "")
				$image_path = $imenu->inlinemenu_image;
			else
				$image_path = $extern_image_path;
 			$out .= "<h4>Neues Zusatzmenü für die Seite &quot;<a href=\"index.php?page=$imenu->page_name\">$imenu->page_title</a>&quot; erstellen</h4>
			<form action=\"" . $_SERVER['PHP_SELF'] . "\">
			<input type=\"hidden\" name=\"page\" value=\"inlinemenu\"/>
			<input type=\"hidden\" name=\"action\" value=\"save_image\"/>
			<input type=\"hidden\" name=\"inlinemenu_id\" value=\"$imenu->inlinemenu_id\"/>
			<table>
				<tr>
					<td>Pfad zum Bild:<span class=\"info\">Das ist der Pfad zu dem Bild, das dem Zusatzmenü zugeordnet wird, es kann der Einfachheit halber aus den bereits hochgeladenen Bildern ausgweählt werden.</span></td>
					<td><input type=\"text\" name=\"image_path\" value=\"$image_path\"/> <a href=\"" . $_SERVER['PHP_SELF'] . "?page=inlinemenu&amp;action=select_image&amp;inlinemenu_id=$imenu->inlinemenu_id\">[Bild auswählen]</a></td>
				</tr>
				<tr>
					<td colspan=\"2\"><input type=\"submit\" class=\"button\" value=\"" . $admin_lang['save'] . "\"/></td>
				</tr>
			</table>
		</form><table>
		<tr><td>Text</td><td>Typ</td><td>Aktion</td></tr>";
			$sql = "SELECT *
				FROM " . DB_PREFIX . "inlinemenu_entries
				WHERE inlineentrie_menu_id=$imenu->inlinemenu_id
				ORDER BY inlineentrie_sortid ASC";
			$entries_result = db_result($sql);
			while($entrie = mysql_fetch_object($entries_result)) {
				$out .= "<tr>
					<td>$entrie->inlineentrie_text</td>
					<td>$entrie->inlinieentrie_type</td>
					<td>
						<a href=\"" . $_SERVER['PHP_SELF'] . "?page=inlinemenu&amp;action=entrie_up&amp;entrie_id=$entrie->inlineentrie_id\"><img src=\"./img/up.jpg\" alt=\"Hoch\" title=\"Hoch\" /></a>
						<a href=\"" . $_SERVER['PHP_SELF'] . "?page=inlinemenu&amp;action=entrie_down&amp;entrie_id=$entrie->inlineentrie_id\"><img src=\"./img/down.jpg\" alt=\"Runter\" title=\"Runter\" /></a>
						<a href=\"" . $_SERVER['PHP_SELF'] . "?page=inlinemenu&amp;action=delete_entrie&amp;entrie_id=$entrie->inlineentrie_id\"><img src=\"./img/del.jpg\" alt=\"Löschen\" title=\"Löschen\" /></a>
						<!--<img src=\"./img/edit.png\" alt=\"Bearbeiten\" title=\"Bearbeiten\" />-->
					</td>
					</tr>";
			}
			$out .= "
		</table>
		<form action=\"" . $_SERVER['PHP_SELF'] . "\">
			<input type=\"hidden\" name=\"page\" value=\"inlinemenu\"/>
			<input type=\"hidden\" name=\"action\" value=\"add_entrie\"/>
			<input type=\"hidden\" name=\"inlinemenu_id\" value=\"$imenu->inlinemenu_id\"/>
			<table>
			<tr>
			<td>Typ:<span class=\"info\">Über den Typ kann lässt sich bestimmen ob der neue Eintrag ein Link auf eine externe oder interne Seite sein soll oder nur ein kurzer Text, der eine Information weitergibt.</span></td>
			<td><select name=\"entrie_type\">
				<option value=\"link\">Link</option>
				<option value=\"text\">Text</option>";
			$sql = "SELECT page_type, page_name, page_title
				FROM " . DB_PREFIX . "pages_content
				ORDER BY page_type ASC";
			$pages_result = db_result($sql);
			while($page = mysql_fetch_object($pages_result)) {
				$out .= "\t\t\t\t<option value=\"" . (($page->page_type == 'gallery' ) ? 'g' : 'l' ) . ":$page->page_name\">Interne " . (($page->page_type == 'gallery' ) ? 'Gallerie:' : 'Seite' ) . ": $page->page_title($page->page_name)</option>\r\n";
			}
//				<!--<option value=\"download\">Download</option>-->
			$out .= "</select></td>
			</tr>
			<tr><td>Text:<span class=\"info\">Dieses Feld beinhaltet den Text, mit dem der Link, egal ob extern oder intern, beschriftet wird, wenn der Typ auf Text gestellt ist, wird der Text einfach so angezeigt.</span></td><td><input type=\"text\" name=\"entrie_text\"/></td></tr>
			<tr><td>Link:<span class=\"info\">Dieses Feld muss nur ausgefüllt werden, wenn im Typ der Typ Link ausgewählt worden ist, es beinhaltet den Link auf die Seite, auf die der Link im Zusatzmenü führen soll.</span></td><td><input type=\"text\" name=\"entrie_link\"/></td></tr>
				<tr>
					<td colspan=\"2\"><input type=\"submit\" class=\"button\" value=\"Hinzufügen\"/></td>
				</tr>
			</table>
		</form>";
		
 		}
 		elseif($extern_action == 'entrie_up') {
 			$sql = "SELECT *
			 	FROM " . DB_PREFIX . "inlinemenu_entries
				WHERE inlineentrie_id=$extern_entrie_id";
			$self_result = db_result($sql);
			$self_data = mysql_fetch_object($self_result);
			$id1 = $self_data->inlineentrie_id;
			
			$sortid1 = $self_data->inlineentrie_sortid;
			
			$sql = "SELECT *
				FROM " . DB_PREFIX . "inlinemenu_entries
				WHERE inlineentrie_sortid < $sortid1 AND inlineentrie_menu_id=$self_data->inlineentrie_menu_id
				ORDER BY inlineentrie_sortid DESC";
			$pre_result = db_result($sql);
			$pre_data = mysql_fetch_object($pre_result);
		
			if($pre_data != null) {

				$id2 = $pre_data->inlineentrie_id;
				$sortid2 = $pre_data->inlineentrie_sortid;
				$sql = "UPDATE " . DB_PREFIX . "inlinemenu_entries
					SET inlineentrie_sortid=$sortid2
					WHERE inlineentrie_id=$id1";
				db_result($sql);
				$sql = "UPDATE " . DB_PREFIX . "inlinemenu_entries
					SET inlineentrie_sortid=$sortid1
					WHERE inlineentrie_id=$id2";
				db_result($sql);
			}
			generateinlinemenu($self_data->inlineentrie_menu_id);
			header("Location: " . $_SERVER['PHP_SELF'] . "?page=inlinemenu&action=edit&inlinemenu_id=$self_data->inlineentrie_menu_id");
 		}
 		elseif($extern_action == 'entrie_down') {
 			
 			$sql = "SELECT *
			 	FROM " . DB_PREFIX . "inlinemenu_entries
				WHERE inlineentrie_id=$extern_entrie_id";
			$self_result = db_result($sql);
			$self_data = mysql_fetch_object($self_result);
			$id1 = $self_data->inlineentrie_id;
			
			$sortid1 = $self_data->inlineentrie_sortid;
			
			$sql = "SELECT *
				FROM " . DB_PREFIX . "inlinemenu_entries
				WHERE inlineentrie_sortid > $sortid1 AND inlineentrie_menu_id=$self_data->inlineentrie_menu_id
				ORDER BY inlineentrie_sortid ASC";
			$pre_result = db_result($sql);
			$pre_data = mysql_fetch_object($pre_result);
		
			if($pre_data != null) {

				$id2 = $pre_data->inlineentrie_id;
				$sortid2 = $pre_data->inlineentrie_sortid;
				$sql = "UPDATE " . DB_PREFIX . "inlinemenu_entries
					SET inlineentrie_sortid=$sortid2
					WHERE inlineentrie_id=$id1";
				db_result($sql);
				$sql = "UPDATE " . DB_PREFIX . "inlinemenu_entries
					SET inlineentrie_sortid=$sortid1
					WHERE inlineentrie_id=$id2";
				db_result($sql);
			}
 			generateinlinemenu($self_data->inlineentrie_menu_id);
			header("Location: " . $_SERVER['PHP_SELF'] . "?page=inlinemenu&action=edit&inlinemenu_id=$self_data->inlineentrie_menu_id");
 		}
 		elseif($extern_action == 'delete_entrie') {
 			$sql = "SELECT *
				FROM " . DB_PREFIX . "inlinemenu_entries
				WHERE inlineentrie_id=$extern_entrie_id";
 			$entrie_result = db_result($sql);
 			$entrie = mysql_fetch_object($entrie_result);
 			if($extern_sure == 1) {
 				$sql = "DELETE FROM " . DB_PREFIX . "inlinemenu_entries
					WHERE inlineentrie_id=$extern_entrie_id";
 				db_result($sql);
	 			generateinlinemenu($entrie->inlineentrie_menu_id);
 				header("Location: " . $_SERVER['PHP_SELF'] . "?page=inlinemenu&action=edit&inlinemenu_id=$entrie->inlineentrie_menu_id");
 			}
 			else {
				$out .= "Sind sie sicher das die das Element &quot;$entrie->inlineentrie_text&quot; unwiederruflich löschen?<br />
				<a href=\"" . $_SERVER['PHP_SELF'] . "?page=inlinemenu&amp;action=delete_entrie&amp;entrie_id=$extern_entrie_id&amp;sure=1\">" . $admin_lang['yes'] . "</a > <a href=\"" . $_SERVER['PHP_SELF'] . "?page=inlinemenu&amp;action=edit&amp;inlinemenu_id=$entrie->inlineentrie_menu_id\">" . $admin_lang['no'] . "</a >";
			}
 			
 		}
 		elseif($extern_action == 'add_entrie') {
 			$sql = "SELECT inlineentrie_sortid
			 	FROM " . DB_PREFIX . "inlinemenu_entries
			 	WHERE inlineentrie_menu_id = $extern_inlinemenu_id
			 	ORDER BY inlineentrie_sortid DESC";
			$lastsort_result = db_result($sql);
			$sortid = 1;
			if($lastsort = mysql_fetch_object($lastsort_result)){
				$sortid = $lastsort->inlineentrie_sortid;
				$sortid++;
			}
			
			$sql = '';
			if($extern_entrie_type == 'text') {
 				$sql = "INSERT INTO " . DB_PREFIX . "inlinemenu_entries (inlineentrie_sortid, inlineentrie_menu_id, inlinieentrie_type, inlineentrie_text)
					VALUES ($sortid, $extern_inlinemenu_id, 'text', '$extern_entrie_text');";
 			}
 			elseif($extern_entrie_type == 'link') {
				$sql = "INSERT INTO " . DB_PREFIX . "inlinemenu_entries (inlineentrie_sortid, inlineentrie_menu_id, inlinieentrie_type, inlineentrie_text, inlineentrie_link)
					VALUES ($sortid, $extern_inlinemenu_id, 'link', '$extern_entrie_text','$extern_entrie_link');";
 			}
 			elseif(substr($extern_entrie_type, 1, 1) == ':') {
 				$link = ( (substr($extern_entrie_type, 0, 1) == 'g') ? 'gallery.php' : 'index.php' ) . "?page=" . substr($extern_entrie_type, 2);
 				$sql = "INSERT INTO " . DB_PREFIX . "inlinemenu_entries (inlineentrie_sortid, inlineentrie_menu_id, inlinieentrie_type, inlineentrie_text, inlineentrie_link)
					VALUES ($sortid, $extern_inlinemenu_id, 'intern', '$extern_entrie_text','$link');";	
 			}
 			if($sql != '')
 				db_result($sql);
 			generateinlinemenu($extern_inlinemenu_id);
 			header("Location: " . $_SERVER['PHP_SELF'] . "?page=inlinemenu&action=edit&inlinemenu_id=$extern_inlinemenu_id");
 		}
 		elseif($extern_action == 'select_image') {
 		 	$sql = "SELECT *
				FROM " . DB_PREFIX . "files
				WHERE file_type LIKE 'image/%'
				ORDER BY file_name ASC";
			$images_result = db_result($sql);
			$out .= "<form action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"get\">
			<input type=\"hidden\" name=\"page\" value=\"inlinemenu\"/>
			<input type=\"hidden\" name=\"action\" value=\"edit\"/>
			<input type=\"hidden\" name=\"inlinemenu_id\" value=\"$extern_inlinemenu_id\"/>";
			while($image = mysql_fetch_object($images_result)) {
				$thumb = str_replace('/upload/', '/thumbnails/', $image->file_path);
				preg_match("'^(.*)\.(gif|jpe?g|png|bmp)$'i", $thumb, $ext);
				if(strtolower($ext[2]) == 'gif')
					$thumb .= '.png';
					
				$succes = true;
				$imgmax = 100;
				if(!file_exists($thumb))
					$succes = generateThumb($image->file_path, $imgmax);
				if(file_exists($thumb) || $succes) {
					$sizes = getimagesize($thumb);
					$margin_top = round(($imgmax - $sizes[1]) / 2);
					$margin_bottom = $imgmax - $sizes[1] - $margin_top;
					$out .= "<div class=\"imageblock\">
					<a href=\"" . generateUrl($image->file_path) . "\">
					<img style=\"margin-top:" . $margin_top . "px;margin-bottom:" . $margin_bottom . "px;width:" . $sizes[0] . "px;height:" . $sizes[1] . "px;\" src=\"" . generateUrl($thumb) . "\" alt=\"$thumb\" /></a><br />
					<input type=\"radio\" name=\"image_path\" value=\"$image->file_path\"/>Auswählen</div>";
				}
			}
			$out .="<input type=\"submit\" value=\"Übernehmen\" /></form>";
 		}
 		elseif($extern_action == 'save_image') {
 			$sql = "UPDATE " . DB_PREFIX . "inlinemenu
				SET inlinemenu_image='$extern_image_path'
				WHERE inlinemenu_id=$extern_inlinemenu_id";
			db_result($sql);
			header("Location: " . $_SERVER['PHP_SELF'] . "?page=inlinemenu&action=edit&inlinemenu_id=$extern_inlinemenu_id");
 		}
 		elseif($extern_action == 'delete') {
 			$sql = "SELECT *
				FROM " . DB_PREFIX . "pages_content
				WHERE page_id=$extern_page_id";
			$page_result = db_result($sql);
			$page = mysql_fetch_object($page_result);
			if($extern_sure == 1) {
				//
				// Remove all inlinemenu_entries of the inlinemenu which is to delete
				//
				$sql = "DELETE FROM " . DB_PREFIX . "inlinemenu_entries
					WHERE inlineentrie_menu_id=$page->page_inlinemenu";
				db_result($sql);
				//
				// Remove the inlinemenu
				//
				$sql = "DELETE FROM " . DB_PREFIX . "inlinemenu
					WHERE inlinemenu_id=$page->page_inlinemenu";
				db_result($sql);
				//
				// Remove the inlinemenu_id from the page
				//
				$sql = "UPDATE " . DB_PREFIX . "pages_content
					SET page_inlinemenu=-1
					WHERE page_id=$extern_page_id";
				db_result($sql);
				header("Location: " . $_SERVER['PHP_SELF'] . "?page=inlinemenu");
			}
			else {
				$out .= "Sind sie sicher, dass sie das Zusatzmenü für die Seite &quot;$page->page_title&quot; unwiederruflich entfernen wollen.<br />
				<a href=\"" . $_SERVER['PHP_SELF'] . "?page=inlinemenu&amp;action=delete&amp;page_id=$page->page_id&amp;sure=1\">" . $admin_lang['yes'] . "</a>&nbsp;<a href=\"" . $_SERVER['PHP_SELF'] . "?page=inlinemenu\">" . $admin_lang['no'] . "</a>";	
			}
			
			 
 		}
 		else {
			$sql = "SELECT cont.*, inline.*
				FROM ( " . DB_PREFIX. "pages_content cont
				LEFT JOIN " . DB_PREFIX . "inlinemenu inline ON inline.inlinemenu_id = cont.page_inlinemenu )
				WHERE cont.page_visible!='deleted'
				ORDER BY cont.page_id";
			$pages_result = db_result($sql);
			while($page = mysql_fetch_object($pages_result)) {
				if($page->page_inlinemenu == -1)
					$out .= "$page->page_title <a href=\"" . $_SERVER['PHP_SELF'] . "?page=inlinemenu&amp;action=new&amp;page_id=$page->page_id\">[Erstellen]</a><br />";
				else
					$out .= "$page->page_title <a href=\"" . $_SERVER['PHP_SELF'] . "?page=inlinemenu&amp;action=edit&amp;inlinemenu_id=$page->page_inlinemenu\">[Bearbeiten]</a>  <a href=\"" . $_SERVER['PHP_SELF'] . "?page=inlinemenu&amp;action=delete&amp;page_id=$page->page_id\">[Entfernen]</a><br />";
			}	
		}
 		return $out;
 	}
?>