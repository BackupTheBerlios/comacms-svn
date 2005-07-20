<?
function page_siteeditor() {
	
	global $admin_lang, $_GET, $_POST, $d_pre, $actual_user_lang, $PHP_SELF,
	$actual_user_id;
	
	$action = "";
	$out = "\t\t\t<h3>".$admin_lang['siteeditor']."</h3><hr />\r\n";
	
	$site_name = "";	
	$site_title = "";
	$site_lang = "";
	$site_parentid = "";
	
	if(isset($_GET['site_name']))
		$site_name = $_GET['site_name'];
	elseif(isset($_POST['site_name']))
		$site_name = $_POST['site_name'];
	
	if(isset($_POST['site_title']))
		$site_title = $_POST['site_title'];
	elseif(isset($_GET['site_title']))
		$site_title = $_GET['site_title'];
	
	if(isset($_POST['site_lang']))
		$site_lang = $_POST['site_lang'];
	elseif(isset($_GET['site_lang']))
		$site_lang = $_GET['site_lang'];
	
	if(isset($_POST['site_parentid']))
		$site_parentid = $_POST['site_parentid'];
	elseif(isset($_GET['site_parentid']))
		$site_parentid = $_GET['site_parentid'];
		
	if(isset($_GET['action']))
		$action = $_GET['action'];
	elseif(isset($_POST['action']))
		$action = $_POST['action'];
	if($action == "add_new") {
		
		$site_edit = "";
		
		if(isset($_POST['site_edit']))
			$site_edit = $_POST['site_edit'];
		elseif(isset($_GET['site_edit']))
			$site_edit = $_GET['site_edit'];
		
		if(isset($_POST['site_visible']))
			$site_visible = $_POST['site_visible'];
		elseif(isset($_GET['site_visible']))
			$site_visible = $_GET['site_visible'];
		
		if($site_name != "" && $site_title != "" && $site_lang != "") {
			
			$a_visible = array("public","private","hidden");
			$visible = $site_visible;
			if(!in_array($site_visible, $a_visible))
				$visible = $a_visible;
			$site_name = strtolower($site_name);
			$site_name = str_replace(" ", "_", $site_name);
			$site_result = db_result("SELECT name FROM ".$d_pre."sitedata WHERE name='".$site_name."'");
			if(!$site_data = mysql_fetch_object($site_result))
				db_result("INSERT INTO ".$d_pre."sitedata (name, type, title, text, lang, html, parent_id, creator, date, visible) VALUES ('".$site_name."', 'text', '".$site_title."', '', '".$site_lang."', '', '".$site_parentid."', '".$actual_user_id."', '" . mktime() . "','" . $visible . "')");
			if($site_edit == "on")
				header("Location: ".$PHP_SELF."?site=siteeditor&action=edit&site_name=".$site_name);
			else
				header("Location: ".$PHP_SELF."?site=siteeditor");
		}
	}
	elseif($action == "update") {
		$site_text = "";
		if(isset($_GET['site_text']))
			$site_text = $_GET['site_text'];
		elseif(isset($_POST['site_text']))
			$site_text = $_POST['site_text'];
		if($site_name != "" && $site_title != "" && $site_text != "") {
			$html = convertToPreHtml($site_text);
			$old_result = db_result("SELECT * FROM " . $d_pre . "sitedata WHERE name='".$site_name."'");
			if($old = mysql_fetch_object($old_result)) {
				if(($old->text != $site_text) || ($old->title != $site_title)) {
					if($old->text != "")
						db_result("INSERT INTO " . $d_pre . "sitedata_history (name, title, text, lang, type, creator, date) VALUES ('".$site_name."', '".$old->title."', '".$old->text."', '".$old->lang."', 'text',".$old->creator.", '" . $old->date . "')");
					db_result("UPDATE ".$d_pre."sitedata SET title= '".$site_title."', text='".$site_text."', html='".$html."', creator='".$actual_user_id."', date='" . mktime() . "' WHERE name='".$site_name."'");
					$out = "Der Eintrag sollte gespeichert sein";
				}
			}
		}
	}
	elseif($action == "new") {
		
		$out .= "\t\t\t<form method=\"post\" action=\"".$PHP_SELF."\">
				<input type=\"hidden\" name=\"site\" value=\"siteeditor\" />
				<input type=\"hidden\" name=\"action\" value=\"add_new\" />
				<table>
					<tr>
						<td>
							Name/Kürzel:
							<span class=\"info\">Mit diesem Kürzel wird auf die Seite zugegriffen und dient es zur eindeutigen Identifizierung der Seite.</span>
						</td>
						<td>
							<input type=\"text\" name=\"site_name\" value=\"".$site_name."\" maxlength=\"20\" />
						</td>
					</tr>
					<tr>
						<td>
							Titel:
							<span class=\"info\">Der Titel wird später in der Titelleiste des Browsers angezeigt.</span>
						</td>
						<td>
							<input type=\"text\" name=\"site_title\" maxlength=\"100\" />
						</td>
					</tr>
					<tr>
						<td>
							" . $admin_lang['language'] . ":
							<span class=\"info\">Der Text soll in der gewählten Sprache geschrieben werden.</span>
						</td>
						<td>
							<select name=\"site_lang\">
								<option value=\"de\">Deutsch</option>
								<option value=\"en\">Englisch</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>
							Zugang:
							<span class=\"info\">Wer soll sich die Seite später anschauen können?<br />
							Jeder (öffentlich), nur ausgewählte Benutzer (privat) oder soll die Seite nur erstellt werden um sie später zu veröffentlichen (versteckt)?</span>
						</td>
						<td>
							<select name=\"site_visible\">
								<option value=\"public\">Öffentlich</option>
								<option value=\"private\">Privat</option>
								<option value=\"hidden\">Versteckt</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>
							Unterseite von:
							<span class=\"info\">TODO</span>	
						</td>
						<td>
							<select name=\"site_parentid\">
								<option value=\"0\">Keiner</option>\r\n";
								
		$sites = db_result("SELECT name, title,id FROM " . $d_pre . "sitedata WHERE visible!='deleted' ORDER BY name ASC");
		while($siteinfo = mysql_fetch_object($sites))
			$out .= "\t\t\t\t\t\t<option value=\"".$siteinfo->id."\">".$siteinfo->title."(".$siteinfo->name.")</option>\r\n";
		$out .= "\t\t\t\t\t\t\t</select>
						</td>
					</tr>
					<tr>
						<td>
							Bearbeiten?
							<span class=\"info\">Soll die Seite nach dem Erstellen bearbeitet werden oder soll wieder auf die Übersichtseite zurückgekehrt werden?</span>
						</td>
						<td><input type=\"checkbox\" name=\"site_edit\" checked=\"true\"/></td>
					</tr>
					<tr>
						<td colspan=\"2\">
							<input type=\"reset\" value=\"Zurücksetzen\" />&nbsp;
							<input type=\"submit\" value=\"Erstellen\" />
						</td>
					</tr>
				</table>
			</form>";
	
	}
	elseif($action == "delete") {
		$sure = "";
		if(isset($_GET['sure']))
			$sure = $_GET['sure'];
		elseif(isset($_POST['sure']))
			$sure = $_POST['sure'];
		$exists_result = db_result("SELECT * FROM " . $d_pre . "sitedata WHERE name='" . $site_name . "'");
		$exists = null;
		if(!$exists = mysql_fetch_object($exists_result)) {
			$out .= "\t\t\tDer Eintag existiert garnicht, das löschen kann man sich also sparen<br />
			<a href=\"" . $PHP_SELF . "?site=siteeditor\">".$admin_lang['ok']."</a>";
			return $out;
		}
		if($sure == 1) {
			db_result("INSERT INTO " . $d_pre . "sitedata_history (name, title, text, lang, type, creator, date) VALUES ('".$site_name."', '".$exists->title."', '".$exists->text."', '".$exists->lang."', 'text',".$exists->creator.", '" . $exists->date . "')");
			db_result("UPDATE ".$d_pre."sitedata SET  visible='deleted', text='', html='', creator='".$actual_user_id."', date='" . mktime() . "' WHERE name='".$site_name."'");
			//TODO Backup old data and set as deleted
		}
		else {
			$out .= "\t\t\tMöchten sie die Seite &quot;" . $exists->title . " (" . $exists->name . ")&quot; wirklich löschen?<br />
			<a href=\"" . $PHP_SELF . "?site=siteeditor&amp;action=delete&amp;sure=1&amp;site_name=" . $site_name . "\">".$admin_lang['yes']."</a> <a href=\"" . $PHP_SELF . "?site=siteeditor\">".$admin_lang['no']."</a>";
		}
		
	}
	elseif($action == "tree") {
		$site_show_hidden = "";
		$site_show_deleted = "";
		$show_deleted = false;
		$show_hidden = false;
		
		if(isset($_GET['site_show_hidden']))
			$site_show_hidden = $_GET['site_show_hidden'];
		elseif(isset($_POST['site_show_hidden']))
			$site_show_hidden = $_POST['site_show_hidden'];
			
		if(isset($_GET['site_show_deleted']))
			$site_show_deleted = $_GET['site_show_deleted'];
		elseif(isset($_POST['site_show_deleted']))
			$site_show_deleted = $_POST['site_show_deleted'];
		if($site_show_hidden == "on")
			$show_hidden = true;
		if($site_show_deleted == "on")
			$show_deleted = true;
			
		if($site_lang == "")
			$site_lang = $actual_user_lang;
		$out .= "\t\t\t<form action=\"".$PHP_SELF."\" method=\"get\">
				<input type=\"hidden\" name=\"site\" value=\"siteeditor\" />
				<input type=\"hidden\" name=\"action\" value=\"tree\" />
				<select name=\"site_lang\">
					<option value=\"de\"";if($site_lang == "de") $out .= " selected=\"selected\""; $out .=">".$admin_lang['de']."</option>
					<option value=\"en\"";if($site_lang == "en") $out .= " selected=\"selected\""; $out .=">".$admin_lang['en']."</option>
				</select><br />
				<input type=\"checkbox\" name=\"site_show_hidden\"";if($show_hidden) $out .= " checked=\"true\""; $out .= "/>" . $admin_lang['show hidden'] ."<br />
				<input type=\"checkbox\" name=\"site_show_deleted\"";if($show_deleted) $out .= " checked=\"true\""; $out .= "/>" . $admin_lang['show deleted'] ."<br />
				<input type=\"submit\" value=\"" . $admin_lang['show'] . "\" />
			</form>";
		$out .= generatesitestree(0, "\t\t\t", $site_lang, $show_deleted, $show_hidden);
	}
	elseif($action == "edit") {
		$site_result = db_result("SELECT * FROM ".$d_pre."sitedata WHERE name='".$site_name."'");
		if($site_data = mysql_fetch_object($site_result)){
			$out .= "\t\t\t<form action=\"".$PHP_SELF."\" method=\"post\">
				<input type=\"hidden\" name=\"site\" value=\"siteeditor\" />
				<input type=\"hidden\" name=\"action\" value=\"update\" />
				<input type=\"hidden\" name=\"site_name\" value=\"".$site_data->name."\" />
				<input type=\"text\" name=\"site_title\" value=\"".$site_data->title."\" /><br />
				<script type=\"text/javascript\" language=\"JavaScript\" src=\"system/functions.js\"></script>
				<script type=\"text/javascript\" language=\"javascript\">
					writeButton(\"img/button_fett.png\",\"Formatiert Text Fett\",\"**\",\"**\",\"Fetter Text\",\"f\");
					writeButton(\"img/button_kursiv.png\",\"Formatiert Text kursiv\",\"//\",\"//\",\"Kursiver Text\",\"k\");
					writeButton(\"img/button_unterstrichen.png\",\"Unterstreicht den Text\",\"__\",\"__\",\"Unterstrichener Text\",\"u\");
					writeButton(\"img/button_ueberschrift.png\",\"Markiert den Text als Überschrift\",\"=== \",\" ===\",\"Überschrift\",\"h\");
				</script><br />
				<textarea id=\"editor\" class=\"edit\" name=\"site_text\">".$site_data->text."</textarea>
				<input type=\"reset\" value=\"Zurücksetzten\" />
				<input type=\"submit\" value=\"Speichern\" />
			</form>";
		}
		else
			header("Location: ".$PHP_SELF."?site=siteeditor&action=new&site_name=".$site_name);
	}
	elseif($action == "info") {
		if($site_name == "")
			header("Location: " . $PHP_SELF . "?site=siteeditor");
		$actual_result = db_result("SELECT * FROM " . $d_pre . "sitedata WHERE name='" . $site_name . "'");
		$olds_result = db_result("SELECT * FROM " . $d_pre . "sitedata_history WHERE name='" . $site_name . "' ORDER BY id DESC");
		$actual = mysql_fetch_object($actual_result);
		$out .= "\t\t\tName: " . $actual->name . "<br />
			Titel: " . $actual->title . "<br />
			<fieldset><legend>Text</legend>".$actual->html."</fieldset>
			Letzte Veränderung von: ".getUserById($actual->creator)."<br />
			insgesamt " . mysql_num_rows($olds_result) . " Veränderungen<br />";
	}
	else { // home site etc.
		$out .= "<a href=\"".$PHP_SELF."?site=siteeditor&amp;action=new\">Neue Seite</a><br />\r\n";
		$out .= "<a href=\"".$PHP_SELF."?site=siteeditor&amp;action=tree\">Übersicht</a><br />\r\n";
		$out .= "<form action=\"" . $PHP_SELF . "\" method=\"get\">
		<input type=\"hidden\" name=\"site\" value=\"siteeditor\" />
		<input type=\"hidden\" name=\"action\" value=\"edit\" />
		<select name=\"site_name\">";
		$sites = db_result("SELECT name, title,id,visible FROM " . $d_pre . "sitedata WHERE visible!='deleted' ORDER BY name ASC");
		while($siteinfo = mysql_fetch_object($sites))
			$out .= "\t\t\t\t\t\t<option value=\"".$siteinfo->name."\">".$siteinfo->title."(".$siteinfo->name.")</option>\r\n";
		$out .= "\t\t\t\t\t\t\t</select>
		<input type=\"submit\" value=\"Öffnen\" /> 
		</form>";
	}
	return $out;
}

function page_logout() {
	global $actual_user_online_id;
	setcookie("CMS_user_cookie",$actual_user_online_id."||", time() + 14400);
	header("Location: index.php");
}
?>