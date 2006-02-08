<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: news.php					#
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
	 * @return string
	 * @param last integer
	 */
	function getNews($last = 6) {
		global $config;
		include_once("functions.php");
		$sql = "SELECT *
			FROM " . DB_PREFIX . "news
			ORDER BY date DESC LIMIT 0, $last";
		$result = db_result($sql);
		$newsTitle =  $config->Get('news_title', '');
		if($newsTitle != '')
			$newsTitle = '<h3>' . $newsTitle . '</h3>';
		$returnStr = '</p><div class="news-block">' . $newsTitle;
		$dateFormat = $config->Get('news_date_format', 'd.m.Y');
		$dateFormat .= ' ' . $config->Get('news_time_format', 'H:i:s');
		while($row = mysql_fetch_object($result)) {
			$returnStr .= "\t\t\t<div class=\"news\">					
				<div class=\"news-title\">
					<span class=\"news-date\">" . date($dateFormat, $row->date) . "</span>
					 $row->title
				</div>
				" . nl2br($row->text) . "\r\n";
					 
			if($config->Get('news_display_author', 1) == 1)	
				$returnStr .= "<div class=\"news-author\">" . getUserByID($row->userid) . "</div>\r\n";
			$returnStr .= "</div>\r\n";	
		}
		$returnStr .= "</div><p>";
		return $returnStr;
	}
?>