<?
	include("./config.php");
	include("./functions.php");
	include("./system/functions.php");
	_start();
	set_usercookies();
	
	if($actual_user_is_logged_in && $actual_user_is_admin)
	{
		$text = "";
		$title = "";
		include("./lang/".$actual_user_lang."/admin_lang.php");
		include("./system/admin_pages.php");
		$menue_array = array();
		$menue_array[] = array($admin_lang['admincontrol'], "admin.php?site=admincontrol");
		$menue_array[] = array($admin_lang['sitepreview'], "admin.php?site=sitepreview");
		$menue_array[] = array($admin_lang['preferences'], "admin.php?site=preferences");
		$menue_array[] = array($admin_lang['menueeditor'], "admin.php?site=menueeditor");
		$menue_array[] = array($admin_lang['siteeditor'], "admin.php?site=siteeditor");
		$menue_array[] = array($admin_lang['news'], "admin.php?site=news");
		$menue_array[] = array($admin_lang['sitestyle'], "admin.php?site=sitestyle");
		$menue_array[] = array($admin_lang['users'], "admin.php?site=users");
		$menue_array[] = array($admin_lang['gallery'], "admin.php?site=gallery");
		$menue_array[] = array($admin_lang['logout'], "admin.php?site=logout");
		//load vars
		$var_result = db_result("SELECT * FROM ".$d_pre."vars");
		while($var_data = mysql_fetch_object($var_result))
		{
			$_N_ = "internal_".$var_data->name;
			$$_N_ = $var_data->value;
		}
		//end
		//insert the "functions" here
		if(!isset($site))
			$site = "admincontrol";
		if($site == "admincontrol")
		{
			$title = $admin_lang['admincontrol'];
			$text = page_admincontrol();
		}
		elseif($site == "sitepreview")
		{
			$title = $admin_lang['sitepreview'];
			$text = page_sitepreview();
		}
		elseif($site == "menueeditor")
		{
			$title = $admin_lang['menueeditor'];
			$text = page_menueeditor();
		}
		elseif($site == "sitestyle")
		{
			$title = $admin_lang['sitestyle'];
			$text = page_sitestyle();
		}
		elseif($site == "news")
		{
			$title = $admin_lang['news'];
			$text = page_news();
		}
		//end of the "functions"
		if(@$internal_style == "")
			$internal_style = "clear";
		include("./styles/".$internal_style."/menue.php");
		$menue = "";
		foreach($menue_array as $part)
		{
			$menue_str = $menue_link;
			$menue_str = str_replace("[text]",$part[0],$menue_str);
			$menue_str = str_replace("[link]",$part[1],$menue_str);
			$menue_str = str_replace("[new]","",$menue_str);
			$menue .= $menue_str."\r\n";
		}
		
		$stylefile = "./styles/".$internal_style."/mainpage.php";
		$_file = fopen($stylefile, "r");
		$page = fread($_file, filesize($stylefile));
		$page = str_replace("[menue]", $menue, $page);
		$page = str_replace("[title]", $title, $page);
		$page = str_replace("[menue2]","",$page);
		$page = str_replace("[text]", $text, $page);
		echo $page;
	}
	else
	{
		login("admin");
	}
	_end();

?>