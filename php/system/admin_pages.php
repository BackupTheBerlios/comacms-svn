<?
function page_admincontrol()
{
	global $d_pre,$admin_lang;
	$sitedata_result = db_result("SELECT * FROM ".$d_pre."sitedata");
	$page_count = mysql_num_rows($sitedata_result);
	$users_result = db_result("SELECT * FROM ".$d_pre."users");
	$users_count = mysql_num_rows($users_result);
	$out = "<h3>AdminControl</h3><hr />
	<table>
		<tr><td>Aktiv Seit</td><td>#DATUM</td></tr>
		<tr><td>".$admin_lang['registered users']."</td><td>".$users_count."</td></tr>
		<tr><td>Erstellte Seiten</td><td>".$page_count."</td></tr>
	</table>
	
	<h3>Aktuelle Besucher</h3><hr />
	<table>
		<tr>
			<td>Name</td>
			<td>Seite</td>
			<td>Letzte Aktion</td>
			<td>Sprache</td>
			<td>IP</td>
		</tr>";

		$users_online_result = db_result("SELECT * FROM ".$d_pre."online");
		while($users_online = mysql_fetch_object($users_online_result))
		{
			$out .= "\t\t\t<tr>
			<td>*Nicht angemeldet*</td>
			<td><a href=\"index.php?site=".$users_online->page."\">".$users_online->page."</a></td>
			<td>".$users_online->lastaction."</td>
			<td>*de*".$users_online->lang."</td>
			<td>".$users_online->ip."</td>
		</tr>\r\n";
		}

	$out .= "</table>";
	
	return $out;
}

function page_sitepreview()
{
	global $admin_lang;
	$out = "<h3>".$admin_lang['sitepreview']."</h3><hr /><iframe src=\"index.php\" class=\"sitepreview\"></iframe>";
	return $out;
}

function page_menueeditor()
{
	global $_GET,$_POST, $admin_language, $d_pre, $menue_id;
	$out = "\t\t\t<h3>Menueeditor</h3><hr />	
			<ul>\r\n";
	
	if(@$_GET['menue_id'] != "" || @$_POST['menue_id'] != "")
	{
		if(isset($_GET['menue_id']))
			$menue_id = $_GET['menue_id'];
		else
			$menue_id = $_POST['menue_id'];
	}
	if($menue_id == "2")
		$out .= "\t\t\t\t<li><a href=\"admin.php?site=menueeditor&amp;menue_id=1\">Men� 1</a></li>
				<li><b>Men� 2</b></li>\r\n";
	else
	{
		$out .= "\t\t\t\t<li><b>Men� 1</b></li>
				<li><a href=\"admin.php?site=menueeditor&amp;menue_id=2\">Men� 2</a></li>\r\n";
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
				$out .= "\t\t\t<div class=\"error\">Soll der Link ".$_data->text."(".$_data->link.") wirklich gel�scht werden?<br />
			<a href=\"admin.php?site=menueeditor&amp;action=delete&amp;menue_id=".$menue_id."&amp;id=".$id."&amp;sure=1\" title=\"Wirklich L�schen?\">Ja</a> &nbsp;&nbsp;&nbsp; <a href=\"admin.php?site=menueeditor&amp;menue_id=".$menue_id."\" title=\"Nein! nicht l�schen\">Nein</a></div>";
				return $out;
			}
		}
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
		/*
		if($intern_link == "")
				$link_str = $link;
			else
				$link_str = "l:".$intern_link;
			$menue_result = db_result("SELECT * FROM ".$d_pre."menue WHERE link='".$link_str."'");
			$menue_data = mysql_fetch_object($menue_result);
			if($menue_data != null)
				$error = "F�r diese Seite existiert bereits ein Link.";
			if($link_str == "")
				$error = "Es wurde kein Link angegeben.";
			if($error == "")
			{
				if(@$newwindow == "on")
					$neww = "yes";
				else
					$neww = "no";

				$menue1_result = db_result("SELECT orderid FROM ".$d_pre."menue ORDER BY orderid DESC");
				$menue1_data = mysql_fetch_object($menue1_result);
				if($menue1_data != null)
					$ordid = $menue1_data->orderid + 1;
				else
					$ordid = 0;
				
				db_result("INSERT INTO ".$d_pre."menue (text, link, new, orderid) VALUES ('".$text."', '".$link_str."', '".$neww."', ".$ordid.")");
			}
		*/
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

	$site_result = db_result("SELECT * FROM ".$d_pre."sitedata ORDER BY name ASC");
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
						<td colspan=\"2\"><input type=\"submit\" value=\"Hinzuf�gen\" /></td>
					</tr>
				</table>
			</form>";
	
	return $out;
	
	

}

