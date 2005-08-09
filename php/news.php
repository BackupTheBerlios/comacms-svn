<?
/*****************************************************************************
 *
 *  file		: news.php
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
 *****************************************************************************/


	function getNews($last = 6) {
		include_once("functions.php");
		$sql = "SELECT *
			FROM " . DB_PREFIX . "news
			ORDER BY date DESC LIMIT 0, $last";
		$result = db_result($sql);
		$return_str = '';
		while($row = mysql_fetch_object($result)) {
			$return_str .= "\t\t\t<div class=\"news\">
				<div class=\"news-title\">
					" . $row->title . "
					<span class=\"news-title\">" . date('d.m.Y H:i:s', $row->date) . "</span>
				</div>
				" . nl2br($row->text) . "
				<span class=\"news-author\">&nbsp;" . getUserByID($row->userid) . "</span>
				</div>\r\n";	
		}
	
		return $return_str;
	}
?>