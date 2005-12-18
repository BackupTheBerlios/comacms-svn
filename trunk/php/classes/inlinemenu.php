<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: inlinemenu.php				#
 # created		: 2005-09-10					#
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
	class InlineMenu {
		
		/**
		 * @var Page 
		 * @access public
		 */
		var $Page = null;
		
		/**
		 * @param Page
		 * @return void
		 */		 
		function InlineMenu($page) {
			if(!empty($page))
				$this->Page = $page;
		}
		
		/**
		 * @return string The html-code for the inlinemenu, based on the style files
		 */
		function LoadInlineMenu() {
			$sql = "SELECT inlinemenu_html, inlinemenu_image
				FROM " . DB_PREFIX . "inlinemenu
				WHERE page_id = " . $this->Page->PageID;
			$inlinemenu_result = db_result($sql);
			$out = '';
			if($inlinemenu = mysql_fetch_object($inlinemenu_result)) {
				if($inlinemenu->inlinemenu_html != '') {
					include($this->Page->Templatefolder . '/menu.php');
					$out = str_replace('[TEXT]', $inlinemenu->inlinemenu_html, $menu_inline);
					$out = str_replace('[IMG]', generateUrl($inlinemenu->inlinemenu_image), $out);
				}
			}
			
			return $out;
		}
	}
?>