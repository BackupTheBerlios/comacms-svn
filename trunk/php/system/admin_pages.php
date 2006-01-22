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
/*
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
			$out .= "\t\t\t\t<li><a href=\"admin.php?page=menueeditor&amp;menue_id=1\">Men� 1</a></li>
				<li><u>Men� 2</u></li>\r\n";
		else {
			$out .= "\t\t\t\t<li><u>Men� 1</u></li>
				<li><a href=\"admin.php?page=menueeditor&amp;menue_id=2\">Men� 2</a></li>\r\n";
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
					$out .= "\t\t\t<div class=\"error\">Soll der Link ".$_data->text."(".$_data->link.") wirklich gel�scht werden?<br />
			<a href=\"admin.php?page=menueeditor&amp;action=delete&amp;menue_id=".$menue_id."&amp;id=".$id."&amp;sure=1\" title=\"Wirklich L�schen?\">Ja</a> &nbsp;&nbsp;&nbsp; <a href=\"admin.php?page=menueeditor&amp;menue_id=".$menue_id."\" title=\"Nein! nicht l�schen\">Nein</a></div>";
					
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
						<td colspan=\"2\"><input type=\"submit\" value=\"Hinzuf�gen\" /></td>
					</tr>
				</table>
			</form>";
	
		return $out;
	}*/
