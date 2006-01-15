<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: index.php					#
 # created		: 2005-07-11					#
 # copyright		: (C) 2005 The ComaCMS-Team			#
 # email		: comacms@williblau.de				#
 #----------------------------------------------------------------------#
 # This program is free software; you can redistribute it and/or modify	#
 # it under the terms of the GNU General Public License as published by	#
 # the Free Software Foundation; either version 2 of the License, or	#
 # (at your option) any later version.					#
 #									#
 # This program is distributed in the hope that it will be useful,	#
 # but WITHOUT ANY WARRANTY; without even the implied warranty of	#
 # MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the	#
 # GNU General Public License for more details.				#
 #									#
 # You should have received a copy of the GNU General Public License	#
 # along with this program; if not, write to the Free Software		#
 # Foundation, Inc., 59 Temple Place, Suite 330,			#
 # Boston, MA  02111-1307  USA						#
 #----------------------------------------------------------------------#

	/**
	 * Set a global to make sure that common.php is executet in the
	 * only right context
	 */
	define("COMACMS_RUN", true);
	include('common.php');
	
	$page->LoadPage($extern_page, $user);
	if($page->FindTag('INLINEMENU')) {
		$inlinemenu = new InlineMenu($page);
		$inlinemenu_html = $inlinemenu->LoadInlineMenu();
		$page->ReplaceTagInTemplate('INLINEMENU', $inlinemenu_html);
		if($inlinemenu_html != '')
			$page->Template = preg_replace("/\<forinlinemenu\>(.+?)\<\/forinlinemenu\>/s", "$1", $page->Template);
		else
			$page->Template = preg_replace("/\<forinlinemenu\>(.+?)\<\/forinlinemenu\>/s", "", $page->Template);
	}
	if($page->FindTagInText('articles-preview'))
		$page->ReplaceTagInText('articles-preview', articlesPreview(5));
	include('news.php');
	
	if($page->FindTagInText('news')) {
		$news_display_count = $config->Get('news_display_count', 6);
		if(!is_numeric($news_display_count))
			$news_display_count = 6;
		$page->ReplaceTagInText('news', getNews($news_display_count));
	}
	if($page->FindTagInText('dates'))
		$page->ReplaceTagInText('dates', nextDates(10));
	$page->Template = preg_replace("/\<notinadmin\>(.+?)\<\/notinadmin\>/s", '$1', $page->Template);
	//else
	//	$page->Template = preg_replace("/\<forinlinemenu\>(.+?)\<\/forinlinemenu\>/s", "", $page->Template);
/*	$sql = "SELECT cont.*, inline.*
		FROM ( " . DB_PREFIX. "pages_content cont
		LEFT JOIN " . DB_PREFIX . "inlinemenu inline ON inline.inlinemenu_id = cont.page_inlinemenu )
		WHERE cont.page_name='$extern_page' AND cont.page_type='text'";
*///	$page_result = db_result($sql);
//	if(!$page_result)
//		die("bad error:  no pagedata found");
//	if(!($page_data = mysql_fetch_object($page_result)))
//		die("bad error:  no sitedata found");
//	$title = $page_data->page_title;
//	$text = $page_data->page_html;
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
/*	if (strpos ($page, "[gbook-")) {
		include("gbook.php");
		$page = str_replace("[gbook-input]", gbook_input(), $page);
		$page = str_replace("[gbook-pages]", gbook_pages(), $page);
		$page = str_replace("[gbook-content]", gbook_content(), $page);
	}
	if (strpos ($page, "[contact]")) {
		include("contact.php");
		$page = str_replace("[contact]", contact_formular(), $page);
	}*/

//	$page = str_replace("[dates]", nextDates(10), $page);
	//
	// end
	//

	echo $page->OutputHTML();
	echo "\r\n<!-- rendered in " . round(getmicrotime(microtime()) - getmicrotime($starttime), 4) . ' seconds with ' . $queries_count .' SQL queries -->';

?>