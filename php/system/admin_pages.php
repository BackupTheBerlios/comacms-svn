<?
/* adminpages.php
 * This file contains 'all' subsites in the admin-interface
 */

/* string page_adminconrol()
 * returns the AdminControl-page with a list of useful details about the page
 * and a list of all visitors
 */
function page_admincontrol()
{
	global $d_pre,$admin_lang;
	//get the coutnt of all pages
	$sitedata_result = db_result("SELECT * FROM ".$d_pre."sitedata");
	$page_count = mysql_num_rows($sitedata_result);
	//get the count of all registered users
	$users_result = db_result("SELECT * FROM ".$d_pre."users");
	$users_count = mysql_num_rows($users_result);
	//get the size of all tables with the prefix $d_pre
	$table_infos_result = db_result("SHOW TABLE STATUS");
	$data_size = 0;
	while($table_infos = mysql_fetch_object($table_infos_result)) {
		if(substr($table_infos->Name, 0, strlen($d_pre)) == $d_pre)
			$data_size += $table_infos->Data_length + $table_infos->Index_length;
	}
	
	$out = "<h3>AdminControl</h3><hr />
	<table>
		<tr><td>".$admin_lang['online since']."</td><td>#DATUM</td></tr>
		<tr><td>".$admin_lang['registered users']."</td><td>".$users_count."</td></tr>
		<tr><td>".$admin_lang['created pages']."</td><td>".$page_count."</td></tr>
		<tr><td>".$admin_lang['database size']."</td><td>".kbormb($data_size)."</td></tr>
	</table>
	
	<h3>Aktuelle Besucher</h3><hr />
	<table>
		<tr>
			<td>".$admin_lang['name']."</td>
			<td>".$admin_lang['page']."</td>
			<td>".$admin_lang['last action']."</td>
			<td>".$admin_lang['language']."</td>
			<td>".$admin_lang['ip']."</td>
		</tr>";
		//output all visitors surfing on the site
		$users_online_result = db_result("SELECT * FROM ".$d_pre."online");
		while($users_online = mysql_fetch_object($users_online_result))
		{
			if($users_online->userid == 0)
				$username  = $admin_lang['not registered'];
			else
				$username = getUserById($users_online->userid);
			$out .= "\t\t\t<tr>
			<td>".$username."</td>
			<td><a href=\"index.php?site=".$users_online->page."\">".$users_online->page."</a></td>
			<td>".date("d.m.Y H:i:s", $users_online->lastaction)."</td>
			<td>".$users_online->lang."</td>
			<td>".$users_online->ip."</td>
		</tr>\r\n";
		}

	$out .= "</table>";
	
	return $out;
}
/* string page_sitepreview()
 * returns the Sitepreview-page where you can see the 'real' site in a iframe
 */
function page_sitepreview()
{
	global $admin_lang;
	$out = "<h3>".$admin_lang['sitepreview']."</h3><hr /><iframe src=\"index.php\" class=\"sitepreview\"></iframe>";
	return $out;
}
/* string page_menueeditor()
 * returns the Menueeditor-page whith the followong functionalitys:
 * -$action=up -> changes the place in the menue order with the item before the selected
 * -$action=down -> changes the place in the menue order with the item after the selected
 * -$action=delete -> removes the item from the menue but it asks if it's sure that the item should be deleted
 * -$action=add -> adds a new item at the end of the menue
 *
 * it is possible to choose between two menues by $menue_id
 */
