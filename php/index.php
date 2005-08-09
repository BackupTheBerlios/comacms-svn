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
	
	define("COMASY_RUN", true);
	
	include("common.php");
	//@include_once("config.php");
	
	//include('news.php');
	//include('gbook.php');
	//include('counter.php');
	//include('contact.php');
	//_start();
	

 	
 	
	$sql = "SELECT *
		FROM " . DB_PREFIX . "pages_content
		WHERE page_name='$extern_page'";
	$page_result = db_result($sql);
	if(!$page_result)
		die("bad error:  no pagedata found");
	if(!($page_data = mysql_fetch_object($page_result)))
		die("bad error:  no sitedata found");
	$title = $page_data->page_title;
	$text = $page_data->page_html;
	//
	// end
	//
	// textcompiler
	//
	/*
	while(eregi("\[var:", $text)) {
		$pos = strpos ($text, "[var:");
		$pos2 = strpos ($text, "]",$pos);
		$str = substr($text,$pos + 5,$pos2 - $pos - 5);
		$str2 = "internal_".$str;
		$text = str_replace("[var:".$str."]", @$$str2, $text);
	}*/
	//
	// end
	//
	

	
	//
	// load style
	//
	$stylefile = "./styles/".$internal_style."/mainpage.php";
	$_file = fopen($stylefile, 'r');
	$page = fread($_file, filesize($stylefile));
	if(!isset($internal_default_page))
		$internal_default_page = '';
	if($extern_page == $internal_default_page)
		$page = preg_replace("/\[notathome\](.+?)\[\/notathome\]/s", "", $page); 
	else
		$page = preg_replace("/\[notathome\](.+?)\[\/notathome\]/s", "$1", $page); 
	
	$page = str_replace("[title]", $title, $page);
	$page = str_replace("[text]", $text, $page);
	$page = str_replace("[menue]", generatemenue(@$internal_style, 1, $extern_page), $page);
	$page = str_replace("[menue2]", generatemenue(@$internal_style, 2, $extern_page), $page);
	
	include("news.php");
	$page = str_replace("[news]", getNews(), $page);
	$page = str_replace("[position]",position_to_root($page_data->page_id), $page);
	
	if (strpos ($page, "[gbook-")) {
		include("gbook.php");
		$page = str_replace("[gbook-input]", gbook_input(), $page);
		$page = str_replace("[gbook-pages]", gbook_pages(), $page);
		$page = str_replace("[gbook-content]", gbook_content(), $page);
	}
	if (strpos ($page, "[contact]")) {
		include("contact.php");
		$page = str_replace("[contact]", contact_formular(), $page);
	}
	//
	// end
	//
	_end();
	echo $page;
?>