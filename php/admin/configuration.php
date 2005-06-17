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
<title>Einstellungen</title>
<link rel="stylesheet" href="style.css" type="text/css"/>
</head>
<body>
<h1>Einstellungen</h1>
<table class="configtable">
<caption>Hier kann alles eingestellt werden, was eingesttellt werden muss/darf.</caption>
<thead class="configtable">
<tr><td>Einstellungsname</td><td>Einstellung</td></tr>
</thead>
</table>
</body>
</html>
<?
_end();
}
else
{
}

?>