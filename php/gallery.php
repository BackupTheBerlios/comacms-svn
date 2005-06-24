<?
	@include("config.php");
	if(file_exists("./install/") && !file_exists("./.svn/"))
	{
		if(defined("CMS_INSTALLED"))
			die("Please remove the install-folder id would be better.");
		else
			header("location: install/install.html");
	}

	include("functions.php");
	_start();
	//load vars
	$var_result = db_result("SELECT * FROM ".$d_pre."vars");
	while($var_data = mysql_fetch_object($var_result))
	{
		$_N_ = "internal_".$var_data->name;
		$$_N_ = $var_data->value;
	}
	//end
	
	if(@$_GET['site'])
	{
		$_site = $_GET['site'];
	}
	else
	{
		$_site = @$_POST['site'];
	}
	if($_site == "")
	{
		$_site=@$internal_default_site;
	}
	if($_site == "")
	{
		$_site = "home";
	}
	if(@$internal_style == "")
	{
		$internal_style = "clear";
	}

	$title = @$internal_gallery_pre."[galleryname]".@$inertal_gallery_past;


	$menue = " ";
	@include("./styles/".@$internal_style."/menue.php");
	$menue_result = db_result("SELECT * FROM ".$d_pre."menue ORDER BY orderid ASC");
	while($menue_data = mysql_fetch_object($menue_result))
	{
		$menue_str = $menue_link;
		$menue_str = str_replace("[text]",$menue_data->text,$menue_str);
		$link = $menue_data->link;
		if(substr($link,0,2) == "l:")
			$link = @$internal_page_root."?site=".substr($link,2);

		$menue_str = str_replace("[link]",$link,$menue_str);
		$new = $menue_data->new;
		if($new == "yes")
			$new = "target=\"_blank\" ";
		else
			$new = "";
		$menue_str = str_replace("[new]",$new,$menue_str);
		$menue .= $menue_str."\n";
	}


	//load style
	$stylefile = "./styles/".$internal_style."/mainpage.php";
	$_file = fopen($stylefile, "r");
	$page = fread($_file, filesize($stylefile));
	
	$page = str_replace("[title]", $title, $page);
	$page = str_replace("[text]", $text, $page);
	$page = str_replace("[menue]", $menue, $page);
	
	echo $page;
	_end();
?>