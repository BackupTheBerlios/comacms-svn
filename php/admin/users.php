<?
	include("functions.php");
	include("../config.php");
	include("../functions.php");

	if(isloggedin())
	{
		_start();
	if(isset($deletesure))
	{
		db_result("DELETE FROM ".$d_pre."users WHERE id='".$id."'");
	}
	if(isset($update))
	{
		if(isset($admin)){ $admin="admin= 'y', "; } else { $admin="admin= 'n', "; }
		if($pw!="evtl. neues Password") { $pw=", password= '".md5($pw)."'"; } else { $pw = ""; }
		db_result("UPDATE ".$d_pre."users SET showname= '".$showname."', name= '".$name."', email= '".$email."', ".$admin."icq= '".$icq."'".$pw." WHERE id=".$id);
	}
	if(isset($speichern))
	{
		if(isset($admin)){ $admin="y"; } else { $admin="n"; }
		$pw = md5($pw);
		db_result("INSERT INTO ".$d_pre."users (name, showname, password, registerdate, admin, icq, email) VALUES ('".$showname."', '".$name."', '".$pw."', '".mktime()."', '".$admin."', '".$icq."', '".$email."')");
	}
	if(isset($delete))
	{
	echo "Wollen sie den User ".getUserByID($id)." wirklich l&ouml;schen?<br>";
	echo "<a href=\"".$PHP_SELF."\" />Nein</a>&nbsp;&nbsp;&nbsp;<a href=\"".$PHP_SELF."?deletesure=y&id=".$id."\" />Ja</a>";
	}
	else
	{
	if(isset($newuser))
	{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="de">
	<head>
		<title>neuen User hinzuf&uuml;gen</title>
		<link rel="stylesheet" type="text/css" href="style.css" />
	</head>
	<body>
		<h1>neuen User hinzuf&uuml;gen</h1>
		<table>
			<form action="<?php echo $PHP_SELF."?speichern=y"?>" method="post">
				<tr><td>Name:</td><td><input type="text" name="showname" /></td></tr>
				<tr><td>K&uuml;rzel:</td><td><input type="text" name="name" /></td></tr>
				<tr><td>Pasword:</td><td><input type="text" name="pw" /></td></tr>
				<tr><td>E-Mail Adresse:</td><td><input type="text" name="email" /></td></tr>
				<tr><td>ICQ Nummer:</td><td><input type="text" name="icq" /></td></tr>
				<tr><td>Admin:</td><td><input type="checkbox" name="admin" /></td></tr>
				<tr><td colspan="2">&nbsp;</td></tr>
				<tr><td><input type="reset" /></td><td><input type="submit" value="Speichern" /></td></tr>
			</form>
		</table>
	</body>
</html>
<?
	}
	else
	{
	if(isset($edit))
	{
		$user_result = db_result("SELECT * FROM ".$d_pre."users WHERE id=".$id);
		$user = mysql_fetch_object($user_result);
		?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="de">
	<head>
		<title>Bearbeitung von User <?php echo $user->showname; ?></title>
		<link rel="stylesheet" type="text/css" href="style.css" />
	</head>
	<body>
		<h1>User <?php echo $user->showname; ?></h1>
		<table>
			<form action="<?php echo $PHP_SELF."?update=y&id=$user->id"?>" method="post">
				<tr><td>Userid </td><td><?php echo $user->id; ?></td></tr>
				<tr><td>Name:</td><td><input type="text" name="showname" value="<?php echo $user->showname; ?>" /></td></tr>
				<tr><td>K&uuml;rzel:</td><td><input type="text" name="name" value="<?php echo $user->name; ?>" /></td></tr>
				<tr><td>Pasword:</td><td><input type="text" name="pw" value="evtl. neues Password" /></td></tr>
				<tr><td>E-Mail Adresse:</td><td><input type="text" name="email" value="<?php echo $user->email; ?>" /></td></tr>
				<tr><td>ICQ Nummer:</td><td><input type="text" name="icq" value="<?php echo $user->icq; ?>" /></td></tr>
				<tr><td>Admin:</td><td><input type="checkbox" <?php if($user->admin=="y") { echo "checked"; } ?> name="admin" /></td></tr>
				<tr><td colspan="2">&nbsp;</td></tr>
				<tr><td><input type="reset" /></td><td><input type="submit" value="Speichern" /></td></tr>
				<tr><td colspan="2">Der Benutzer wurde am <?php echo date("d.m.Y",$user->registerdate); ?> um <?php echo  date("H:i:s",$user->registerdate); ?> registriert.</td></tr>
			</form>
		</table>
	</body>
</html>
		<?
	}
	else
	{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="de">
	<head>
		<title>Userliste</title>
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
			<td>#".$user->id."</td><td>$user->showname</td><td>$user->name</td><td>$user->email</td><td>$user->admin</td><td><a href=\"".$PHP_SELF."?edit=y&id=".$user->id."\" />Bearbeiten</a></td><td><a href=\"".$PHP_SELF."?delete=y&id=".$user->id."\" />L&ouml;schen</a></td>
			</tr>";
		}
?>
		<tr><td colspan="7"><a href="<?php echo $PHP_SELF."?newuser=y"; ?>" />Neuen User hinzuf&uuml;gen</a></td></tr>
		</table>
	</body>
</html>
<?
	}
	}
	}
	_end();
	}
	else
	{
		login();
	}
?>