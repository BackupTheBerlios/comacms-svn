<?
	include("functions.php");
	include("../config.php");
	include("../functions.php");
	if(isloggedin())
	{
		_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title>AdminControl</title>
		<link rel="stylesheet" type="text/css" href="style.css"/>
	</head>
	<body>
	<h1>AdminControl</h1>
	<table>
		<tr><td>Aktiv Seit</td><td>#DATUM</td></tr>
		<tr><td>Angemeldete Benutzer</td><td>#ANZ.USERS</td></tr>
		<tr><td>Eingetragene Seiten</td><td>#ANZ.SITEDATA</td></tr>
	</table>
	
	<h3>Aktuelle Besucher</h3>
	<table>
		<tr>
			<td>Name</td>
			<td>Seite</td>
			<td>Letzte Aktion</td>
			<td>Sprache</td>
			<td>IP</td>
		</tr>
<?
		$users_online_result = db_result("SELECT * FROM ".$d_pre."online");
		while($users_online = mysql_fetch_object($users_online_result))
		{
			echo"\t\t\t<tr>
			<td>*Nicht angemeldet*</td>
			<td><a href=\"../index.php?site=".$users_online->page."\">".$users_online->page."</a></td>
			<td>".$users_online->lastaction."</td>
			<td>*de*".$users_online->lang."</td>
			<td>".$users_online->ip."</td>
		</tr>\r\n";
		}
?>
	</table>
	</body>
</html>

<?
		_end();
	}
	else
	{
		login();
	}
?>