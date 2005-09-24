<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: gallerypage.php					#
 # created		: 2005-09-21					#
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
	class GalleryPage {
		
		/**
		 * @access public
		 * @var string 
		 */	
		var $HTML = '';
		
		/**
		 * @return void
		 * @param page_id integer
		 */
		function GalleryPage($page_id) {
			if(empty($page_id))
				return;
			$sql = "SELECT page.*, gallery.* " .
				"FROM (" . DB_PREFIX . "pages page " .
				"LEFT JOIN " . DB_PREFIX . "pages_gallery gallery ON page.page_id = gallery.page_id)" .
				"WHERE page.page_id=$page_id AND page.page_type='gallery' " .
				"LIMIT 0,1";
				
			$sql = "SELECT *
				FROM (" . DB_PREFIX ."gallery gallery
				LEFT JOIN " . DB_PREFIX . "pages_gallery page
				ON page.gallery_id = gallery.gallery_id)
				WHERE page.page_id=$page_id";
			$images = db_result($sql);
			$imgmax = 100;
			while($image = mysql_fetch_object($images)) {
				
				
				$sizes = getimagesize($image->gallery_image_thumbnail);
				$margin_top = round(($imgmax - $sizes[1]) / 2);
				$margin_bottom = $imgmax - $sizes[1] - $margin_top;
				$this->HTML .= "\t\t\t\t<div class=\"imageblock\">" .
					"\t\t\t\t\t<a href=\"" . generateUrl($image->gallery_image) . "\">" .
					"\t\t\t\t\t<img style=\"margin-top:" . $margin_top . "px;margin-bottom:" . $margin_bottom . "px;width:" . $sizes[0] . "px;height:" . $sizes[1] . "px;\" src=\"" . generateUrl($image->gallery_image_thumbnail) . "\" alt=\"$image->gallery_image_thumbnail\" /></a><br />" .
					"\t\t\t\t</div>";
			}
		}
	}
?>