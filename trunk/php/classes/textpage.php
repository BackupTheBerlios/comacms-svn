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
	 * @package ComaCMS
	 */ 
	class TextPage {
	
		var $Text = '';
		var $HTML = '';
		
		function TextPage($page_id) {
			if(empty($page_id))
				return;
			$sql = "SELECT *
				FROM " . DB_PREFIX . "pages_text
				WHERE page_id = $page_id";
			if($page_result = db_result($sql)) {
				$page = mysql_fetch_object($page_result);
				$this->Text = $page->text_page_text;
				$this->HTML = $page->text_page_html;
			}
		}
	}

 
?>