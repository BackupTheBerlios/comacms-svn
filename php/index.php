<?php
/*****************************************************************************
 *
 *  file		: index.php
 *  created		: 2005-06-17
 *  copyright		: (C) 2005 The Comasy-Team
 *  email		: comasy@williblau.de
 *
 *****************************************************************************/

/*****************************************************************************
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 *****************************************************************************/

	@include_once("config.php");

	if(file_exists("./install/") && !file_exists("./.svn/")) {
		if(defined("CMS_INSTALLED"))
			die("Please remove the install-folder id would be better.");
		else
			header('location: install/install.html');
	}

	include('functions.php');
	include('news.php');
	include('gbook.php');
	include('counter.php');
	include('contact.php');
	_start();
	//
	// load vars
	//
	$sql = "SELECT *
		FROM " . DB_PREFIX . "vars";
	$var_result = db_result($sql);
	while($var_data = mysql_fetch_object($var_result)) {
		$_N_ = 'internal_'.$var_data->name;
		$$_N_ = $var_data->value;
	}
	set_usercookies();
	//
	// end
	//
	if(@$_GET['site']) {
		$_site = $_GET['site'];
	}
	else {
		$_site = @$_POST['site'];
	}
	if($_site == "") {
		$_site=@$internal_default_site;
 	}
	if($_site == "") {
		$_site = "home";
 	}
	$sql = "SELECT *
		FROM " . DB_PREFIX . "sitedata
		WHERE name='$_site'";
	$site_result = db_result($sql);
	if(!$site_result)
		die("bad error:  no sitedata found");
	if(!($site_data = mysql_fetch_object($site_result)))
		die("bad error:  no sitedata found");
	$title = $site_data->title;
	$text = $site_data->html;
	//
	// end
	//
	// textcompiler
	//
	counter_set("l:",$_site);
	actual_online();
	while(eregi("\[var:", $text)) {
		$pos = strpos ($text, "[var:");
		$pos2 = strpos ($text, "]",$pos);
		$str = substr($text,$pos + 5,$pos2 - $pos - 5);
		$str2 = "internal_".$str;
		$text = str_replace("[var:".$str."]", @$$str2, $text);
	}
	//
	// end
	//
	if(@$internal_style == "")
		$internal_style = "clear";

	if(isset($_GET['style']) && $actual_user_is_admin)
		$internal_style = $_GET['style'];
	//
	// load style
	//
	$stylefile = "./styles/".$internal_style."/mainpage.php";
	$_file = fopen($stylefile, "r");
	$page = fread($_file, filesize($stylefile));
	if($_site == $internal_default_site)
		$page = preg_replace("/\[notathome\](.+?)\[\/notathome\]/s", "", $page); 
	else
		$page = preg_replace("/\[notathome\](.+?)\[\/notathome\]/s", "$1", $page); 
	
	$page = str_replace("[title]", $title, $page);
	$page = str_replace("[text]", $text, $page);
	$page = str_replace("[menue]", generatemenue(@$internal_style, 1, $_site), $page);
	$page = str_replace("[menue2]", generatemenue(@$internal_style, 2, $_site), $page);
	
	
	$page = str_replace("[news]", getNews(), $page);
	$page = str_replace("[position]",position_to_root($site_data->id),$page);

	if (strpos ($page, "[gbook-")) {
		$page = str_replace("[gbook-input]", gbook_input(), $page);
		$page = str_replace("[gbook-pages]", gbook_pages(), $page);
		$page = str_replace("[gbook-content]", gbook_content(), $page);
	}
	if (strpos ($page, "[contact]"))
		$page = str_replace("[contact]", contact_formular(), $page);
	//
	// end
	//
	_end();
	echo $page;
?>