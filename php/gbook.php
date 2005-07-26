<?

function gbook_input()
{
	global $_site, $gb_name, $gb_mail, $gb_icq, $gb_text, $gb_homepage, $REMOTE_ADDR, $d_pre, $input;
	$error = "";
	
	if($input == "true") {
		if($gb_name == "")
			$error .= "<li class=\"error\">Es wurde kein Name angegeben.</li>\r\n";
		if($gb_text == "")
			$error .= "<li class=\"error\">Es wurde kein Nachrichtentext eingegeben.</li>\r\n";
		if($gb_mail != "") {
			if(!isEMailAddress($gb_mail))
				$error .= "<li class=\"error\">Die Email-Adresse ist ungültig.</li>\r\n";
		}
		if($gb_icq != "") {
			if(!isIcqNumber($gb_icq))
				$error .= "<li class=\"error\">Die Icq-Nummer ist ungültig.</li>\n";
			else
				$gb_icq = str_replace("-", "", $gb_icq);
		}
		if($gb_homepage == "http://")
			$gb_homepage = "";
	}

	if($error == "" && $input == "true") {
		db_result("INSERT INTO " . $d_pre . "guestbook (name, ip, date, message, mail, icq, homepage, host) VALUES ('" . $gb_name . "', '" . $REMOTE_ADDR . "', '" . mktime() . "', '" . $gb_text . "', '" . $gb_mail . "', '" . $gb_icq . "', '" . $gb_homepage . "', '" . gethostbyaddr($REMOTE_ADDR) . "')");
		$gb_name = "";
		$gb_mail = "";
		$gb_icq  = "";
		$gb_homepage = "http://";
		$gb_text = "";
	}
	if($gb_homepage == "")
		$gb_homepage ="http://";
	if($error != "")
		$error = "Folgende Fehler sind aufgetreten:
	<ul>" . $error . "</ul>";
	$text = "<div class=\"gbook\">
	<div class=\"error\">" . $error . "</div>
	<form method=\"post\" action=\"index.php?site=" . $_site . "\">
		<input type=\"hidden\" name=\"input\" value=\"true\" />
		<table class=\"gbook\">
			<tr>
				<td>
					<label>Name:</label>
				</td>
				<td>
					<input type=\"text\" name=\"gb_name\" value=\"" . $gb_name . "\" />
				</td>
			</tr>	
			<tr>
					<td>
					<label>Email:</label>
				</td>
				<td>
					<input type=\"text\" name=\"gb_mail\" value=\"" . $gb_mail . "\" />
				</td>
			</tr>
			<tr>
				<td>
					<label>ICQ:</label>
				</td>
				<td>
					<input type=\"text\" name=\"gb_icq\" value=\"" . $gb_icq . "\" />
				</td>
			</tr>
			<tr>
				<td>
					<label>Homepage:</label>
				</td>
				<td>
					<input type=\"text\" name=\"gb_homepage\" value=\"" . $gb_homepage . "\" />
				</td>
			</tr>
			<tr>
				<td>
					<label>Nachricht:</label>
				</td>
				<td>
					<textarea name=\"gb_text\">" . $gb_text . "</textarea>
				</td>
			</tr>
			<tr>
				<td>
					<input type=\"reset\" value=\"Zurücksetzen\" class=\"button\" />
				</td>
				<td>
					<input class=\"button\" type=\"submit\" value=\"Eintragen\" />
				/td>
			</tr>
		</table>
	</form>
</div>";

	return $text;
}

function gbook_pages()
{
	global $d_pre;
	$result = db_result("SELECT * FROM " . $d_pre . "guestbook"); 
	$entries = mysql_num_rows($result); 
	$text = "<div class=\"gbook_pages\">";
	$pages = floor($entries / 10) + 1;
	global $_site;
	for($i = 0;$i < $pages;$i++)
	{
		$text .= "<a href=\"index.php?site=" . $_site . "&amp;p=" . $i . "#content\">" . ($i + 1) . "</a>";
		if($pages != $i + 1)
			$text = "-";
	}
	return $text . "</div>";
}

function gbook_content()
{
	global $p,$d_pre;
	$result = db_result("SELECT * FROM " . $d_pre . "guestbook ORDER BY date DESC LIMIT " . (@$p * 10) . ",10");
	$text = "<a name=\"content\" ></a>\r\n";
	$o=1;
	while($row = mysql_fetch_object($result))
	{
		$text .= "<div class=\"gb_content\">
	<div class=\"gb_content_info\">
		<span class=\"gb_content_info_name\">";
		if($row->mail != "")
			$text .= "<a href=\"mailto:".$row->mail."\">".$row->name."</a>";
		else
			$text .= $row->name;
	
		$text .= "</span>\r\n";
 
		if($row->icq != "")
			$text .= "\t\t<span class=\"gb_content_info_icq\">
			<a href=\"http://wwp.icq.com/scripts/search.dll?to=" . $row->icq . "\" target=\"_blank\">
				<img width=\"18\"  height=\"18\" class=\"gb_content_info_icq\" src=\"http://status.icq.com/online.gif?icq=" . $row->icq . "&amp;img=5\" alt=\"ICQ-Status\"/>
			</a>
		</span>\r\n";
 
		if($row->homepage != "")
			$text .= "\t\t<span class=\"gb_content_info_homepage\">
			<a href=\"http://".$row->homepage."\">Homepage</a>
		</span>\r\n";
 
		$text .= "\t</div>\r\n";
		$time = date("H:i:s",$row->date);
		if($time == "00:00:00")
			$time ="";
		$text .= "\t<div class=\"gb_content_text\">" . replace_smilies(nl2br(htmlspecialchars($row->message))) . "
		<div class=\"gb_content_date\">" . date("d.m.Y", $row->date) . "&nbsp;" . $time . "</div>
	</div>
</div>\r\n\r\n";
		$o++;
	}
	
	return $text;
}
?>