function page_menueeditor()
{
	global $_GET,$_POST, $admin_language, $d_pre;
	$menue_id = 0;
	$out = "\t\t\t<h3>Menueeditor</h3><hr />	
			<ul>\r\n";
	
	if(isset($_GET['menue_id']) || isset($_POST['menue_id']))
	{
		if(isset($_GET['menue_id']))
			$menue_id = $_GET['menue_id'];
		else
			$menue_id = $_POST['menue_id'];
	}
	//write the 'coose menue'  to make it able to switch betwen both possible menues
	if($menue_id == "2")
		$out .= "\t\t\t\t<li><a href=\"admin.php?site=menueeditor&amp;menue_id=1\">Menü 1</a></li>
				<li><u>Menü 2</u></li>\r\n";
	else
	{
		$out .= "\t\t\t\t<li><u>Menü 1</u></li>
				<li><a href=\"admin.php?site=menueeditor&amp;menue_id=2\">Menü 2</a></li>\r\n";
		$menue_id = 1;
	}
 			$out .= "\t\t\t</ul>\r\n";
	
	if(isset($_GET['action']) || isset($_POST['action']))
	{
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
		//put the item one position higher	
		if($action == "up")
		{
			$_result = db_result("SELECT * FROM ".$d_pre."menue WHERE id=".$id."");
			$_data = mysql_fetch_object($_result);
			$id1 = $_data->id;
			$orderid1 = $_data->orderid; //get the orderid to find the follownig menue item
			
			$_result2 = db_result("SELECT * FROM ".$d_pre."menue WHERE orderid <".$orderid1." AND menue_id=".$menue_id." ORDER BY orderid DESC");
			$_data2 = mysql_fetch_object($_result2);
			
			if($_data2 != null) //switch the orderids to cange the order of this two menue items
			{
				$id2 = $_data2->id;
				$orderid2 = $_data2->orderid;
				db_result("UPDATE ".$d_pre."menue SET orderid= ".$orderid2." WHERE id=".$id1);
				db_result("UPDATE ".$d_pre."menue SET orderid= ".$orderid1." WHERE id=".$id2);
			}
		}
		//put the item one position lower
		elseif($action == "down")
		{
			$_result = db_result("SELECT * FROM ".$d_pre."menue WHERE id=".$id."");
			$_data = mysql_fetch_object($_result);
			$id1 = $_data->id;
			$orderid1 = $_data->orderid;
			
			$_result2 = db_result("SELECT * FROM ".$d_pre."menue WHERE orderid >".$orderid1." AND menue_id=".$menue_id." ORDER BY orderid ASC");
			$_data2 = mysql_fetch_object($_result2);
			
			if($_data2 != null)
			{
				$id2 = $_data2->id;
				$orderid2 = $_data2->orderid;
				db_result("UPDATE ".$d_pre."menue SET orderid= ".$orderid2." WHERE id=".$id1);
				db_result("UPDATE ".$d_pre."menue SET orderid= ".$orderid1." WHERE id=".$id2);
			}
		}
		//remove the selected item
		elseif($action == "delete")
		{
			
			if(isset($_GET['sure']) || isset($_POST['sure']))
			{
				if(isset($_GET['sure']))
					$sure = $_GET['sure'];
				else
					$sure = $_POST['sure'];
				if($sure == 1)
					db_result("DELETE FROM ".$d_pre."menue WHERE id=".$id."");
			}
			else
			{
				$_result = db_result("SELECT * FROM ".$d_pre."menue WHERE id=".$id."");
				$_data = mysql_fetch_object($_result);
				$out .= "\t\t\t<div class=\"error\">Soll der Link ".$_data->text."(".$_data->link.") wirklich gelöscht werden?<br />
			<a href=\"admin.php?site=menueeditor&amp;action=delete&amp;menue_id=".$menue_id."&amp;id=".$id."&amp;sure=1\" title=\"Wirklich Löschen?\">Ja</a> &nbsp;&nbsp;&nbsp; <a href=\"admin.php?site=menueeditor&amp;menue_id=".$menue_id."\" title=\"Nein! nicht löschen\">Nein</a></div>";
				return $out;
			}
		}
		//add a new item
		elseif($action == "add")
		{
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
			if($link != "" && $caption != "")
			{
				if($new_window == "on")
					$new = "yes";
				else
					$new = "no";
				$menue_result = db_result("SELECT orderid FROM ".$d_pre."menue WHERE menue_id = ".$menue_id." ORDER BY orderid DESC");
				$menue_data = mysql_fetch_object($menue_result);
				if($menue_data != null)
					$ordid = $menue_data->orderid + 1;
				else
					$ordid = 0;
				
				db_result("INSERT INTO ".$d_pre."menue (text, link, new, orderid,menue_id) VALUES ('".$caption."', '".$link."', '".$new."', ".$ordid.",$menue_id)");
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
	//add the menueedit part where you can select
	$out .= menue_edit_view($menue_id);
	$out .= "\t\t\t\t</tbody>
			</table><br />\r\n";
	$out .= "\t\t\t<form method=\"get\" action=\"admin.php\">
				<input type=\"hidden\" name=\"site\" value=\"menueeditor\" />
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
	//list all available pages 
	$site_result = db_result("SELECT * FROM ".$d_pre."sitedata WHERE visible!='deleted' ORDER BY name ASC");
	while($site_data = mysql_fetch_object($site_result))
	{
		$out.= "\t\t\t\t\t\t\t\t<option value=\"".$site_data->name."\">".$site_data->title."(".$site_data->name.")</option>\r\n";
	}	
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
/* string page_sitesytle()
 * returns the sitestyle(changer)-page with a preview-iframe and a form which make it able o change the style
 */
function page_sitestyle() {
	global $_GET, $_POST, $d_pre;
	$out = "<script type=\"text/javascript\" language=\"JavaScript\" src=\"./system/functions.js\"></script>";
	
	if(!isset($_GET["style"]) && !isset($_POST["style"]))
	{
		$object = mysql_fetch_object(db_result("SELECT * FROM ".$d_pre."vars WHERE name='style'"));
		$style = $object->value;
	}
	else
	{
		if(isset($_GET["style"]))
			$style = $_GET["style"];
		else
			$style = $_POST["style"];
	}
	
	if(isset($_GET["save"]) || isset($_POST["save"]))
	{
		if(file_exists("./styles/".$style."/mainpage.php"))
			db_result("UPDATE ".$d_pre."vars SET value= '".$style."' WHERE name='style'");
	}
	
	$out .= "<iframe id=\"previewiframe\" src=\"./index.php?style=".$style."\" class=\"stylepreview\"></iframe>
		<form action=\"admin.php\" method=\"get\">
			<input type=\"hidden\" name=\"site\" value=\"sitestyle\" />
			<label for=\"stylepreviewselect\">Style:
				<select id=\"stylepreviewselect\" name=\"style\" size=\"1\">";
	
	$verz = dir("./styles/");
	//read the available styles
	while($entry = $verz->read()) 
	{
		//check if the style really exists	
		if($entry != "." && $entry != ".." && file_exists("./styles/".$entry."/mainpage.php"))
		{
			//mark the selected style as selected in the list
			if($entry == $style)
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

/* 
 * string page_news()
 * returns the news-admin-page where you can write,change and delete news-entries
 *
 */
 
function page_news() {
	global $_GET, $_POST, $d_pre, $actual_user_showname,$actual_user_id;
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
		// delete the selected new-entrie
		if($action == "delete") {
			if(isset($_GET['sure']) || isset($_POST['sure'])) {
				if(isset($_GET['sure']))
					$sure = $_GET['sure'];
				else
					$sure = $_POST['sure'];
				
				if($sure == 1)
					db_result("DELETE FROM " . $d_pre . "news WHERE id=" . $id);
			}
			else {
				$result = db_result("SELECT * FROM " . $d_pre . "news WHERE id=" . $id);
				$row = mysql_fetch_object($result);
				$out .= "Den News Eintrag &quot;" . $row->title . "&quot; wirklich löschen?<br />
				<a href=\"admin.php?site=news&amp;action=delete&amp;id=" . $id . "&amp;sure=1\" title=\"Wirklich Löschen\">ja</a> &nbsp;&nbsp;&nbsp;&nbsp;
				<a href=\"admin.php?site=news\" title=\"Nicht Löschen\">nein</a>";
				
				return $out;
			}
		}
		elseif($action == "new") { // add a new entrie
			if($text != "" && $title != "")		
				db_result("INSERT INTO ".$d_pre."news (title, text, date, userid) VALUES ('".$title."', '".$text."', '".mktime()."', '$actual_user_id')");
		}
		elseif($action == "update") { // update the selected entrie
			if($text != "" && $title != "" && $id != 0)
				db_result("UPDATE ".$d_pre."news SET title= '".$title."', text= '".$text."' WHERE id=".$id);
		}
	}
	if($action != "edit") { // don't show the add new form if it is sure that the user wants to edit a news entrie
		$out .= "\t\t<form method=\"post\" action=\"admin.php\">
			<input type=\"hidden\" name=\"site\" value=\"news\" />
			<input type=\"hidden\" name=\"action\" value=\"new\" />
			Titel: <input type=\"text\" name=\"title\" maxlength=\"60\" value=\"\" /><br />
			<textarea cols=\"60\" rows=\"6\" name=\"text\"></textarea><br />
			Eingelogt als " . $actual_user_showname . " &nbsp;<input type=\"submit\" value=\"Senden\" /><br />
		</form>";
	}
		$out .= "\t\t<form method=\"post\" action=\"admin.php\">
			<input type=\"hidden\" name=\"site\" value=\"news\" />
			<input type=\"hidden\" name=\"action\" value=\"update\" />
			<table>\r\n";
	// write all news entries
	$result = db_result("SELECT * FROM ".$d_pre."news ORDER BY date DESC");
	while($row = mysql_fetch_object($result)) {
		
		if($id == $row->id && $action == "edit") {// show an editform for the selected entrie
			$out .= "\t\t\t\t<tr>
					<td colspan=\"2\">
						<a id=\"newsid".$row->id."\" ></a>
						<input type=\"hidden\" name=\"id\" value=\"".$row->id."\" />
						<input type=\"submit\" value=\"Speichern\" />
						&nbsp;<a href=\"admin.php?site=news&amp;action=delete&amp;id=".$row->id."\" title=\"Löschen\">Löschen</a>
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
						".getUserByID($row->userid)."
					</td>
				</tr>";
		}
		else { // show only the entrie
			$out .= "\t\t\t\t<tr>
					<td colspan=\"2\">
						<a id=\"newsid".$row->id."\" ></a>
						<a href=\"admin.php?site=news&amp;action=edit&amp;id=".$row->id."#newsid".$row->id."\" title=\"Bearbeiten\">Bearbeiten</a>
						&nbsp;<a href=\"admin.php?site=news&amp;action=delete&amp;id=".$row->id."\" title=\"Löschen\">Löschen</a>
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

function page_users() {
	global $d_pre, $_GET, $_POST, $PHP_SELF, $admin_lang, $actual_user_id, $actual_user_passwd_md5,$actual_user_online_id, $actual_user_online_id;
	
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
			else {
				if($user_admin == "on")
					$user_admin = "y";
				else
					$user_admin = "n";
				$user_password = md5($user_password);
				db_result("INSERT INTO ".$d_pre."users (showname, name, password, registerdate, admin, icq, email) VALUES ('".$user_showname."', '".$user_name."', '".$user_password."', '".mktime()."', '".$user_admin."', '".$user_icq."', '".$user_email."')");
			}
		}
		elseif($action == "save") {
			if($user_name == "" || $user_showname == "" || $user_password != $user_password_confirm)
							$action = "save-error";
			else {
				if($user_password != "")
					$user_password = ", password= '".md5($user_password)."'";
				if($user_admin == "on")
					$user_admin = "admin= 'y', ";
				else
					$user_admin = "admin= 'n', ";
				if($user_id == $actual_user_id) {
					if($user_password_confirm != "")
						$actual_user_passwd_md5 = md5($user_password_confirm);
					$actual_user_name = $user_name;
					setcookie("CMS_user_cookie",$actual_user_online_id."|".$actual_user_name."|".$actual_user_passwd_md5 , time() + 14400);
				}
				db_result("UPDATE " . $d_pre . "users SET showname= '" . $user_showname . "', name= '" . $user_name . "', email= '" . $user_email . "', " . $user_admin . "icq= '" . $user_icq . "'" . $user_password . " WHERE id=" . $user_id);
			}
			
		
		}
		elseif($action == "delete") {
			if(isset($_GET['sure']) || isset($_POST['sure'])) {
				if(isset($_GET['sure']))
					$sure = $_GET['sure'];
				else
					$sure = $_POST['sure'];
				
				if($sure == 1 && $user_id != $actual_user_id) {
					$result = db_result("SELECT * FROM " . $d_pre . "users WHERE id=" . $user_id);
				$user = mysql_fetch_object($result);
					db_result("DELETE FROM " . $d_pre . "users WHERE id=" . $user_id);
					$out .= "Der Benutzer &quot;" . $user->showname . "&quot; ist nun unwiederuflich gelöscht worden!<br />";
				}
			}
			else {
				$result = db_result("SELECT * FROM " . $d_pre . "users WHERE id=" . $user_id);
				$user = mysql_fetch_object($result);
				$out .= "Den Benutzer &quot;" . $user->showname . "&quot; unwiederruflich löschen?<br />
				<a href=\"admin.php?site=users&amp;action=delete&amp;user_id=" . $user_id . "&amp;sure=1\" title=\"Wirklich Löschen\">" . $admin_lang['yes'] . "</a> &nbsp;&nbsp;&nbsp;&nbsp;
				<a href=\"admin.php?site=users\" title=\"Nicht Löschen\">" . $admin_lang['no'] . "</a>";
				
				return $out;
			}
		}
		if($action == "edit" || $action == "new" || $action == "add-error" || $action == "save-error") {
			if($user_id != "0" || $action = "new" || $action == "add-error" || $action == "save-error") {
				$user_result = db_result("SELECT * FROM ".$d_pre."users WHERE id=".$user_id."");
				if(($user = mysql_fetch_object($user_result)) || $action == "new") {
					if($user != null && $action != "save-error") {
						$user_showname = $user->showname;
						$user_name = $user->name;
						$user_email = $user->email;
						$user_icq = $user->icq;
						$user_admin = $user->admin;
					}
					$out .= "\t\t\t<form action=\"".$PHP_SELF."\" method=\"post\">
				<input type=\"hidden\" name=\"site\" value=\"users\"/>\r\n";
					if($action == "new" || $action == "add-error")
						$out .= "\t\t\t\t<input type=\"hidden\" name=\"action\" value=\"add\"/>\r\n";
					else
						$out .= "\t\t\t\t<input type=\"hidden\" name=\"action\" value=\"save\"/>
				<input type=\"hidden\" name=\"user_id\" value=\"".$user_id."\"/>\r\n";
					$out.= "\t\t\t\t<table>
					<tr>
						<td>
							Name:
							<span class=\"info\">Der Name wird immer angezeigt, wenn der Benutzer z.B. einen News-Eintrag geschrieben hat.</span>
						</td>
						<td>
							<input type=\"text\" name=\"user_showname\" value=\"".$user_showname."\" maxlength=\"20\" />
						</td>
					</tr>
					<tr>
						<td>
							Kürzel:
							<span class=\"info\">Mit dem Kürzel kann sich der Benutzer einloggen, so muss er nicht seinen unter Umständen komplizierten Namen,der angezeigt wird, eingeben muss.</span>
						</td>
						<td>
							<input type=\"text\" name=\"user_name\" value=\"".$user_name."\" maxlength=\"20\" />
						</td>
					</tr>
					<tr>
						<td>
							E-Mail:
							<span class=\"info\">Über die E-Mail-Adresse wird der Benutzer kontaktiert. Sie ist also notwendig.</span>
						</td>
						<td>
							<input type=\"text\" name=\"user_email\" value=\"".$user_email."\" maxlength=\"20\" />
						</td>
					</tr>
					<tr>
						<td>
							ICQ:
							<span class=\"info\">Die ICQ Nummer kann angegben werden, ist aber nicht dirngend notwendig.</span>
						</td>
						<td>
							<input type=\"text\" name=\"user_icq\" value=\"".$user_icq."\" maxlength=\"20\" />
						</td>
					</tr>
					<tr>
						<td>
							Passwort:
							<span class=\"info\">.</span>
						</td>
						<td>
							<input type=\"password\" name=\"user_password\" value=\""."\" maxlength=\"20\" />
						</td>
					</tr>
					<tr>
						<td>
							Passwort wiederholen:
							<span class=\"info\">.</span>
						</td>
						<td>
							<input type=\"password\" name=\"user_password_confirm\" value=\""."\" maxlength=\"20\" />
						</td>
					</tr>
					<tr>
						<td>
							Administrator:
							<span class=\"info\">Ist ein Benutzer Administrator so hat er keinerlei Einschränkungen in seinem Handeln.<strong>Nur auswählen wenn es wirklich Notwendig ist.</strong></span>
						</td>
						<td>
							<input type=\"checkbox\" name=\"user_admin\"";
					if($user_admin == "y")
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
					<td>#id</td>
					<td>" . $admin_lang['name'] . "</td>
					<td>Kürzel</td>
					<td>email</td>
					<td>Admin</td>
					<td colspan=\"2\">Aktionen</td>
				</tr>\r\n";

		$users_result = db_result("SELECT * FROM ".$d_pre."users");
		while($user = mysql_fetch_object($users_result))
		{
			$out .= "\t\t\t\t<tr>
					<td>#".$user->id."</td>
					<td>$user->showname</td>
					<td>$user->name</td>
					<td>$user->email</td>
					<td>";
				if($user->admin == "y")
					$out .= $admin_lang['yes'];
				else
					$out .= $admin_lang['no'];
				$out .= "</td>
					<td><a href=\"".$PHP_SELF."?site=users&amp;action=edit&amp;user_id=".$user->id."\" ><img src=\"./img/edit.png\" height=\"16\" width=\"16\" alt=\"" . $admin_lang['edit'] . "\" title=\"" . $admin_lang['edit'] . "\"/></a></td>
					<td>";
					
				if($actual_user_id == $user->id)
					$out .= "&nbsp;";
				else
					$out .= "<a href=\"".$PHP_SELF."?site=users&amp;action=delete&amp;user_id=".$user->id."\" ><img src=\"./img/del.jpg\" height=\"16\" width=\"16\" alt=\"" . $admin_lang['delete'] . "\" title=\"" . $admin_lang['delete'] . "\"/></a>";
				$out .= "</td>
				</tr>\r\n";
		}
		//<tr><td colspan="7"><a href="<?php echo $PHP_SELF."?newuser=y"; " />Neuen User hinzuf&uuml;gen</a></td></tr>
		$out .= "\t\t\t</table>
			<a href=\"" . $PHP_SELF . "?site=users&amp;action=new\" title=\"Einen neuen Benutzer erstellen\">Neuen Benutzer erstellen</a>";
		//( if(!isset($pw)) { $pw = "1"; } if(!isset($pwwdh)) { $pwwdh= "1"; } if($pw!=$pwwdh) { echo "<h3>Die Wiederhohlung des Passwortes ist fehlerhaft...<br>Aus diesem Grund wurde der Eintrag nicht gespeichert.</h3>"; } 
	
	return $out;
}
?>