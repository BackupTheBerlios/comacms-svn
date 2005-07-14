<?
include("functions.php");
include("../config.php");
include("../functions.php");
	if(isloggedin()) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title>News</title>
		<link rel="stylesheet" type="text/css" href="style.css"/>
	</head>
	<body>

<?
		_start();
		if(isset($delete)) {
			$result = db_result("SELECT * FROM ".$d_pre."news WHERE id=".$delete);
			$row = mysql_fetch_object($result);
			echo "Den News Eintrag &quot;$row->title&quot; wirklich löschen?";
			echo "<a href=\"news.php?deletesure=$delete\" title=\"Wirklich Löschen\">ja</a> &nbsp;&nbsp;&nbsp;&nbsp;";
			echo "<a href=\"news.php\" title=\"Nicht Löschen\">nein</a>";
			_end();
			die();
		}
		
		if(isset($deletesure))
		{
		db_result("DELETE FROM ".$d_pre."news WHERE id=".$deletesure);
		}
		
		if(isset($text) && isset($title) && !isset($id))
		{
			$data = explode("|",$_COOKIE["CMS_user_cookie"]);
			$username = $data[0];
			$userpassword = $data[1];
			$userid = getUserIDByName($username);
			
			if($text != "" && $title != "")
				db_result("INSERT INTO ".$d_pre."news (title, text, date, userid) VALUES ('".$title."', '".$text."', '".mktime()."', '$userid')");
		}
		
		if(isset($text) && isset($title) && isset($id))
		{
			db_result("UPDATE ".$d_pre."news SET title= '".$title."', text= '".$text."' WHERE id=".$id);
		}
		
		if(!isset($edit))
		{ 
?>
		<form method="post" action="<?php echo $PHP_SELF; ?>">
			<table>
				<tr>
					<td>Titel: <input type="text" name="title" maxlength="60" value="" /></td>
				</tr>
				<tr>
					<td><textarea cols="60" rows="6" name="text"></textarea></td>
				</tr>
				<tr>
<?
					$data = explode("|",$_COOKIE["CMS_user_cookie"]);
					$username = $data[0];
					$userpassword = $data[1];
?>
					<td>Eingelogt als <? echo $username; ?> &nbsp;<input type="submit" value="Senden" /></td>
				</tr>
			</table>
		</form>
<?
}
?>
		<form method="post" action="<?php echo $PHP_SELF; ?>">
			<table>
<?
			$result = db_result("SELECT * FROM ".$d_pre."news ORDER BY date DESC");
			while($row = mysql_fetch_object($result))
			{
				if(@$edit == $row->id)
				{
?>
				<tr>
					<td colspan="2"><a id="Newsnummer&nbsp;<?php echo $row->id; ?>" ></a><input type="hidden" name="id" value="<?php echo $row->id; ?>" /><input type="submit" value="Speichern" />&nbsp;<a href="news.php?delete=<?php echo $row->id; ?>" title="Löschen">Löschen</a></td>
					</tr>
				<tr>
					<td><input type="text" name="title" value="<?php echo $row->title; ?>" /></td><td><?php echo date("d.m.Y H:i:s",$row->date); ?></td>
				</tr>
				<tr>
					<td colspan="2"><textarea name="text" cols="60" rows="6"><?php echo $row->text; ?></textarea></td>
				</tr>
				<tr>
					<td colspan="2"><?php echo getUserByID($row->userid); ?></td>
				</tr>
<?
				}
				else
				{
?>
				<tr>
					<td colspan="2"><a id="Newsnummer&nbsp;<?php echo $row->id; ?>" ></a><a href="news.php?edit=<?php echo $row->id; ?>#newsnr<?php echo $row->id; ?>" title="Bearbeiten">Bearbeiten</a>&nbsp;<a href="news.php?delete=<?php echo $row->id; ?>" title="Löschen">Löschen</a></td>
				</tr>
				<tr>
					<td><b><?php echo $row->title; ?></b></td><td><?php echo date("d.m.Y H:i:s",$row->date); ?></td>
				</tr>
				<tr>
					<td colspan="2"><?php echo nl2br($row->text); ?></td>
				</tr>
				<tr>
					<td colspan="2"><?php echo getUserByID($row->userid); ?></td>
				</tr>
<?
				}
			}
?>
			</table>
		</form>
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