function page_sitestyle()
{
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
	
	$out .= "<iframe id=\"previewiframe\" src=\"./system/stylepreview.php?style=".$style."\" class=\"stylepreview\"></iframe>
		<form action=\"admin.php\" method=\"get\">
			<input type=\"hidden\" name=\"site\" value=\"sitestyle\" />
			<label for=\"stylepreviewselect\">Style:
				<select id=\"stylepreviewselect\" name=\"style\" size=\"1\">";
	
	$verz = dir("./styles/");

	while($entry = $verz->read()) 
	{
		if($entry != "." && $entry != ".." && file_exists("./styles/".$entry."/mainpage.php") && $entry == $style)
			$out .= "\t\t\t\t\t<option value=\"".$entry."\" selected=\"selected\">".$entry."</option>\r\n";
		elseif($entry != "." && $entry != ".." && file_exists("./styles/".$entry."/mainpage.php"))
			$out .= "\t\t\t\t\t<option value=\"".$entry."\">".$entry."</option>\r\n";
	}
	$verz->close();
	
	$out .= "</select>
			</label>

			<input type=\"submit\" value=\"Vorschau\" onclick=\"preview_style();return false;\" name=\"preview\" />
			<input type=\"submit\" value=\"Speichern\" name=\"save\" />

		</form>";
		
	return $out;
}

