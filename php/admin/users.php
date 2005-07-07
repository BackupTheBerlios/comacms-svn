<?
	include("functions.php");
	include("../config.php");
	include("../functions.php");
	if(isloggedin())
	{
		_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="de">
	<head>
		<title>Seitenstyle</title>
		<link rel="stylesheet" type="text/css" href="style.css"/>
	</head>
	<body>
		<h1>Users</h1>
		<table>
			<tr><td>#id</td><td>Name</td><td>Kürzel</td><td>email</td><td>Admin</td></tr>
<?
		$users_result = db_result("SELECT * FROM ".$d_pre."users");
		while($user = mysql_fetch_object($users_result))
		{
		echo "\t\t\t<tr>
			<td>#".$user->id."</td><td>$user->showname</td><td>$user->name</td><td>$user->email</td><td>$user->admin</td>
			</tr>";
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