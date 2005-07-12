<?
function page_admincontrol()
{
	global $d_pre,$admin_lang;
	$sitedata_result = db_result("SELECT * FROM ".$d_pre."sitedata");
	$page_count = mysql_num_rows($sitedata_result);
	$users_result = db_result("SELECT * FROM ".$d_pre."users");
	$users_count = mysql_num_rows($users_result);
	$out = "<h3>AdminControl</h3><hr>
	<table>
		<tr><td>Aktiv Seit</td><td>#DATUM</td></tr>
		<tr><td>".$admin_lang['registered users']."</td><td>".$users_count."</td></tr>
		<tr><td>Erstellte Seiten</td><td>".$page_count."</td></tr>
	</table>
	
	<h3>Aktuelle Besucher</h3><hr>
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
	$out = "<h3>".$admin_lang['sitepreview']."</h3><hr><iframe src=\"index.php\" class=\"sitepreview\"></iframe>";
	return $out;
}
?>