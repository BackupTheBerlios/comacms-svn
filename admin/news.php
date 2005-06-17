<?
include("functions.php");
include("../config.php");
include("../functions.php");
if(isloggedin())
{
_start();
if(isset($delete))
{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head><title>News</title>
<link rel="stylesheet" type="text/css" href="style.css"/>
</head>
<body>
<?
$result = db_result("SELECT * FROM ".$d_pre."news WHERE id=".$delete);
$row = mysql_fetch_object($result);
echo "Den News Eintrag &quot;$row->title&quot; wirklich löschen?";
echo "<a href=\"news.php?deletesure=$delete\" title=\"Wirklich Löschen\">ja</a> &nbsp;&nbsp;&nbsp;&nbsp;";
echo "<a href=\"news.php\" title=\"Nicht Löschen\">nein</a>";
?>
</body>
</html>
<?
_end();
die();
}
if(isset($deletesure))
{
db_result("DELETE FROM ".$d_pre."news WHERE id=".$deletesure);
}
if(isset($text) && isset($title) && !isset($id))
{
if($text != "" && $title != "")
	db_result("INSERT INTO ".$d_pre."news (title, text, date, userid) VALUES ('".$title."', '".$text."', '".mktime()."', '0')");

}
if(isset($text) && isset($title) && isset($id))
{
	db_result("UPDATE ".$d_pre."news SET title= '".$title."', text= '".$text."' WHERE id=".$id);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head><title>News</title>
<link rel="stylesheet" type="text/css" href="style.css"/>
</head>
<? if(!isset($edit))
{ ?>
<form method="post">
<table>
<tr><td>Titel: <input type="text" name="title" maxlength="60" value="" /></td></tr>
<tr><td><textarea cols="60" rows="6" name="text"></textarea></td></tr>
<tr><td>Eingelogt als <? echo getUserByID(); ?> &nbsp;<input type="submit" value="Senden" /></td></tr>
</table>
</form>
<?
}
echo "<form method=\"post\" action=\"news.php\"><table>\n";
$result = db_result("SELECT * FROM ".$d_pre."news ORDER BY date DESC");
while($row = mysql_fetch_object($result))
{
if(@$edit == $row->id)
{
echo "<tr><td colspan=\"2\"><a id=\"newsnr$row->id\" ></a><input type=\"hidden\" name=\"id\" value=\"$row->id\" /><input type=\"submit\" value=\"Speichern\" />&nbsp;<a href=\"news.php?delete=$row->id\" title=\"Löschen\">Löschen</a></td></tr><tr><td><input type=\"text\" name=\"title\" value=\"$row->title\" /></td><td>".date("d.m.Y H:i:s",$row->date)."</td></tr>
<tr><td colspan=\"2\"><textarea name=\"text\" cols=\"60\" rows=\"6\">$row->text</textarea></td></tr>
<tr><td colspan=\"2\">".getUserByID($row->userid)."</td></tr>";

}
else
{

echo "<tr><td colspan=\"2\"><a id=\"newsnr$row->id\" ></a><a href=\"news.php?edit=$row->id#newsnr$row->id\" title=\"Bearbeiten\">Bearbeiten</a>&nbsp;<a href=\"news.php?delete=$row->id\" title=\"Löschen\">Löschen</a></td></tr><tr><td><b>$row->title</b></td><td>".date("d.m.Y H:i:s",$row->date)."</td></tr>
<tr><td colspan=\"2\">".nl2br($row->text)."</td></tr>
<tr><td colspan=\"2\">".getUserByID($row->userid)."</td></tr>";

}

}

//<tr><td>Titel</td><td>Datum</td></tr>
//<tr><td>text</td></tr>
//<tr><td>Autor</td></tr>
echo "</table></form>";
?>
</html>
<?
_end();
}
else
{


}
?>