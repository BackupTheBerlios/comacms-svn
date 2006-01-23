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
		$return_str = '</p><div class="news-block">';
		while($row = mysql_fetch_object($result)) {
			$date_format = $config->Get('news_date_format', 'd.m.Y');
			$date_format .= ' ' . $config->Get('news_time_format', 'H:i:s'); 
			$return_str .= "\t\t\t<div class=\"news\">					
				<div class=\"news-title\">
					<span class=\"news-date\">" . date($date_format, $row->date) . "</span>
					 $row->title
				</div>
				" . nl2br($row->text) . "
				<div class=\"news-author\">" . getUserByID($row->userid) . "</div>
				</div>\r\n";	
		}
		$return_str .= "</div><p>";
		return $return_str;
	}
?>