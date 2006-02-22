<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: functions.php					#
 # created		: 2005-06-17					#
 # copyright		: (C) 2005-2006 The ComaCMS-Team		#
 # email		: comacms@williblau.de				#
 #----------------------------------------------------------------------#
 # This program is free software; you can redistribute it and/or modify	#
 # it under the terms of the GNU General Public License as published by	#
 # the Free Software Foundation; either version 2 of the License, or	#
 # (at your option) any later version.					#
 #----------------------------------------------------------------------#
	/**
	 *
	 */
	// Constants for image aligns
	define('IMG_ALIGN_NORMAL', 'normal');
	define('IMG_ALIGN_LEFT', 'left');
	define('IMG_ALIGN_CENTER', 'center');
	define('IMG_ALIGN_RIGHT', 'right');
	// Constants for image layouts
	define('IMG_DISPLAY_PICTURE', 'picture');
	define('IMG_DISPLAY_BOX', 'box');
	define('IMG_DISPLAY_BOX_ONLY', 'box_only');
	
	/**
	 * @return string
	 */
	function make_link($Link) {
		
		$Link = encodeUri($Link);
		
		if(eregi("^[a-z0-9\._-]+@+[a-z0-9\._-]+\.+[a-z]{2,4}$", $Link))
			return "mailto:$Link\" class=\"link_email";
		else if(substr($Link, 0, 4) == 'http')
			return "$Link\" class=\"link_extern";
		// TODO: load the title of the page into the link title and set an other css-class if the page does not exists
		return "index.php?page=$Link\" class=\"link_intern";
	}
	
	function makeMedia($Image, $ImageAlign) {
		global $config;
		#[size] {[int_x]X[int_y],[int_maxsize], w[int_maxwidth], thumb=>w180, original=>[orig_x]X[orig_y], big=>800}
		#[format] {box, box_only, picture}
		#[Url]|[Title] => [Url]|box|thumb|[Title]
		#[Url]|[size]|[Title] = [Url]|box|[size]|[Title]
		#[Url]|[display]|[size]|[Title]
		
		// remove spaces at the start and end of the string
		$Image = preg_replace("~^\ *(.+?)\ *$~", '$1', $Image);
		
		$parameter = explode('|', $Image);
		
		// set the path to the local media dir
		$imageUrl = preg_replace("~^\media:\ *(.+?)$~", "data/upload/" . '$1', $parameter[0]);
		// set some other default values
		$imageTitle = $parameter[0];
		$imageWidth = 0;
		$imageHeight = 0;
		$imageSize = 'w180';
		$imageDisplay = IMG_DISPLAY_BOX;
		
		//remove first entry (we don't have to check it)
		unset($parameter[0]);
		
		// go through each parameter
		foreach($parameter as $key => $value) {
			// extract the image layout
			if(preg_match('~^(' . IMG_DISPLAY_BOX_ONLY .'|' . IMG_DISPLAY_BOX . '|' . IMG_DISPLAY_PICTURE . ')$~', $value))
				$imageDisplay = $value;
			// extract the size for the image
			else if(preg_match('~^(thumb|original|big|[0-9]+[Xx][0-9]+|[0-9]+|\w[0-9]+)$~', $value))
				$imageSize = $value;
			else // its the Title of the picture (it is the last unused parameter)
				$imageTitle = $value;
		}
		// is te file available?
		if(!file_exists($imageUrl))
			return '';
		// resize the image (?):
		
		// get the original sizes
		list($originalWidth, $originalHeight) = getimagesize($imageUrl);
		
		// convert the 'name-sizes' to 'pixel-sizes' 
		if($imageSize == 'thumb') 
			$imageSize = 'w180'; //width: 180px
		else if ($imageSize == 'big')
			$imageSize = '800'; // maximal width/length: 800px
		else if ($imageSize == 'original') {
			// took the original sizes
			$imageWidth = $originalWidth;
			$imageHeight = $originalHeight;
		}
		// 'width-format''
		if(preg_match('~^w[0-9]+$~', $imageSize)) {
			$imageWidth = substr($imageSize, 1);
			// calculate the proporitonal height 
			$imageHeight = round($originalHeight / $originalWidth *  $imageWidth, 0);
		}
		// 'maximal-format'
		else if(preg_match('~^[0-9]+$~', $imageSize)) {
			// look for the longer side and resize it to te given size,
			// short the other side proportional to the longer side
			$imageWidth = ($originalWidth > $originalHeight) ? round($imageSize, 0) : round($originalWidth / ($originalHeight / $imageSize), 0);
			$imageHeight = ($originalHeight > $originalWidth) ? round($imageSize, 0) : round($originalHeight / ($originalWidth / $imageSize), 0);
		}
		// 'exacact-size'
		else if(preg_match('~^([0-9]+)[Xx]([0-9]+)$~', $imageSize, $maches)) {
			// took the given sizes
			$imageWidth = $maches[1];
			$imageHeight = $maches[2];
			
		}
		
		$originalUrl = str_replace(' ', '%20', basename($imageUrl));
		
		// should we generate a thumbnail?
		// has it the original size?
		// would it be bigger than the original?
		if(($imageWidth != $originalWidth && $imageHeight != $originalHeight) || ($imageWidth * $imageHeight < $originalWidth * $originalHeight)) {
			// generate the path of the thumbnail
			$thumbnails = $config->Get('thumbnailfolder', 'data/thumbnails/');
			$oldUrl = $imageUrl;
			$fileName = basename($imageUrl);
			$fileName = $thumbnails . $imageWidth . 'x' . $imageHeight . '_' . $fileName;
			// check if the file already exists
			if(!file_exists($fileName)) { // it doesn't exists! 
				// resize it!
				if(resizeImage($imageUrl, $fileName, $imageWidth, $imageHeight)) // was the resize action succesfull?
					// set the thumbnail-path 
					$imageUrl = $fileName; 
			}
			else
				// set the thumbnail-path 
				$imageUrl = $fileName;
		}
		
		// generate the HTML-code for the images
		
		// remove spaces from the image_url
		$imageUrl = str_replace(' ', '%20', $imageUrl);
		$imageString = '';
		
		$imageTitle = str_replace('"', '&quot;', $imageTitle);
		
		// HTMLcode for the box style
		if($imageDisplay == IMG_DISPLAY_BOX) {
			
			$imageString = "\n\n<div class=\"thumb t" . $ImageAlign . "\">
						<div style=\"width:" . ($imageWidth + 4) . "px\">
							<img width=\"$imageWidth\" height=\"$imageHeight\" src=\"$imageUrl\" title=\"$imageTitle\" alt=\"$imageTitle\" />
							<div class=\"description\" title=\"$imageTitle\"><div class=\"magnify\"><a href=\"special.php?page=image&amp;file=$originalUrl\" title=\"vergr&ouml;&szlig;ern\"><img src=\"img/magnify.png\" title=\"vergr&ouml;&szlig;ern\" alt=\"vergr&ouml;&szlig;ern\"/></a></div>$imageTitle</div>
						</div>
					</div>\n";
		}
		// HTMLcode for the box style without a title
		else if($imageDisplay == IMG_DISPLAY_BOX_ONLY) {
			$imageString = "\n\n<div class=\"thumb tbox t" . $ImageAlign . "\">
						<div style=\"width:" . ($imageWidth + 4) . "px\">
							<img width=\"$imageWidth\" height=\"$imageHeight\" src=\"$imageUrl\" title=\"$imageTitle\" alt=\"$imageTitle\" />
							<div class=\"magnify\"><a href=\"special.php?page=image&amp;file=$originalUrl\" title=\"vergr&ouml;&szlig;ern\"><img src=\"img/magnify.png\" title=\"vergr&ouml;&szlig;ern\" alt=\"vergr&ouml;&szlig;ern\"/></a></div>
						</div>
					</div>\n";
		}
		// HTMLcode for the picture only
		else if($imageDisplay == IMG_DISPLAY_PICTURE) {
			$imageString = "\n\n<div class=\"thumb tbox t" . $ImageAlign . "\">
					<img width=\"$imageWidth\" height=\"$imageHeight\" src=\"$imageUrl\" title=\"$imageTitle\" alt=\"$imageTitle\" />
					</div>";
		}
		return $imageString;
	}
	
	/**
	 * @return string
	 * @param text string
	 * FIXME: port this function into a class
	 */ 
	function convertToPreHtml($text) {
		
		$text = stripslashes($text);
		$text = preg_replace("!(\r\n)|(\r)!","\n",$text);
		$text = "\n" . $text . "\n";
		$text = str_replace('&auml;', 'ä', $text);
		$text = str_replace('&Auml;', 'Ä', $text);
		$text = str_replace('&uuml;', 'ü', $text);
		$text = str_replace('&Uuml;', 'Ü', $text);
		$text = str_replace('&ouml;', 'ö', $text);
		$text = str_replace('&Ouml;', 'O', $text);
		$text = str_replace('&szlig;', 'ß', $text);
		$text = str_replace('&gt;', '>', $text);
		$text = str_replace('&lt;', '<', $text);
		// extract all code we won't compile it <code>...CODE...</code>
		preg_match_all("/\<code\>(.+?)\<\/code\>/s", $text, $matches);
		$codes = array();
		foreach($matches[1] as $key => $match)  {
			$codes[$key] = $matches[1][$key];
			$text = str_replace('<code>' . $matches[1][$key] . '</code>', '[code]%' . $key . '%[/code]', $text);
		}			
		
		// images
		preg_match_all("/\{\{(.+?)\}\}/s", $text, $images);
		$images_html = array();
		foreach($images[1] as $key => $match)  {
			$first_is_space = (substr($images[1][$key],0,1) == ' ');
			$last_is_space = (substr($images[1][$key],-1,1) == ' ');
			if($last_is_space && $first_is_space)
				$images_html[$key] = makeMedia($images[1][$key], IMG_ALIGN_CENTER);
			else if($first_is_space)
				$images_html[$key] = makeMedia($images[1][$key], IMG_ALIGN_LEFT);
			else if($last_is_space)
				$images_html[$key] = makeMedia($images[1][$key], IMG_ALIGN_RIGHT);
			else
				$images_html[$key] = makeMedia($images[1][$key], IMG_ALIGN_NORMAL);
		
			$text = str_replace('{{' . $images[1][$key] . '}}', '%[img]%' . $key .'%[/img]%', $text);
		}
		// 'repair' all urls (with no http:// but a www or ftp)
		$text = preg_replace("/(\ |\\r|\\n|\[)(www|ftp)\.(.+?)\.([a-zA-Z.]{2,6}(|\/.+?))/s", '$1' . "http://$2.$3.$4", $text);
		// remove all html characters
		// TODO: ad a configuration posibility to allow html
		$text = htmlspecialchars($text);
		// fixes for some security bugs
		$text = str_replace("\\r","\r", $text);
		$text = str_replace("\\n","\n", $text);
		$text = preg_replace("!(\r\n)|(\r)!","\n",$text);
		$text = preg_replace("#\\\\(\ |\\r|\\n)#s","<br />\r\n", $text);
		// catch all email-adresses which should be convertet to links ( <email@domain.com>)
		preg_match_all("#\&lt\;([a-z0-9\._-]+?)\@([\w\-]+\.[a-z0-9\-\.]+\.*[\w]+)\&gt\;#s", $text, $emails);
		// allowed auto-link protocols
		$protos = "http|ftp|https";
		// convert urls to links http://www.domain.com to [[http://www.domain.com|www.domain.com]]
		$text = preg_replace("#(?<!\[\[)($protos):\/\/(.+?)(\ |\\n|\\r)#s",'[[$1://$2|$2]]$3', $text);
		$text = preg_replace("#\[\[($protos):\/\/([a-z0-9\-\.]+)\]\]#s",'[[$1://$2|$2]]', $text);
		// convert catched emails into the link format [[]]
		// TODO: add a possibility to covert emails int anti-spam-bot-structures
		foreach($emails[0] as $key => $email)
			$text = str_replace("&lt;".$emails[1][$key].'@'.$emails[2][$key]."&gt;", "[[".$emails[1][$key].'@'.$emails[2][$key]."|".$emails[1][$key].'@'.$emails[2][$key]."]]", $text);
		// catch all links
		preg_match_all("#\[\[(.+?)\]\]#s", $text, $links);
		$link_list = array();
		$link_nr = 1;
		// replace all links with a short uniqe id to replace them later back
		foreach($links[1] as $link) {
			$link_list[$link_nr] = $link;
			$text = str_replace("[[$link]]", "[[%$link_nr%]]", $text);
			$link_nr++;
		}
		// convert all **text** to <strong>text</strong> => Bold
		$text = preg_replace("/\*\*(.+?)\*\*/s", "<strong>$1</strong>", $text);
		// convert all //text// to <em>text</em> => Italic
		$text = preg_replace("/\/\/(.+?)\/\//s", "<em>$1</em>", $text);
		// convert all __text__ to <u>text</u> => Underline
		$text = preg_replace("/__(.+?)__/s", "<u>$1</u>", $text);
		// convert ==== text ==== to a header <h2>
		$text = preg_replace("#====\ (.+?)\ ====#s", "\n\n<h2>$1</h2>\n", $text);
		// convert === text === to a header <h3>
		$text = preg_replace("#===\ (.+?)\ ===#s", "\n\n<h3>$1</h3>\n", $text);
		// convert == text == to a header <h4>
		$text = preg_replace("#==\ (.+?)\ ==#s", "\n\n<h4>$1</h4>\n", $text);
		// convert <center>text</center> to <div class="center">text</div>
		$text = preg_replace("#&lt;center&gt;(.+?)&lt;/center&gt;#s", "\n\n<div class=\"center\">$1</div>\n", $text);
		// convert ({text}{text}) to two colums
		$text = preg_replace("#\(\{(.+?)\}\{(.+?)\}\{(.+?)\}\)#s", "\n\n<div class=\"column ctree\">$1</div>\n<div class=\"column ctree\">$2</div><div class=\"column ctree\">$3</div><p class=\"after_column\">\n", $text);
		// convert ({text}{text}{text}) to tree colums
		$text = preg_replace("#\(\{(.+?)\}\{(.+?)\}\)#s", "\n\n<div class=\"column ctwo\">$1</div>\n<div class=\"column ctwo\">$2</div>\n<div class=\"column ctree\">$3</div><p class=\"after_column\"/>\n", $text);
		
		// paste links into the text
		foreach($link_list as $link_nr => $link) {
			if(preg_match("#^(.+?)\|(.+?)$#i", $link, $link2))				
				$text = str_replace("[[%$link_nr%]]", "<a href=\"" . make_link($link2[1]) . "\">" . $link2[2] . "</a>", $text);
			else
				$text = str_replace("[[%$link_nr%]]", "<a href=\"" . make_link($link) . "\">" . $link . "</a>", $text);
		}
		// paste images into the text
		foreach($images_html as $key => $match)
			$text = str_replace('%[img]%' . $key . '%[/img]%', $match, $text);
		
		$lines = explode("\n", $text);
		$open_list = false;
		$list_has_prev = false;
		$open_sub_list = false;
		$new_text = '';
		$open_table = false;
		$last_line_was_empty = true;
		$paragaph_open = false;
		foreach($lines as $line) {
			//echo ($last_line_was_empty ? '1 - ': '0 - ') . htmlentities($line)."<br/>\r\n";
			if(special_start_with('* ', $line)){
				if($paragaph_open) {
					$new_text .= "</p>\r\n";
					$paragaph_open = false;
				}
				if($open_table) {
					$open_table = false;
					$new_text .= "</table>\r\n";
				}
				if(!$open_list) {
					$new_text .= "<ul>\r\n";
					$open_list = true;
				}
				if($open_sub_list) {
					$open_sub_list = false;
					$new_text .= "</ul>\r\n";
				}
				if($list_has_prev)
					$new_text .= 	"</li>\r\n";
				else
					$list_has_prev = true;
				$new_text .= "\t<li>" . substr(strstr($line, '* '), 2);
				$last_line_was_empty = true;
			}
			elseif(special_start_with('*+ ', $line)){
				if($paragaph_open) {
					$new_text .= "</p>\r\n";
					$paragaph_open = false;
				}
				if($open_table) {
					$open_table = false;
					$new_text .= "</table>\r\n";
				}
				if(!$open_list) {
					$new_text .= "<ul>\r\n\t<li>\r\n";
					$open_list = true;
					$list_has_prev = true;
				}
				
				if(!$open_sub_list) {
					$new_text .= "\r\n\t\t<ul>\r\n";
					$open_sub_list = true;
				}
				$new_text .= "\t\t\t<li>" . substr(strstr($line, '*+ '), 3) . "</li>\r\n";
				$last_line_was_empty = true;
			}
			else if(special_start_with('|',$line) || special_start_with('^',$line)) {
				if($paragaph_open) {
					$new_text .= "</p>\r\n";
					$paragaph_open = false;
				}
				if($open_sub_list) {
					$open_sub_list = false;
					$new_text .= "\t\t</ul>\r\n";
					if($list_has_prev) {
						$new_text .= 	"\t</li>\r\n";
						$list_has_prev = false;
					}
				}
				if($list_has_prev) {
					$new_text .= 	"</li>\r\n";
					$list_has_prev = false;
				}
				if($open_list) {
					$open_list = false;
					$new_text .= "</ul>\r\n";
				}
				if(!$open_table) {
					$open_table = true;
					$new_text .= "<table class=\"text_table\">\r\n";
				}
				$new_text .= "\t<tr>\r\n";
				$row = str_replace('^', '|', $line);
				$row_values = explode('|', $row);
				$max = count($row_values) - 1;
				$row_pos = strlen($row_values[0]) + 1;
				if($line[$row_pos - 1] == '|')
						$element = 'td';
					else	
						$element = 'th';
				for($pos = 1; $pos < $max; $pos++) {
					$new_text .= "\t\t<$element>\r\n\t\t\t".$row_values[$pos]."\r\n\t\t</$element>\r\n";
					$row_pos += 1 + strlen($row_values[$pos]);
					if($line[$row_pos - 1] == '|')
						$element = 'td';
					else	
						$element = 'th';
				}
				$new_text .= "\t</tr>\r\n";
				$last_line_was_empty = true;
				
			}
			else {
				if($open_table) {
					$open_table = false;
					$new_text .= "</table>\r\n";
				}
				if($open_sub_list) {
					$open_sub_list = false;
					$new_text .= "\t\t</ul>\r\n";
					if($list_has_prev) {
						$new_text .= 	"\t</li>\r\n";
						$list_has_prev = false;
					}
				}
				if($list_has_prev) {
					$new_text .= 	"</li>\r\n";
					$list_has_prev = false;
				}
				
				if($open_list) {
					$open_list = false;
					$new_text .= "</ul>\r\n";
				}
				$line = str_replace("\t", '', $line);
				if($line != '' && $line != ' ' && !special_start_with('<h', $line) && !special_start_with('<div', $line) && !special_start_with('[code]', $line))
				{
					if($last_line_was_empty) {
						$new_text .= '<p>'. $line;
						$last_line_was_empty = false;
						$paragaph_open = true;
					}
					else
						$new_text .= "\r\n" . $line;
				}
				else {
					if(special_start_with('$%&', $line . '$%&', array(' ', "\t", "\r"))) {
						if($paragaph_open) {
							$new_text .= "</p>\r\n";
							$paragaph_open = false;
						}
						$last_line_was_empty = true;
					}
					else
						$new_text .= "$line\r\n";
				}
				if(special_start_with('$%&', $line . '$%&', array(' ', "\t", "\r")))
					$last_line_was_empty = true;
				else
					$last_line_was_empty = false;
			}		
		}
		
		$text = $new_text;
		// remove the spaces which are not necessary
		$text = preg_replace('/\ \ +/', ' ', $text);

		$text = str_replace(' -- ', ' &ndash; ', $text);
		
		$text = str_replace(' --- ', ' &mdash; ', $text);
		
		$text = str_replace('(c)', '&copy;', $text);
		
		$text = str_replace('(r)', '&reg;', $text);
		$text = str_replace('ä', '&auml;', $text);
		$text = str_replace('Ä', '&Auml;', $text);
		$text = str_replace('ü', '&uuml;', $text);
		$text = str_replace('Ü', '&Uuml;', $text);
		$text = str_replace('ö', '&ouml;', $text);
		$text = str_replace('Ö', '&Ouml;', $text);
		$text = str_replace('ß', '&szlig;', $text);

		// paste code back
		foreach($codes as $key => $match)
			$text = str_replace('[code]%' . $key . '%[/code]', "<pre class=\"code\">$match</pre>", $text);
			
		return $text;
	}
	
	/** special_start_with
	 * 
	 * Diese Funktion schaut, ob ein String mit einer bestimmten Zeichenkette anf�ngt und ignoriert dabei einige Zeichen,
	 * so k�nnen grunds�tzlich noch Lehrzeichen und Tabs vor der Zeichenkette sein, nach der gesucht wird und dennoch
	 * wird zur�ckgegeben, dass der String mit der gesuchten Zeichenkette beginnt. 
	 * 
	 * @return bool
	 * @param string search
	 * @param string str
	 * @param array $allowedchars
	 */
	function special_start_with($search, $str, $allowedchars = array(' ', "\t")) {
		$str_temp = ' '.$str;
		$search_len = strlen($search);
		do {
			// ein weiteres unerw�nschtes Zeichen entfernen
			$str_temp = substr($str_temp, 1);
			if(substr($str_temp, 0, $search_len) == $search) 
				return true;
			
		} while(in_array(substr($str_temp,0,1), $allowedchars));
		return false;
	}
	//
	// TODO: language-compatibilty
	//
	function menue_edit_view($menue_id = 1) {
		global $d_pre;
		$out = '';
		$menue_result = db_result("SELECT * FROM " . $d_pre . "menue WHERE menue_id='" . $menue_id . "' ORDER BY orderid ASC");
	
		while($menue_data = mysql_fetch_object($menue_result)) {
			$out .= "\t\t\t\t\t<tr>
						<td>" . $menue_data->text . "</td>
						<td>" . $menue_data->link . "</td>
						<td>
							<a href=\"admin.php?page=menueeditor&amp;menue_id=" . $menue_id . "&amp;action=delete&amp;id=" . $menue_data->id . "\" title=\"L?schen\">
								<img src=\"./img/del.jpg\" height=\"16\" width=\"16\" border=\"0\" alt=\"L?schen\" />
							</a>
							<a href=\"admin.php?page=menueeditor&amp;menue_id=" . $menue_id . "&amp;action=up&amp;id=" . $menue_data->id . "\" title=\"Nach Oben\">
								<img src=\"./img/up.jpg\" height=\"16\" width=\"16\" border=\"0\" alt=\"Nach Oben\"/>
							</a>
							<a href=\"admin.php?page=menueeditor&amp;menue_id=" . $menue_id . "&amp;action=down&amp;id=" . $menue_data->id . "\" title=\"Nach Unten\">
								<img src=\"./img/down.jpg\" height=\"16\" width=\"16\" border=\"0\" alt=\"Nach Unten\"/>
							</a>
						</td>
					</tr>\r\n";
		}
		return $out;
	}
	/* string kbormb(int $bytes)
	 * this function convertes a size given in bytes to kilobytes or to megabytes
	 * if its possible
	 */
	function kbormb($bytes) {
		if($bytes < 1024)
			return $bytes . " B";
		elseif($bytes < 1048576)
			return round($bytes/1024, 1) . " KB";
		else
			return round($bytes/1048576, 1) . " MB";
	}
	
	function generatePagesTree($parentid, $tabs = "", $lang = "", $show_deleted = false, $show_hidden = false, $type = 'text') {
		global $_SERVER, $admin_lang;
		
		$out = '';
		$q_lang = '';
		$q_visible = '';
		if($lang != '')
			$q_lang = "AND page_lang='" . $lang . "' ";
		if($show_deleted == false)
			$q_visible = "AND page_visible!='deleted' ";
		if($show_hidden == false)
			$q_visible .= "AND page_visible!='hidden' ";	
		$sql = "SELECT page_parent_id, page_name, page_id, page_title, page_visible
			FROM " . DB_PREFIX . "pages_content
			WHERE page_parent_id=$parentid ".$q_lang.$q_visible." AND page_type='$type'
			ORDER BY page_id ASC";
		$sites_result = db_result($sql);
		if(mysql_num_rows($sites_result) != 0) {
			$out .= "\r\n" . $tabs . "<ol>\r\n";
			while($site_info = mysql_fetch_object($sites_result)) {
				$out .= $tabs . "\t<li>";
				if($site_info->page_visible == 'deleted')
					$out .= '<strike>';
				$out .= '<a href="' . $_SERVER['PHP_SELF'] . '?page=pageeditor&amp;action=info&amp;page_name=' . $site_info->page_name . '">' . $site_info->page_title . '</a> <em>[' . $site_info->page_name . ']</em> <a href="' . $_SERVER['PHP_SELF'] . '?page=pageeditor&amp;action=info&amp;page_name=' . $site_info->page_name . '">[' . $admin_lang['info'] . ']</a>';
				if($site_info->page_visible == 'deleted')
					$out .= '</strike>';
				else
					$out .= ' <a href="' . $_SERVER['PHP_SELF'] . '?page=pageeditor&amp;action=edit&amp;page_name=' . $site_info->page_name . '">[' . $admin_lang['edit'] . ']</a> <a href="' . $_SERVER['PHP_SELF'] . '?page=pageeditor&amp;action=delete&amp;page_name=' . $site_info->page_name . '">[' . $admin_lang['delete'] . ']</a>';
				
				$out .= generatePagesTree($site_info->page_id, $tabs . "\t\t", $lang, $show_deleted, $show_hidden, $type) . "</li>\r\n";
				
			}
			$out .= $tabs . "</ol>\r\n";
			$out .= substr($tabs, 0, -1);
		}
		return $out;
	}

	function getSubmitVar($name, $default = "") {
		global $_GET, $_POST;
		
		if(isset($_GET[$name]))
			return $_GET[$name];
		elseif(isset($_POST[$name]))
			return $_POST[$name];
		return $default;
	}
	
	function generateThumb($file, $outputdir, $maxsize= 100) {
	
		list($width, $height) = getimagesize($file);
		
		$newfile = $outputdir . basename($file);
		preg_match("'^(.*)\.(gif|jpe?g|png|bmp)$'i", $file, $ext);
		//$newfile = './data/thumbnails/' . $ext[1] . '.' . $ext[2];
		
		if($width > $maxsize || $height > $maxsize) {
			$newwidth = ($width > $height) ? $maxsize : $width / ($height / $maxsize);
			$newheight = ($height > $width) ? $maxsize : $height / ($width / $maxsize);
			
			$memory_limit = ini_get("memory_limit");
			if(substr($memory_limit, -1) == 'M')
				$memory_limit = substr($memory_limit, 0, -1) * 1048576;
			//
			// mostly all php-binarys for windows are not compiled with --enable-memory-limit
			// and don't suport memory_get_usage() and are able to handle bigger data
			// (it is not bad for us) 
			//
			if(function_exists('memory_get_usage'))
				$free_memory = $memory_limit - memory_get_usage();
			else
				$free_memory = 0;

			$needspace = ($width * $height + $newwidth * $newheight) * 5;
			// check for enough available memory to resize the image
			if($needspace > $free_memory && $free_memory > 0)
				return false;
			
			$newimage = ImageCreateTrueColor($newwidth, $newheight);
			
			switch (strtolower($ext[2])) {
				case 'jpg' :
				case 'jpeg': $source  = imagecreatefromjpeg($file);
					break;
				case 'gif' : $source  = imagecreatefromgif($file);
					break;
				case 'png' : $source  = imagecreatefrompng($file);
					break;
				default    : return false;
			}
			imagecopyresized($newimage, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
			switch (strtolower($ext[2])) {
				case 'jpg' :
				case 'jpeg': imagejpeg($newimage, $newfile ,100);
					break;
				case 'gif' : imagepng($newimage, $newfile . '.png');
					break;
				case 'png' : imagepng($newimage, $newfile);
					break;
			}
			//imagejpeg($newimage, $newfile ,100);
			
			return true;
		}
		else
			return copy($file, $newfile);
	}
	
	/**
	 * @return string filename of the thumbnail
	 */
	function resizteImageToMaximum($InputFile, $OutputDir, $Maximum) {
		list($originalWidth, $originalHeight) = getimagesize($InputFile);
		$width = ($originalWidth > $originalHeight) ? round($Maximum, 0) : round($originalWidth / ($originalHeight / $Maximum), 0);
		$height = ($originalHeight > $originalWidth) ? round($Maximum, 0) : round($originalHeight / ($originalWidth / $Maximum), 0);
		$outputFile = (substr($OutputDir, -1) == '/') ? $OutputDir :  $OutputDir . '/';
		$outputFile .= $width . 'x' . $height . '_'. basename($InputFile);
		return resizeImage($InputFile, $outputFile, $width, $height);
	}
	
	/**
	 * @return string filename of the thumbnail
	 */
	function resizteImageToWidth($InputFile, $OutputDir, $Width) {
		if(!file_exists($InputFile))
			return false;
		list($originalWidth, $originalHeight) = getimagesize($InputFile);
		$height = round($originalHeight / $originalWidth *  $Width, 0);
		$outputFile = (substr($OutputDir, -1) == '/') ? $OutputDir :  $OutputDir . '/';
		$outputFile .= $Width . 'x' . $height . '_'. basename($InputFile);
		return resizeImage($InputFile, $outputFile, $Width, $height);
	}
	
	/**
	 * @return string filename of the thumbnail
	 */
	function resizeImage($InputFile, $OutputFile, $Width, $Height) {
		
		preg_match("'^(.*)\.(gif|jpe?g|png|bmp)$'i", $InputFile, $ext);
		
		if(file_exists($OutputFile))
			return $OutputFile;
		list($originalWidth, $originalHeight) = getimagesize($InputFile);

		$memory_limit = ini_get("memory_limit");
		if(substr($memory_limit, -1) == 'M')
			$memory_limit = substr($memory_limit, 0, -1) * 1048576;
		/*
		 * mostly all php-binarys for windows are not compiled with --enable-memory-limit
		 * and don't suport memory_get_usage() and are able to handle bigger data
		 * (it is not bad for us) 
		 */
		if(function_exists('memory_get_usage'))
			$free_memory = $memory_limit - memory_get_usage();
		else
			$free_memory = 0;
		$needspace = ($originalWidth * $originalHeight + $Width * $Height) * 5;
		// check for enough available memory to resize the image
		if($needspace > $free_memory && $free_memory > 0)
			return false;
		
		$newImage = ImageCreateTrueColor($Width, $Height);
			
		switch (strtolower($ext[2])) {
			case 'jpg' :
			case 'jpeg': $source  = imagecreatefromjpeg($InputFile);
				break;
			case 'gif' : $source  = imagecreatefromgif($InputFile);
				break;
			case 'png' : $source  = imagecreatefrompng($InputFile);
				break;
			default    : return false;
		}
		imagecopyresized($newImage, $source, 0, 0, 0, 0, $Width, $Height, $originalWidth, $originalHeight);
		switch (strtolower($ext[2])) {
			case 'jpg' :
			case 'jpeg': imagejpeg($newImage, $OutputFile, 100);
				break;
			case 'gif' : imagepng($newImage, $OutputFile . '.png');
				$OutputFile .= '.png';
				break;
			case 'png' : imagepng($newImage, $OutputFile);
				break;
		}
		return $OutputFile;
	}
	
	function generateUrl($string) {
		$string = preg_replace("~^\ *(.+?)\ *$~", "$1", $string);
		return str_replace(" ", "%20", $string);
	}
	
	function isUTF8($string)
	{
   		if(strpos(utf8_encode($string), "?", 0) !== false ) // "?" is ALT+159
         		return true;  // the original string was utf8
   		return false; // no utf8
	}
?>
