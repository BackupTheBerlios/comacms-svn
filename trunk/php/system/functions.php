<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: functions.php					#
 # created		: 2005-06-17					#
 # copyright		: (C) 2005 The ComaCMS-Team			#
 # email		: comacms@williblau.de				#
 #----------------------------------------------------------------------#
 # This program is free software; you can redistribute it and/or modify	#
 # it under the terms of the GNU General Public License as published by	#
 # the Free Software Foundation; either version 2 of the License, or	#
 # (at your option) any later version.					#
 #----------------------------------------------------------------------#

	
	/*function alt($link) {
		$text = preg_replace("/(.+?)\|(.+$)/s","$1\" alt=\"\\2", $link);
		echo $link.'<br \>' . $text . '<br \>';
		return $text;
	}*/
	
	/**
	 * @return string
	 */
	function make_link($link) {
		
		
		if(eregi("^[a-z0-9\._-]+@+[a-z0-9\._-]+\.+[a-z]{2,4}$", $link))
			return "mailto:$link\" class=\"link_email";
		else if(substr($link,0,4) == 'http')
			return "$link\" class=\"link_extern";
		// TODO: load the title of the page into the link title and set an other css-class if the page does not exists
		return "index.php?page=$link\" class=\"link_intern";
	}
	
	/**
	 * @return string
	 * @param text string
	 * FIXME: port this function into a class
	 */ 
	function convertToPreHtml($text) {
		
		// extract all code we won't compile it <code>...CODE...</code>
		preg_match_all("/\<code\>(.+?)\<\/code\>/s", $text, $matches);
		$codes = array();
		foreach($matches[1] as $key => $match)  {
			$codes[$key] = $matches[1][$key];
			$text = str_replace('<code>' . $matches[1][$key] . '</code>', '[code]%' . $key . '%[/code]', $text);
		}
		// 'repair' all urls (wirh no http://)
		$text = preg_replace("/(\ |\\r|\\n)([A-Za-z]{1,})\.(.+?)\.([a-zA-Z.]{2,6}(|\/.+?))/s", '$1' . "http://$2.$3.$4", $text);
		// remove all html characters
		// TODO: ad a configuration posibility to allow html
		$text = htmlspecialchars($text);
		// fixes for some security bugs
		$text = str_replace("\\r","\r", $text);
		$text = str_replace("\\n","\n", $text);
		$text = preg_replace("!(\r\n)|(\r)!","\n",$text);
		// catch all email-adresses which should be convertet to links ( <email@domain.com>)
		preg_match_all("#\&lt\;([a-z0-9\._-]+?)\@([\w\-]+\.[a-z0-9\-\.]+\.*[\w]+)\&gt\;#s", $text, $emails);
		// allowed auto-link protocols
		$protos = "http|ftp|https";
		// convert urls to links http://www.domain.com to [[http://www.domain.com|www.domain.com]]
		$text = preg_replace("#(?<!\[\[)($protos):\/\/(.+?)(\ |\\n|\\r)#s",'[[$1://$2|$2]]$3', $text);
		
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
		$text = preg_replace("/\/_\/(.+?)\/\/_/s", " <em>$1</em> ", $text);
		// convert all __text__ to <u>text</u> => Underline
		$text = preg_replace("/__(.+?)__/s", "<u>$1</u>", $text);
		// convert === text === to a header
		$text = preg_replace("#===\ (.+?)\ ===#s", "<h3>$1</h3><hr />", $text);
		// convert "/n" to "<br />" (more or less ;-))
		$text = nl2br($text);
		// paste links into the text
		foreach($link_list as $link_nr => $link) {
			if(preg_match("#^(.+?)\|(.+?)$#i", $link, $link2))				
				$text = str_replace("[[%$link_nr%]]", "<a href=\"".make_link($link2[1])."\">" . $link2[2] . "</a>", $text);

			else
				$text = str_replace("[[%$link_nr%]]", "<a href=\"".make_link($link)."\">" . $link . "</a>", $text);
		}
		// paste code back
		foreach($codes as $key => $match)
			$text = str_replace('[code]%' . $key . '%[/code]', "<pre class=\"code\">$match</pre>", $text);
			
		return $text;
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
							<a href=\"admin.php?page=menueeditor&amp;menue_id=" . $menue_id . "&amp;action=delete&amp;id=" . $menue_data->id . "\" title=\"Löschen\">
								<img src=\"./img/del.jpg\" height=\"16\" width=\"16\" border=\"0\" alt=\"Löschen\" />
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

	function setSetting($name, $display, $description, $default = '') {
		global $setting;
		$setting[$name] = array($display, $description, $default);
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
			imagejpeg($newimage, $newfile ,100);
			
			return true;
		}
		else
			return copy($file, $newfile);
	}
	
	function generateUrl($string) {
		return str_replace(" ", "%20", $string);
	}
	
	function generateinlinemenu($page_id) {
		$sql = "SELECT *
			FROM " . DB_PREFIX . "inlinemenu_entries
			WHERE inlineentrie_page_id=$page_id
			ORDER BY inlineentrie_sortid ASC";
		$entries = db_result($sql);
		$text = '';
		while($entrie = mysql_fetch_object($entries)) {
			if($entrie->inlinieentrie_type == 'text')
				$text .= "<div>$entrie->inlineentrie_text</div>";
			elseif($entrie->inlinieentrie_type == 'link')
				$text .= "<div><a href=\"$entrie->inlineentrie_link\">$entrie->inlineentrie_text</a></div>";
			elseif($entrie->inlinieentrie_type == 'intern')
				$text .= "<div><a href=\"$entrie->inlineentrie_link\">$entrie->inlineentrie_text</a></div>";
		}
		$sql = "UPDATE " . DB_PREFIX . "inlinemenu
			SET inlinemenu_html='$text'
			WHERE page_id='$page_id'";
		db_result($sql);	
	}
?>