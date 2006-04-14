<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: gallerypage.php				#
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
	 * 
	 */
	require_once ('classes/page.php');
	
	/**
	 * @package ComaCMS
	 */ 
	class GalleryPage  extends Page{
		
		/**
		 * @return void
		 * @param integer page_id
		 * @access public
		 */
		function GalleryPage($PageID) {
			// is there a PageID?
			if(empty($PageID))
				return;
			// Get the imageID
			$imageID = GetPostOrGet('imageID');
			// If the value of $imageID is plausible try to show the image
			if(is_numeric($imageID) && !empty($imageID)) {
				$imageID = abs($imageID);
				global $config;
				$thumbnailfoler = $config->Get('thumbnailfolder', 'data/thumbnails/');
				$sql = "SELECT *
					FROM (" . DB_PREFIX ."gallery gallery
					LEFT JOIN " . DB_PREFIX . "pages_gallery page
					ON page.gallery_id = gallery.gallery_id)
					WHERE page.page_id=$PageID
					ORDER BY gallery.gallery_orderid";
				$images = db_result($sql);
				$lastImageID = -1;
				// the html code for the image
				$imageHTML = '';
				// the html code for the links (to navigate in the gallery)
				$linksHTML = '';
				// go through all images of the gallery
				while($galleryImage = mysql_fetch_object($images)) {
					$galleryImageID = $galleryImage->gallery_file_id;
					// is the actual image the image we are looking for?
					if($galleryImageID == $imageID) {
						// get the image
						$imageHTML = "<div class=\"gallery_image_detail\"><img alt=\"$galleryImage->gallery_description\" title=\"$galleryImage->gallery_description\" src=\"" . generateUrl(resizeImageToMaximum($galleryImage->gallery_image, $thumbnailfoler, 600)) . "\"/>
								<div class=\"gallery_image_detail_description\">$galleryImage->gallery_description</div></div>";
						// isn't it the first image?
						if($lastImageID != -1)
							// add a link to the pervious image
							$linksHTML  .= "<a class=\"imagemove\" href=\"index.php?page=$PageID&amp;imageID=$lastImageID\"><img alt=\"previous\" src=\"img/previous.png\" /></a>\r\n";
						// add a link to the gallery
						$linksHTML  .= "<a class=\"imagemove\" href=\"index.php?page=$PageID\"><img alt=\"up\" src=\"img/up.png\" /></a>\r\n";
					}
					// was the last image the image we were looking for?
					if($lastImageID == $imageID) {
						// add a link to navigate to the next image
						$linksHTML  .="<a class=\"imagemove\" href=\"index.php?page=$PageID&amp;imageID=$galleryImageID\" ><img alt=\"next\" src=\"img/next.png\" /></a>\r\n";
					}
					$lastImageID = $galleryImageID;
				}
				// generate the final html code
				$this->HTML .= "<div class=\"imagebox\">
				<div class=\"imagelinks\">
					$linksHTML
				</div>
				$imageHTML
				<div class=\"imagelinks\">
					$linksHTML
				</div>
					</div>\r\n";
				return;
			}
			// show the gallery
			$sql = "SELECT *
				FROM (" . DB_PREFIX ."gallery gallery
				LEFT JOIN " . DB_PREFIX . "pages_gallery page
				ON page.gallery_id = gallery.gallery_id)
				WHERE page.page_id=$PageID
				ORDER BY gallery.gallery_orderid";
			$images = db_result($sql);
			$imgmax = 100;
			$this->HTML .= "<div class=\"imagesblock\">";
			while($image = mysql_fetch_object($images)) {
				
				if(file_exists($image->gallery_image_thumbnail)) {
					$sizes = getimagesize($image->gallery_image_thumbnail);
					$margin_top = round(($imgmax - $sizes[1]) / 2);
					$margin_bottom = $imgmax - $sizes[1] - $margin_top;
					$this->HTML .= "\t\t\t\t<div class=\"imageblock\">
						<a href=\"index.php?page=$PageID&amp;imageID=" . $image->gallery_file_id . "\">
						<img style=\"margin-top:" . $margin_top . "px;margin-bottom:" . $margin_bottom . "px;width:" . $sizes[0] . "px;height:" . $sizes[1] . "px;\" src=\"" . generateUrl($image->gallery_image_thumbnail) . "\" alt=\"$image->gallery_image_thumbnail\" /></a><br />
					</div>\r\n";
				}
			}
			$this->HTML .= "</div>";
		}
	}
?>