/*****************************************************************************
 *
 * string page_sitesytle()
 * returns the sitestyle(changer)-page with a preview-iframe and a form which make it able o change the style
 *
 *****************************************************************************/
	function page_sitestyle() {
		global $internal_style, $extern_save, $extern_style, $config;
		
		$out = "<script type=\"text/javascript\" language=\"JavaScript\" src=\"./system/functions.js\"></script>";
		$save = GetPostOrGet('save');
		$style = GetPostOrGet('style_intern');
		if(!empty($save))
			$extern_style = $config->Get('style');
	
		if(isset($extern_save)) {
			if(file_exists("./styles/$style/mainpage.php")) {
				$sql = "UPDATE " . DB_PREFIX . "config
					SET config_value= '$style'
					WHERE config_name='style'";
				db_result($sql);
			}
		}
	
		$out .= "<iframe id=\"previewiframe\" src=\"./index.php?style=$style\" class=\"stylepreview\"></iframe>
		<form action=\"admin.php\" method=\"get\" >
			<input type=\"hidden\" name=\"page\" value=\"sitestyle\" />
			<label for=\"stylepreviewselect\">Style:
				<select id=\"stylepreviewselect\" name=\"style_intern\" size=\"1\">";
	
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

			<input type=\"submit\" value=\"Vorschau\" onclick=\"preview_style();return false;\" class=\"button\" />
			<input type=\"submit\" value=\"Speichern\" name=\"save\" class=\"button\" />

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
		global $_GET, $_POST, $actual_user_showname, $actual_user_id, $user;
		
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
					$out .= "Den News Eintrag &quot;" . $row->title . "&quot; wirklich l&ouml;schen?<br />
				<a href=\"admin.php?page=news&amp;action=delete&amp;id=" . $id . "&amp;sure=1\" title=\"Wirklich L&ouml;schen\" class=\"button\">Ja</a>
				<a href=\"admin.php?page=news\" title=\"Nicht L�schen\" class=\"button\">Nein</a>";
				
					return $out;
				}
			}
			//
			// add a new entrie
			//
			elseif($action == "new") {
				if($text != "" && $title != "")
					db_result("INSERT INTO ".DB_PREFIX."news (title, text, date, userid) VALUES ('".$title."', '".$text."', '".mktime()."', '$user->id')");
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
			Eingelogt als " . $user->showname . " <input class=\"button\" type=\"submit\" value=\"Senden\" /><br />
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
						<input type=\"submit\" value=\"Speichern\" class=\"button\" />
						<a href=\"admin.php?page=news&amp;action=delete&amp;id=".$row->id."\" title=\"L&ouml;schen\"  class=\"button\">L&ouml;schen</a>
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
						<a href=\"admin.php?page=news&amp;action=edit&amp;id=".$row->id."#newsid".$row->id."\" title=\"Bearbeiten\" class=\"button\">Bearbeiten</a>
						<a href=\"admin.php?page=news&amp;action=delete&amp;id=".$row->id."\" title=\"L&ouml;schen\" class=\"button\">L&ouml;schen</a>
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
					if($user_id == $user->id) {
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
					
					if($sure == 1 && $user_id != $user->id) {
						$sql = "SELECT *
							FROM " . DB_PREFIX . "users
							WHERE user_id=$user_id";
						$result = db_result($sql);
						$user_data = mysql_fetch_object($result);
						$sql = "DELETE FROM " . DB_PREFIX . "users
							WHERE user_id=$user_id";
						db_result($sql);
						$out .= "Der Benutzer &quot;" . $user_data->user_showname . "&quot; ist nun unwiederuflich gel?scht worden!<br />";
					}
				}
				else {
					$sql = "SELECT *
						FROM " . DB_PREFIX . "users
						WHERE user_id=$user_id";
					$result = db_result($sql);
					$user = mysql_fetch_object($result);
					$out .= "Den Benutzer &quot;" . $user->user_showname . "&quot; unwiederruflich l�schen?<br />
				<a href=\"admin.php?page=users&amp;action=delete&amp;user_id=" . $user_id . "&amp;sure=1\" title=\"Wirklich L�schen\" class=\"button\">" . $admin_lang['yes'] . "</a>
				<a href=\"admin.php?page=users\" title=\"Nicht L�schen\" class=\"button\">" . $admin_lang['no'] . "</a>";
					
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
					$out .= "\t\t\t\t\t\t\t<span class=\"info\">Mit dem Nick kann sich der Benutzer einloggen, so muss er nicht seinen unter Umst�nden komplizierten Namen,der angezeigt wird, eingeben muss. (Notwendig)</span>
						</td>
						<td>
							<input type=\"text\" name=\"user_name\" value=\"".$user_name."\" />
						</td>
					</tr>
					<tr>
						<td>
							E-Mail:\r\n";
					if($action == "add-error" || $action == "save-error" && $user_email != "" && !isEMailAddress($user_email))
						$out .= "\t\t\t\t\t\t\t<span class=\"error\">Die Angegebene E-Mail-Adresse ist ung�ltig.</span>\r\n";		
					$out .= "\t\t\t\t\t\t\t<span class=\"info\">�ber die E-Mail-Adresse wird der Benutzer kontaktiert. Sie ist also notwendig.</span>
						</td>
						<td>
							<input type=\"text\" name=\"user_email\" value=\"".$user_email."\" />
						</td>
					</tr>
					<tr>
						<td>
							ICQ:\r\n";
					if(($action == "add-error" || $action == "save-error") && ($user_icq != "" && !isIcqNumber($user_icq)))
						$out .= "\t\t\t\t\t\t\t<span class=\"error\">Die Angegebene ICQ-Nummer ist ung�ltig.</span>\r\n";		
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
					$out .= "\t\t\t\t\t\t\t<span class=\"info\">Mit diesem Passwort kann sich der Benutzer in die gesch�tzten Bereiche einloggen. (";
					if($action == "save-error" || $action == "edit")
						$out .= "Wenn beide Felder f�r das Passwort leer gelassen werden, wird das Passwort nicht ver�ndert.";
					elseif($action == "add-error" || $action == "new")
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
							<span class=\"info\">Ist ein Benutzer Administrator so hat er keinerlei Einschr�nkungen in seinem Handeln. <strong>Nur ausw�hlen wenn es wirklich Notwendig ist.</strong></span>
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
			$out .= "\t\t\t<table>
				<tr>
					<td>id</td>
					<td>" . $admin_lang['name'] . "</td>
					<td>K�rzel</td>
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
					
					if($user->id == $user_db->user_id)
						$out .= "&nbsp;";
					else
						$out .= "<a href=\"".$PHP_SELF."?page=users&amp;action=delete&amp;user_id=".$user_db->user_id."\" ><img src=\"./img/del.png\" height=\"16\" width=\"16\" alt=\"" . $admin_lang['delete'] . "\" title=\"" . $admin_lang['delete'] . "\"/></a>";
					$out .= "</td>
				</tr>\r\n";
			}
			//<tr><td colspan="7"><a href="<?php echo $PHP_SELF."?newuser=y"; " />Neuen User hinzuf&uuml;gen</a></td></tr>
			$out .= "\t\t\t</table>
			<a href=\"" . $PHP_SELF . "?page=users&amp;action=new\" title=\"Einen neuen Benutzer erstellen\" class=\"button\">Neuen Benutzer erstellen</a>";
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
		global $setting, $_SERVER, $admin_lang, $extern_action, $_GET, $_POST, $config;
		/**
		 * Load the template file for the preferences
		 */
		// TODO: rewrite the whole thing here into an own class
		include('./system/settings.php');
		$out = '';
		$empty_allowed = array();
		$empty_allowed[] = 'news_time_format';
		if(!isset($extern_action))
			$extern_action = '';
		if($extern_action == 'save') {
			$tosave = array();
			foreach($_GET as $key => $value) {
				if(substr($key, 0, 8) == 'setting_'){
					$name = substr($key, 8);
					$conf_value = $config->Get($name);
					if($conf_value != $_GET[$key])
						$tosave[$name] = $value;
				}
			
			}
			foreach($_POST as $key => $value) {
				if(substr($key, 0, 8) == 'setting_'){
					$name = substr($key, 8);
					$conf_value = $config->Get($name);
					if($conf_value != $_POST[$key])
						$tosave[$name] = $value;
				}
			}
			foreach($tosave as $key => $value) {
				$conf_value = $config->Get($key);
				if($key == 'news_display_count' && !is_numeric($value))
					$value = $conf_value;
				if($conf_value == '' && !in_array($key, $empty_allowed)) {
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
				$_value = $config->Get($key);
				if($_value == '' && !in_array($key, $empty_allowed))
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


?>
