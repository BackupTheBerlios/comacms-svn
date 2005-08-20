<?php
/*****************************************************************************
 *
 *  file		: gallery.php
 *  created		: 2005-06-24
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
 *****************************************************************************/
	define("COMACMS_RUN", true);
	
	include("common.php");
		
	
	$sql = "SELECT *
		FROM " . DB_PREFIX . "pages_content
		WHERE page_name='$extern_page' AND page_type='gallery'";
	$page_result = db_result($sql);
	if(!($page_data = mysql_fetch_object($page_result)))
		header("Location: special.php?page=404&notfound=g:$extern_page");
	$title = $page_data->page_title;
	$text = $page_data->page_text;
	$images = explode("\r\n", $page_data->page_text);
	$image_count = count($images);
	$text = '';
	for($i = 1;$i < $image_count; $i++) {
		$thumb = str_replace('/upload/', '/thumbnails/', $images[$i]);
				preg_match("'^(.*)\.(gif|jpe?g|png|bmp)$'i", $thumb, $ext);
				if(strtolower($ext[2]) == 'gif')
					$thumb .= '.png';
		$text .= "<div class=\"gallery_image\"><img  style=\"margin-top:0px;\" src=\"".$thumb."\"/></div>\r\n";
	}
	
	//
	// insert data into style
	//
	$page = str_replace("[title]", $title, $page);
	$page = str_replace("[text]", $text, $page);
	$page = str_replace("[menue]", generatemenue(@$internal_style, 1, $extern_page), $page);
	$page = str_replace("[menue2]", generatemenue(@$internal_style, 2, $extern_page), $page);
	$page = str_replace("[position]", position_to_root($page_data->page_id), $page);
	
	//
	// end
	//
	echo $page;
?>