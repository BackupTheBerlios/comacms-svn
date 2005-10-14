<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: gbook.php					#
 # created		: 2005-09-04					#
 # copyright		: (C) 2005 The ComaCMS-Team			#
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
	require_once ('classes/page.php');
	
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
		 * @param integer history_id
		 */
		function TextPage($page_id, $history_id = -1) {
			if(empty($page_id))
				return;
			if($history_id == -1)
			$sql = "SELECT *
				FROM " . DB_PREFIX . "pages_text
				WHERE page_id = $page_id";
			else
			$sql = "SELECT *
				FROM " . DB_PREFIX . "pages_text_history
				WHERE page_id=$history_id";
			if($page_result = db_result($sql)) {
				$page = mysql_fetch_object($page_result);
				if($history_id == -1) {
					$this->Text = $page->text_page_text;
					$this->HTML = $page->text_page_html;
				}
				else
				{
					$this->Text = $page->page_text;
					$this->HTML = convertToPreHtml($page->page_text);
				}
			}
		}
	}

 
?>