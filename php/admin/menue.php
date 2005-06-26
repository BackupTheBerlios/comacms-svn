<?
include("functions.php");
if(isloggedin())
{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head><title>Menue</title></head>
<body>
<ul>
<li>AdminControl</li>
<li><a href="../index.php" target="content">Seitenvorschau</a></li>
<li><a href="configuration.php" target="content">Einstellungen</a></li>
<li><a href="menueeditor.php" target="content">Men&uuml;</a></li>
<li><a href="siteeditor.php" target="content">SeitenEditor</a></li>
<li><a href="news.php" target="content">News</a></li>
<li><a href="style.php" target="content">Seitenstyle</a></li>
<li>Gallerien</li>
<li>Logout</li>
</ul>
</body>
</html>

<?
}
else
{
login();

}
?>