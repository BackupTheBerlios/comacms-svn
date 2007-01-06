<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2007 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : inlinemenu.php
 # created              : 2005-09-10
 # copyright            : (C) 2005-2007 The ComaCMS-Team
 # email                : comacms@williblau.de
 #----------------------------------------------------------------------
 # This program is free software; you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation; either version 2 of the License, or
 # (at your option) any later version.
 #----------------------------------------------------------------------

	/**
	 * @package ComaCMS
	 */
	class InlineMenu {
		
		/**
		 * @var Page 
		 * @access public
		 */
		//var $Page = null;
		
		/**
		 * @param Page Page
		 * @return void
		 */		 
		/*function InlineMenu($PageID) {
			//if(!empty($Page))
				$this->PageID = $PageID;
		}*/
		
		/**
		 * @return array
		 */
		function LoadInlineMenu($SqlConnection ,$PageID) {
			$sql = "SELECT inlinemenu_html, inlinemenu_image, inlinemenu_image_thumb, inlinemenu_image_title
				FROM " . DB_PREFIX . "inlinemenu
				WHERE page_id = $PageID";
			$inlinemenu_result = $SqlConnection->SqlQuery($sql);
			$replacements = array();
			if($inlinemenu = mysql_fetch_object($inlinemenu_result)) {
				if($inlinemenu->inlinemenu_html != ''  || $inlinemenu->inlinemenu_image_thumb!= '') {
					//include($this->Page->Templatefolder . '/menu.php');
					///$out = str_replace('[TEXT]', $inlinemenu->inlinemenu_html, $menu_inline);
					$replacements['INLINEMENU_TEXT'] = $inlinemenu->inlinemenu_html;
					$imageString = '';
					if(file_exists($inlinemenu->inlinemenu_image_thumb)){
						list($imageWidth, $imageHeight) = getimagesize($inlinemenu->inlinemenu_image_thumb);
						$imageString = "<div class=\"thumb\">
	<img width=\"200\" height=\"$imageHeight\"src=\"" . generateUrl($inlinemenu->inlinemenu_image_thumb) . "\" title=\"$inlinemenu->inlinemenu_image_title\" alt=\"$inlinemenu->inlinemenu_image_title\" />
	<div class=\"description\" title=\"$inlinemenu->inlinemenu_image_title\">
		<div class=\"magnify\">
			<a href=\"special.php?page=image&amp;file=" . generateUrl(basename($inlinemenu->inlinemenu_image)) . "\" title=\"vergr&ouml;&szlig;ern\">
				<img src=\"img/magnify.png\" title=\"vergr&ouml;&szlig;ern\" alt=\"vergr&ouml;&szlig;ern\"/>
			</a>
		</div>$inlinemenu->inlinemenu_image_title
	</div>
</div>";
					}
					//$out = str_replace('[IMG]', $imageString, $out);
					$replacements['INLINEMENU_IMAGE'] = $imageString;
				}
			}
			
			return $replacements;
		}
	}
?>