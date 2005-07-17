<?
function page_siteeditor() {
	
	global $admin_lang, $_GET, $_POST, $d_pre, $actual_user_lang, $PHP_SELF;
	
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
		
		if($site_name != "" && $site_title != "" && $site_lang != "") {
			
			$site_result = db_result("SELECT name FROM ".$d_pre."sitedata WHERE name='".$site_name."'");
			if(!$site_data = mysql_fetch_object($site_result))
				db_result("INSERT INTO ".$d_pre."sitedata (name, type, title, text, lang, html) VALUES ('".$site_name."', 'text', '".$site_title."', '', '".$site_lang."', '')");
			if($site_edit == "on")
				header("Location: ".$PHP_SELF."?site=siteeditor&action=edit&site_name=".$site_name);
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
			db_result("UPDATE ".$d_pre."sitedata SET title= '".$site_title."', text='".$site_text."', html='".$html."' WHERE name='".$site_name."'");
			
			$out = "Der eintrag sollte gespeichert sein";
		}
	}
	elseif($action == "new") {
		
		$out .= "\t\t\t<form method=\"post\" action=\"".$PHP_SELF."\">
				<input type=\"hidden\" name=\"site\" value=\"siteeditor\" />
				<input type=\"hidden\" name=\"action\" value=\"add_new\" />
				<table>
					<tr>
						<td>
							Name/Kürzel:<br />
							<span class=\"info\">Mit diesem Kürzel wird auf die Seite zugegriffen und dient zur eindeutigen Identifizierung der Seite.</span>
						</td>
						<td>
							<input type=\"text\" name=\"site_name\" value=\"".$site_name."\" maxlength=\"20\" />
						</td>
					</tr>
					<tr>
						<td>Titel:</td>
						<td>
							<input type=\"text\" name=\"site_title\" maxlength=\"100\" />
						</td>
					</tr>
					<tr>
						<td>Sprache:</td>
						<td>
							<select name=\"site_lang\">
								<option value=\"de\">Deutsch</option>
								<option value=\"en\">Englisch</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>Unterseite von:</td>
						<td>
							<select name=\"site_parentid\">
								<option value=\"0\">Keiner</option>\r\n";
								
		$sites = db_result("SELECT name, title,id FROM " . $d_pre . "sitedata ORDER BY name ASC");
		while($siteinfo = mysql_fetch_object($sites))
			$out .= "\t\t\t\t\t\t<option value=\"".$siteinfo->id."\">".$siteinfo->title."(".$siteinfo->name.")</option>\r\n";
		$out .= "\t\t\t\t\t\t\t</select>
						</td>
					</tr>
					<tr>
						<td>Nach dem Erstellen diese Seite bearbeiten?</td>
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
	}
	elseif($action == "tree") {
	}
	elseif($action == "edit") {
		$site_result = db_result("SELECT * FROM ".$d_pre."sitedata WHERE name='".$site_name."'");
		if($site_data = mysql_fetch_object($site_result)){
			$out .= "<form>
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
	else { // home site etc.
		$out .= "<a href=\"".$PHP_SELF."?site=siteeditor&amp;action=new\">Neue Seite</a>\r\n";
		$out .= "<form>
		<input type=\"hidden\" name=\"site\" value=\"siteeditor\" />
		<input type=\"hidden\" name=\"action\" value=\"edit\" />
		<select name=\"site_name\">";
		$sites = db_result("SELECT name, title,id FROM " . $d_pre . "sitedata ORDER BY name ASC");
		while($siteinfo = mysql_fetch_object($sites))
			$out .= "\t\t\t\t\t\t<option value=\"".$siteinfo->name."\">".$siteinfo->title."(".$siteinfo->name.")</option>\r\n";
		$out .= "\t\t\t\t\t\t\t</select>
		<input type=\"submit\" value=\"Öffnen\" /> 
		</form>";
	}
	
	return $out;
}

?>