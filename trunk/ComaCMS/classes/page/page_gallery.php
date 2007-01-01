<?php
/**
 * @package ComaCMS
 * @subpackage Page
 * @copyright (C) 2005-2007 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : page_gallery.php
 # created              : 2006-10-06
 # copyright            : (C) 2005-2007 The ComaCMS-Team
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
 	require_once __ROOT__ . '/classes/page/page.php';
	
	/**
	 * @package ComaCMS
	 * @subpackage Page
  	 */
	class Page_Gallery extends Page {
		
		/**
		 * @access public
		 * @param integer PageID
		 * @return boolean Is true on success
		 */
		function LoadPage($PageID) {
			$imageID = GetPostOrGet('imageID');
			//$page = GetPostOrGet('page');
			if(is_numeric($imageID))
				return $this->LoadImagePage($PageID, $imageID);
			else
				return $this->LoadGalleryPage($PageID);
		}
		
		function LoadImagePage($PageID, $ImageID) {
			if(!is_numeric($PageID) || !is_numeric($ImageID))
				return false;
			$sql = 'SELECT gallery.gallery_description, gallery.gallery_image, page.page_title
					FROM (
						(' . DB_PREFIX . 'pages page
						LEFT JOIN ' . DB_PREFIX . 'pages_gallery gallery_page
						ON page.page_id = gallery_page.page_id)
					LEFT JOIN ' . DB_PREFIX . 'gallery gallery
					ON gallery_page.gallery_id =gallery.gallery_id)
					WHERE page.page_id=' . $PageID . '
					ORDER BY gallery.gallery_orderid
					LIMIT ' . $ImageID . ',2'; // we have to check if there is a second one;
			$imageResult = $this->_SqlConnection->SqlQuery($sql);
			
			if($image = mysql_fetch_object($imageResult)) {
				$this->_ComaLate->SetReplacement('LANG_IMAGE_OF', $this->_Translation->GetTranslation('image_of'));
				$this->_ComaLate->SetReplacement('LANG_IMAGE', $this->_Translation->GetTranslation('image'));
				$this->_ComaLate->SetReplacement('PAGE_ID', $PageID);
				$this->_ComaLate->SetReplacement('LANG_UP', $this->_Translation->GetTranslation('up'));
				$this->_ComaLate->SetReplacement('PAGE_TITLE', $image->page_title);
				$this->_ComaLate->SetReplacement('IMAGE_DESCRIPTION', $image->gallery_description);
				if($ImageID > 0) {
					$this->_ComaLate->SetReplacement('LAST_IMAGE_ID', $ImageID - 1);
					$this->_ComaLate->SetReplacement('LANG_PREVIOUS', $this->_Translation->GetTranslation('previous_image'));
					$this->_ComaLate->SetCondition('imageBack');
				}
				
					
				$thumbnailFoler = $this->_Config->Get('thumbnailfolder', 'data/thumbnails/');
				$imageUrl = resizeImageToMaximum($image->gallery_image, $thumbnailFoler, 600);
				$imageSrc = generateUrl($imageUrl);
				if(file_exists($imageUrl)) {
				$sizes = getimagesize($imageUrl);
				$this->_ComaLate->SetReplacement('IMAGE_WIDTH', $sizes[0]);
				$this->_ComaLate->SetReplacement('IMAGE_HEIGHT', $sizes[1]);
				$this->_ComaLate->SetReplacement('IMAGE_WIDTH+4', $sizes[0] + 4);
				$this->_ComaLate->SetReplacement('IMAGE_SRC', $imageSrc);
				}
				
			}
			else
				return false;
				
			if($image = mysql_fetch_object($imageResult)) {
					$this->_ComaLate->SetReplacement('NEXT_IMAGE_ID', $ImageID + 1);
					$this->_ComaLate->SetReplacement('LANG_NEXT', $this->_Translation->GetTranslation('next_image'));
					$this->_ComaLate->SetCondition('imageNext');
			}
			$this->HTML = '<h2>{LANG_IMAGE_OF} {PAGE_TITLE}</h2>
			<div class="imagebox">
				<div class="imagelinks">
					<imageBack:condition><a class="imagemove" href="index.php?page={PAGE_ID}&amp;imageID={LAST_IMAGE_ID}"><img alt="{LANG_PREVIOUS}" title="{LANG_PREVIOUS}" src="img/previous.png" /></a></imageBack>
					<a class="imagemove" href="index.php?page={PAGE_ID}"><img alt="{LANG_UP}" title="{LANG_UP}" src="img/up.png" /></a>
					<imageNext:condition><a class="imagemove" href="index.php?page={PAGE_ID}&amp;imageID={NEXT_IMAGE_ID}" ><img alt="{LANG_NEXT}" title="{LANG_NEXT}" src="img/next.png" /></a></imageNext>
				</div>
				<div class="gallery_image_detail">
				<div class="thumb tcenter">
						<div style="width:{IMAGE_WIDTH+4}px">
							<img width="{IMAGE_WIDTH}" height="{IMAGE_HEIGHT}" src="{IMAGE_SRC}" title="{IMAGE_DESCRIPTION}" alt="{LANG_IMAGE}: {IMAGE_DESCRIPTION}" />
							<div class="description" title="{IMAGE_DESCRIPTION}">{IMAGE_DESCRIPTION}</div>
						</div>
					</div></div>
				<div class="imagelinks">
					<imageBack:condition><a class="imagemove" href="index.php?page={PAGE_ID}&amp;imageID={LAST_IMAGE_ID}"><img alt="{LANG_PREVIOUS}" title="{LANG_PREVIOUS}" src="img/previous.png" /></a></imageBack>
					<a class="imagemove" href="index.php?page={PAGE_ID}"><img alt="{LANG_UP}" title="{LANG_UP}" src="img/up.png" /></a>
					<imageNext:condition><a class="imagemove" href="index.php?page={PAGE_ID}&amp;imageID={NEXT_IMAGE_ID}" ><img alt="{LANG_NEXT}" title="{LANG_NEXT}" src="img/next.png" /></a></imageNext>
				</div>
					</div>';
			if($ImageID >= 0)
				$this->HTML .= '';
		}
		
		function LoadGalleryPage($PageID) {
			
			if(!is_numeric($PageID))
				return false;
			
			$this->_ComaLate->SetReplacement('LANG_GALLERY', $this->_Translation->GetTranslation('gallery'));
			$this->_ComaLate->SetReplacement('LANG_IMAGE', $this->_Translation->GetTranslation('image'));
			$this->_ComaLate->SetReplacement('PAGE_ID', $PageID);
			/*$sql = 'SELECT gallery.gallery_description, gallery.gallery_image_thumbnail
					FROM (' . DB_PREFIX . 'gallery gallery
					LEFT JOIN ' . DB_PREFIX . 'pages_gallery page
					ON page.gallery_id = gallery.gallery_id)
					WHERE page.page_id=' . $PageID . '
					ORDER BY gallery.gallery_orderid';*/
			$sql = 'SELECT gallery.gallery_description, gallery.gallery_image_thumbnail, page.page_title
					FROM (
						(' . DB_PREFIX . 'pages page
						LEFT JOIN ' . DB_PREFIX . 'pages_gallery gallery_page
						ON page.page_id = gallery_page.page_id)
					LEFT JOIN ' . DB_PREFIX . 'gallery gallery
					ON gallery_page.gallery_id =gallery.gallery_id)
					WHERE page.page_id=' . $PageID . '
					ORDER BY gallery.gallery_orderid';
			$imagesResult = $this->_SqlConnection->SqlQuery($sql);
			$images = array();
			$imgmax = 100;
			$imageID = 0;
			$title = ''; 
			while($image = mysql_fetch_object($imagesResult)) {
				
				if($title == '')
					$title = $image->page_title;
				if(file_exists($image->gallery_image_thumbnail)) {
					$sizes = getimagesize($image->gallery_image_thumbnail);
					$marginTop = round(($imgmax - $sizes[1]) / 2);
					$marginBottom = $imgmax - $sizes[1] - $marginTop;
				$images[] = array('IMAGE_TITLE' => $image->gallery_description,
								'IMAGE_MARGIN_TOP' => $marginTop,
								'IMAGE_MARGIN_BOTTOM' => $marginBottom,
								'IMAGE_ID' => $imageID++,
								'IMAGE_WIDTH' => $sizes[0],
								'IMAGE_HEIGHT' => $sizes[1],
								'IMAGE_SRC' => generateUrl($image->gallery_image_thumbnail));
				}
			}
			$this->_ComaLate->SetReplacement('IMAGES', $images);
			$this->_ComaLate->SetReplacement('PAGE_TITLE', $title);
			$this->HTML = '<h2>{LANG_GALLERY}: {PAGE_TITLE}</h2><div class="imagesblock">
					<IMAGES:loop>
					<div class="imageblock">
						<a href="index.php?page={PAGE_ID}&amp;imageID={IMAGE_ID}">
						<img style="margin-top:{IMAGE_MARGIN_TOP}px;margin-bottom:{IMAGE_MARGIN_BOTTOM}px;width:{IMAGE_WIDTH}px;height:{IMAGE_HEIGHT}px;" src="{IMAGE_SRC}" alt="{LANG_IMAGE}: {IMAGE_TITLE}" title="{IMAGE_TITLE}" /></a><br />
					</div>
					</IMAGES>
					</div>';
			return true;
		}
		
		
		
	}
 
?>
