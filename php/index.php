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
	include("news.php");
	include("gbook.php");
	include("counter.php");
	include("contact.php");
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

	$site_result = db_result("SELECT * FROM ".$d_pre."sitedata WHERE name='".$_site."'");
	if(!$site_result)
    	die("bad error:  no sitedata found");
	$site_data = mysql_fetch_object($site_result);
	
	$title = $site_data->title;
	$text = $site_data->html;
	//end
	//textcompile

	counter_set();
	actual_online();
	while(eregi("\[var:", $text))
	{
		$pos = strpos ($text, "[var:");
		$pos2 = strpos ($text, "]",$pos);
		$str = substr($text,$pos + 5,$pos2 - $pos - 5);
		$str2 = "internal_".$str;
		$text = str_replace("[var:".$str."]", @$$str2, $text);
	
	}

	//end
	if(@$internal_style == "")
	{
		$internal_style = "clear";
 	}

	$menue = generatemenue(@$internal_style,$_site);

	//load style
	$stylefile = "./styles/".$internal_style."/mainpage.php";
	$_file = fopen($stylefile, "r");
	$page = fread($_file, filesize($stylefile));
	
	$page = str_replace("[title]", $title, $page);
	$page = str_replace("[text]", $text, $page);
	$page = str_replace("[menue]", $menue, $page);
	$page = str_replace("[news]", getNews(), $page);

	if (strpos ($page, "[gbook-")) { 
    $page = str_replace("[gbook-input]", gbook_input(), $page);
	$page = str_replace("[gbook-pages]", gbook_pages(), $page);
	$page = str_replace("[gbook-content]", gbook_content(), $page);
	}
	if (strpos ($page, "[contact]")) { 
	$page = str_replace("[contact]", contact_formular(), $page);
	}					  
	//end
	_end();
	echo $page;
?>