<?
	include("./config.php");
	include("./functions.php");
	include("./system/functions.php");
	_start();
	set_usercookies();
	if($actual_user_is_logged_in && $actual_user_is_admin)
	{
		echo "hi Admin";
	}
	else
	{
		login("admin");
	}
	_end();

?>