function page_newseditor()
{
	global $_GET, $_POST, $d_pre;
	$out = "";
	
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
		
		if($action=="delete")
		{
			if(isset($_GET['sure']) || isset($_POST['sure']))
			{
				if(isset($_GET['sure']))
					$sure = $_GET['sure'];
				else
					$sure = $_POST['sure'];
					
				if($sure == 1)
					db_result("DELETE FROM ".$d_pre."news WHERE id=".$id);
			}
			else
			{
				$result = db_result("SELECT * FROM ".$d_pre."news WHERE id=".$id);
				$row = mysql_fetch_object($result);
				$out .= "Den News Eintrag &quot;$row->title&quot; wirklich l�schen?<br>";
				$out .= "<a href=\"admin.php?site=news&amp;action=delete&amp;id=".$id."&amp;sure=1\" title=\"Wirklich L�schen\">ja</a> &nbsp;&nbsp;&nbsp;&nbsp;";
				$out .= "<a href=\"admin.php?site=news\" title=\"Nicht L�schen\">nein</a>";
				
				return $out;
			}
		}
		elseif($action=="neu")
		{
			if(isset($_GET['text']) || isset($_POST['text']))
			{
				if(isset($_GET['text']))
					$text = $_GET['text'];
				else
					$text = $_POST['text'];
				
				if(isset($_GET['title']) || isset($_POST['title']))
				{
					if(isset($_GET['title']))
						$title = $_GET['title'];
					else
						$title = $_POST['title'];
						
					$data = explode("|",$_COOKIE["CMS_user_cookie"]);
					$username = $data[0];
					$userpassword = $data[1];
					$userid = getUserIDByName($username);
					
					db_result("INSERT INTO ".$d_pre."news (title, text, date, userid) VALUES ('".$title."', '".$text."', '".mktime()."', '$userid')");
				}
			}

		}
		elseif($action=="update") 
		{
			if(isset($_GET['text']) || isset($_POST['text']))
			{
				if(isset($_GET['text']))
					$text = $_GET['text'];
				else
					$text = $_POST['text'];
				
				if(isset($_GET['title']) || isset($_POST['title']))
				{
					if(isset($_GET['title']))
						$title = $_GET['title'];
					else
						$title = $_POST['title'];
						
					if(isset($_GET['id']) || isset($_POST['id']))
					{
						if(isset($_GET['id']))
							$id = $_GET['id'];
						else
							$id = $_POST['id'];
						
						db_result("UPDATE ".$d_pre."news SET title= '".$title."', text= '".$text."' WHERE id=".$id);
					}
				}
			}
		}
	}
	
	$data = explode("|",$_COOKIE["CMS_user_cookie"]);
	$username = $data[0];
	$userpassword = $data[1];
	
	if(!isset($_GET['edit']) && !isset($_POST['edit']))
	{
		$out .= "		<form method=\"post\" action=\"admin.php\">
			<input type=\"hidden\" name=\"site\" value=\"news\" />
			<input type=\"hidden\" name=\"action\" value=\"neu\" />
			<table>
				<tr>
					<td>Titel: <input type=\"text\" name=\"title\" maxlength=\"60\" value=\"\" /></td>
				</tr>
				<tr>
					<td><textarea cols=\"60\" rows=\"6\" name=\"text\"></textarea></td>
				</tr>
				<tr>
					<td>Eingelogt als ".$username." &nbsp;<input type=\"submit\" value=\"Senden\" /></td>
				</tr>
			</table>
		</form>";
	}
	
	$out .= "		<form method=\"post\" action=\"admin.php\">
			<input type=\"hidden\" name=\"site\" value=\"news\" />
			<input type=\"hidden\" name=\"action\" value=\"update\" />
			<table>";
	if(isset($_GET['edit']) || isset($_POST['edit']))
	{
		if(isset($_GET['edit']))
			$edit = $_GET['edit'];
		else
			$edit = $_POST['edit'];
	}
	
	$result = db_result("SELECT * FROM ".$d_pre."news ORDER BY date DESC");
	while($row = mysql_fetch_object($result))
	{
		if(@$edit == $row->id)
		{
			$out .= "				<tr>
					<td colspan=\"2\"><a id=\"Newsnummer&nbsp;".$row->id."\" ></a><input type=\"hidden\" name=\"id\" value=\"".$row->id."\" /><input type=\"submit\" value=\"Speichern\" />&nbsp;<a href=\"admin.php?site=news&amp;action=delete&amp;id=".$row->id."\" title=\"L�schen\">L�schen</a></td>
					</tr>
				<tr>
					<td><input type=\"text\" name=\"title\" value=\"".$row->title."\" /></td><td>".date("d.m.Y H:i:s",$row->date)."</td>
				</tr>
				<tr>
					<td colspan=\"2\"><textarea name=\"text\" cols=\"60\" rows=\"6\">".$row->text."</textarea></td>
				</tr>
				<tr>
					<td colspan=\"2\">".getUserByID($row->userid)."</td>
				</tr>";
		}
		else
		{
			$out .= "				<tr>
					<td colspan=\"2\"><a id=\"Newsnummer&nbsp;".$row->id."\" ></a><a href=\"admin.php?site=news&amp;edit=".$row->id."#Newsnummer&nbsp;".$row->id."\" title=\"Bearbeiten\">Bearbeiten</a>&nbsp;<a href=\"admin.php?site=news&amp;action=delete&amp;id=".$row->id."\" title=\"L�schen\">L�schen</a></td>
				</tr>
				<tr>
					<td><b>".$row->title."</b></td><td>".date("d.m.Y H:i:s", $row->date)."</td>
				</tr>
				<tr>
					<td colspan=\"2\">".nl2br($row->text)."</td>
				</tr>
				<tr>
					<td colspan=\"2\">".getUserByID($row->userid)."</td>
				</tr>";
		}
	}
	$out .= "			</table>
		</form>
	</body>
</html>";

	return $out;
}
?>