<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : textpage.php
 # created              : 2005-09-04
 # copyright            : (C) 2005-2006 The ComaCMS-Team
 # email                : comacms@williblau.de
 #----------------------------------------------------------------------
 # This program is free software; you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation; either version 2 of the License, or
 # (at your option) any later version.
 #----------------------------------------------------------------------
	
	/**
	 * 
	 */
	require_once __ROOT__ . '/classes/page.php';
	require_once __ROOT__ . '/classes/textactions.php';
	
	/**
	 * @package ComaCMS
	 */ 
	class TextPage extends Page{
		/**
		 * @access public
		 * @var string
		 */
		var $Text = '';
		
		/**
		 * @param integer page_id
		 * @param integer change
		 */
		function TextPage($PageID, $change = 0) {
			if(empty($PageID))
				return;
			if($change == 0)
				$sql = 'SELECT text_page_text, text_page_html
					FROM ' . DB_PREFIX . 'pages_text
					WHERE page_id = ' . $PageID;
			else
				$sql = 'SELECT text
					FROM (' . DB_PREFIX . 'pages_history page
					LEFT JOIN ' . DB_PREFIX . 'pages_text_history text ON text.page_id = page.id ) 
					WHERE page.page_id=' . $PageID . '
					ORDER BY  page.page_date ASC
					LIMIT ' . ($change - 1) . ',1';
			if($page_result = db_result($sql)) {
				$page = mysql_fetch_object($page_result);
				if($change == 0) {
					$this->Text = $page->text_page_text;
					$this->HTML = $page->text_page_html;
				}
				else
				{
					$this->Text = $page->text_page_text;
					$this->HTML = TextActions::ConvertToPreHtml($page->text_page_text);
				}
			}
		}
	}

 
?>