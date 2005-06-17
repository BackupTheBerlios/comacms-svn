<?
include("functions.php");
if(isloggedin())
{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head><title>Admin-Interface</title></head>


<frameset  cols="15%,85%">
   <frame src="menue.php" id="menue"/>
   <frame src="rechts.html" id="content" name="content"/>
</frameset>
</html>
<?
}
else
{
login();

}
?>