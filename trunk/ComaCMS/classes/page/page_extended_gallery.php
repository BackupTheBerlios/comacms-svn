<?php
/**
 * @package ComaCMS
 * @subpackage Page
 * @copyright (C) 2005-2007 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : page_extended_gallery.php
 # created              : 2007-01-03
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
	require_once __ROOT__ . '/classes/page/page_extended.php';
	require_once __ROOT__ . '/classes/imageconverter.php';
	
 	/**
 	 * @package ComaCMS
	 * @subpackage Page
 	 */
	class Page_Extended_Gallery extends Page_Extended {
 		
 		function NewPage($PageID) {
 			if(!is_numeric($PageID))
 				return false;
			$sql = "INSERT INTO " . DB_PREFIX . "pages_gallery (page_id)
				VALUES ($PageID)";
			$this->_SqlConnection->SqlQuery($sql);		
 		}
 		
 		function UpdateTitle($PageID, $PageTitle) {
			if(!is_numeric($PageID))
 				return false;
			$sql = "UPDATE " . DB_PREFIX . "pages
					SET page_title='$PageTitle'
					WHERE page_id='$PageID'
					LIMIT 1";
			$this->_SqlConnection->SqlQuery($sql);
		}
 		
 		function GetEditPage($PageID) {
 			$action = GetPostOrGet('action2');
			$out = '';
			switch ($action) {
				
		 		case 'saveImage':
		 			$out .= $this->_EditPageSaveImage($PageID);
					break;
				case 'editImage':
					$out .= $this->_EditPageImage($PageID);
					break;
				case 'saveTitle': 
					$out .= $this->_EditPageSaveTitle($PageID);
					break;
				case 'addNewImage':
					$out .= $this->_EditPageAddNew($PageID);
					break;
				case 'addNewImageDialog':
					$out .= $this->_EditPageNewImage($PageID);
					break;
				case 'moveImageUp' :
					$out .= $this->_EditPageMoveUp($PageID);
					break;
				case 'moveImageDown' :
					$out .= $this->_EditPageMoveDown($PageID);
					break;
				case 'removeImage':
					$out .= $this->_EditPageRemove($PageID);
					break;
				case 'regenerateThumbnails':
					$out .= $this->_EditPageRegenerateThumbnails($PageID);
					break;
				default:
					$out .= $this->_EditPageOverview($PageID);
					break;
			}
 			return $out;
 		}
 		
 		function _EditPageRegenerateThumbnails($PageID) {
 				$sql = "SELECT page.*, gallery.* 
					FROM (" . DB_PREFIX . "pages page 
					LEFT JOIN " . DB_PREFIX . "pages_gallery gallery ON page.page_id = gallery.page_id)
					WHERE page.page_id=$PageID AND page.page_type='gallery' 
					LIMIT 1";

			$pageResult = $this->_SqlConnection->SqlQuery($sql);
			$pageData = mysql_fetch_object($pageResult);

			
			$sql = "SELECT gallery_file_id, gallery_image, gallery_image_thumbnail, gallery_file_id
					FROM " . DB_PREFIX . "gallery
					WHERE gallery_id = {$pageData->gallery_id}";
			$img_result = $this->_SqlConnection->SqlQuery($sql);
			$thumbnailSize = 100;
			$outputSize = 600;
			$thumbnailfolder = $this->_Config->Get('thumbnailfolder', 'data/thumbnails/');
			while($galleryImage = mysql_fetch_object($img_result)) {
				$imageResizer = new ImageConverter($galleryImage->gallery_image);
				$sizes = $imageResizer->CalcSizeByMax($thumbnailSize);
				$thumbnailFile = $galleryImage->gallery_image;
				if($sizes[0] < $imageResizer->Size[0] && $sizes[1] < $imageResizer->Size[1])
					$thumbnailFile = $imageResizer->SaveResizedTo($sizes[0], $sizes[1], $thumbnailfolder, $sizes[0] . 'x' . $sizes[1] . '_', true);
				if($thumbnailFile != $galleryImage->gallery_image_thumbnail) {
					$sql = "UPDATE " . DB_PREFIX . "gallery
							SET gallery_image_thumbnail = '{$thumbnailFile}'
							WHERE gallery_id={$pageData->gallery_id} AND gallery_file_id={$galleryImage->gallery_file_id}
							LIMIT 1";
					$this->_SqlConnection->SqlQuery($sql);
				}
				$sizes = $imageResizer->CalcSizeByMax($outputSize);
				if($sizes[0] < $imageResizer->Size[0] && $sizes[1] < $imageResizer->Size[1])
					$imageResizer->SaveResizedTo($sizes[0], $sizes[1], $thumbnailfolder, $sizes[0] . 'x' . $sizes[1] . '_', true);
			}
			return $this->_EditPageOverview($PageID);	
 		}
 		
 		function _EditPageSaveImage($PageID) {
 			if(!is_numeric($PageID))
				return $this->_EditPageOverview($PageID);
			$sql = "SELECT gallery.gallery_id
				FROM (" . DB_PREFIX . "pages page
				LEFT JOIN " . DB_PREFIX . "pages_gallery gallery ON page.page_id = gallery.page_id)
				WHERE page.page_id={$PageID} AND page.page_type='gallery'
				LIMIT 1";
			$pageResult = $this->_SqlConnection->SqlQuery($sql);	
			if($pageData = mysql_fetch_object($pageResult)) {
				$galleryID = $pageData->gallery_id;
				$imageID = GetPostOrGet('imageID');
				$imageDescription = GetPostOrGet('imageDescription');
				$sql = "UPDATE " . DB_PREFIX . "gallery
					SET `gallery_description` = '{$imageDescription}'
					WHERE gallery_id={$galleryID} AND gallery_file_id={$imageID}
					LIMIT 1";
				$this->_SqlConnection->SqlQuery($sql);
			}
			
 			return $this->_EditPageOverview($PageID);
 		}
 		
 		function _EditPageImage($PageID) {
			if(!is_numeric($PageID))
				return $this->_EditPageOverview($PageID); 
			$imageID = GetPostOrGet('imageID');
			
			$sql = "SELECT gallery.gallery_id
				FROM (" . DB_PREFIX . "pages page
				LEFT JOIN " . DB_PREFIX . "pages_gallery gallery ON page.page_id = gallery.page_id)
				WHERE page.page_id={$PageID} AND page.page_type='gallery'
				LIMIT 1";
			$pageResult = $this->_SqlConnection->SqlQuery($sql);	
			if($pageData = mysql_fetch_object($pageResult)) {
				$galleryID = $pageData->gallery_id;
				$sql = "SELECT *
		 				FROM " . DB_PREFIX . "gallery
		 				WHERE gallery_id={$galleryID} AND gallery_file_id={$imageID}
		 				LIMIT 1";
		 		$imageDataResult = $this->_SqlConnection->SqlQuery($sql);
		 		if($imageData = mysql_fetch_object($imageDataResult)) {
		 			$thumbnailfolder = $this->_Config->Get('thumbnailfolder', 'data/thumbnails/');
					
						
						$imageResizer = new ImageConverter($imageData->gallery_image);
						$sizes = $imageResizer->CalcSizeByMax(400);
						$prefix = $sizes[0] . 'x' . $sizes[1] . '_';
						$imageUrl = $imageResizer->SaveResizedTo($sizes[0], $sizes[1], $thumbnailfolder, $prefix);
						$this->_ComaLate->SetReplacement('LANG_BACK', $this->_Translation->GetTranslation('back'));
						$this->_ComaLate->SetReplacement('LANG_APPLY', $this->_Translation->GetTranslation('apply'));
						$this->_ComaLate->SetReplacement('LANG_IMAGE', $this->_Translation->GetTranslation('image'));
						$this->_ComaLate->SetReplacement('LANG_IMAGE_DESCRIPTION', $this->_Translation->GetTranslation('image_description'));
						$this->_ComaLate->SetReplacement('LANG_IMAGE_DESCRIPTION_INFO', $this->_Translation->GetTranslation('this_text_describes_what_you_can_see_on_the_picture'));
						$this->_ComaLate->SetReplacement('LANG_MODIFY_IMAGE_DESCRIPTION', $this->_Translation->GetTranslation('modyfy_image_description'));
						$this->_ComaLate->SetReplacement('IMAGE_DESCRIPTION', $imageData->gallery_description);
						$this->_ComaLate->SetReplacement('PAGE_ID', $PageID);
						$this->_ComaLate->SetReplacement('IMAGE_ID', $imageID);
						$this->_ComaLate->SetReplacement('IMAGE_SRC', generateUrl($imageUrl));
						$template = '<fieldset> 
							<legend>{LANG_MODIFY_IMAGE_DESCRIPTION}</legend>
							<form action="{ADMIN_FORM_URL}" method="post">
								<input type="hidden" name="pageID" value="{PAGE_ID}" />
								<input type="hidden" name="{ADMIN_FORM_PAGE}" value="pagestructure" />
								<input type="hidden" name="action" value="editPage" />
								<input type="hidden" name="action2" value="saveImage" />
								<input type="hidden" name="imageID" value="{IMAGE_ID}" />					
								<div class="imagebox">
									<img alt="{LANG_IMAGE}: {IMAGE_DESCRIPTION}" title="{IMAGE_DESCRIPTION}" src="{IMAGE_SRC}" />
								</div>
								<div class="row">
									<label>
										<strong>{LANG_IMAGE_DESCRIPTION}:</strong>
										<span class="info">{LANG_IMAGE_DESCRIPTION_INFO}</span>
									</label>
									<input type="text" name="imageDescription" value="{IMAGE_DESCRIPTION}" />
								</div>
								<div class="row">
									<input class="button" type="submit" value="{LANG_APPLY}" />
									<a class="button" href="{ADMIN_LINK_URL}page=pagestructure&amp;action=editPage&amp;pageID={PAGE_ID}">{LANG_BACK}</a>
								</div>
							</form>
						</fieldset>';
					return $template;
		 		}
			}	
			return $this->_EditPageOverview($PageID); 
 		}
 		
 		function _EditPageSaveTitle($PageID) {
 			$title = GetPostOrGet('pageTitle');
 			$this->UpdateTitle($PageID, $title);
 			return $this->_EditPageOverview($PageID);
 		}
 		
 		function _EditPageRemove($PageID) {
 			global $translation;
					
			
			$imageID = GetPostOrGet('imageID');
			$sure = GetPostOrGet('sure');
			$sql = "SELECT gallery.gallery_id
					FROM (" . DB_PREFIX . "pages page
					LEFT JOIN " . DB_PREFIX . "pages_gallery gallery ON page.page_id = gallery.page_id)
					WHERE page.page_id=$PageID AND page.page_type='gallery'
					LIMIT 1";
			$pageResult = $this->_SqlConnection->SqlQuery($sql);
			$pageData = mysql_fetch_object($pageResult);
			$galleryID = $pageData->gallery_id;
			
			if($sure == 1) {
				$sql = "DELETE FROM " . DB_PREFIX . "gallery
						WHERE gallery_id=$galleryID AND gallery_file_id=$imageID";
				$this->_SqlConnection->SqlQuery($sql);
				return $this->_EditPageOverview($PageID);
			}
			$sql = "SELECT *
					FROM " . DB_PREFIX . "gallery
					WHERE gallery_id=$galleryID AND gallery_file_id=$imageID";
			$imageResult = $this->_SqlConnection->SqlQuery($sql);
			$image = mysql_fetch_object($imageResult);
			//M&ouml;chten sie das Bild wirklich aus der Galerie entfernen?
			$this->_ComaLate->SetReplacement('LANG_IMAGE', $this->_Translation->GetTranslation('image'));
			$this->_ComaLate->SetReplacement('LANG_YES', $this->_Translation->GetTranslation('yes'));
			$this->_ComaLate->SetReplacement('LANG_NO', $this->_Translation->GetTranslation('no'));
			$this->_ComaLate->SetReplacement('LANG_BACK', $this->_Translation->GetTranslation('back'));
			$this->_ComaLate->SetReplacement('LANG_DELETE_QUESTION', $this->_Translation->GetTranslation('do_you_really_want_to_remove_this_image_from_the_gallery'));
			$this->_ComaLate->SetReplacement('LANG_REMOVE_IMAGE', $this->_Translation->GetTranslation('remove_image'));
			$this->_ComaLate->SetReplacement('IMAGE_SRC', generateUrl($image->gallery_image_thumbnail));
			$this->_ComaLate->SetReplacement('PAGE_ID', $PageID);
			$this->_ComaLate->SetReplacement('IMAGE_ID', $imageID);
			$template = '<fieldset>
					<legend>{LANG_REMOVE_IMAGE}</legend>
					<img src="{IMAGE_SRC}" alt="{LANG_IMAGE}: {IMAGE_TITLE}" title="{IMAGE_TITLE}" />
				{LANG_DELETE_QUESTION}<br />
				<a href="{ADMIN_LINK_URL}page=pagestructure&amp;action=editPage&amp;pageID={PAGE_ID}&amp;action2=removeImage&amp;imageID={IMAGE_ID}&amp;sure=1" class="button">{LANG_YES}</a>
		 		<a href="{ADMIN_LINK_URL}page=pagestructure&amp;action=editPage&amp;pageID={PAGE_ID}" class="button">{LANG_NO}</a></fieldset>';
			
			return $template;			
 		}
 		
 		function _EditPageAddNew($PageID) {
 			$images = GetPostOrGet('images');
		 			 	
		 	$sql = "SELECT page.*, gallery.*
					FROM (" . DB_PREFIX . "pages page 
					LEFT JOIN " . DB_PREFIX . "pages_gallery gallery ON page.page_id = gallery.page_id)
					WHERE page.page_id=$PageID AND page.page_type='gallery'
					LIMIT 1";

			$pageResult = $this->_SqlConnection->SqlQuery($sql);
			$pageData = mysql_fetch_object($pageResult);
		 	$sql = "SELECT *
		 			FROM " . DB_PREFIX ."gallery
		 			WHERE gallery_id=$pageData->gallery_id
		 			ORDER BY gallery_orderid DESC
		 			LIMIT 1";
		 	$orderidResult = $this->_SqlConnection->SqlQuery($sql);
		 	$orderid = 0;
		 	if($orderidRes = mysql_fetch_object($orderidResult))
		 		$orderid = $orderidRes->gallery_orderid + 1;
			$thumbnailFolder = $this->_Config->Get('thumbnailfolder', 'data/thumbnails/');
			$imgmax = 100;
			$outputSize = 600; 
			if(count($images) > 0) {
				$usedIDs = array();
			
				$sql = "SELECT gallery_file_id
						FROM " . DB_PREFIX . "gallery
						WHERE gallery_id = {$pageData->gallery_id}";
				$img_result = $this->_SqlConnection->SqlQuery($sql);
				while($galleryImage = mysql_fetch_object($img_result)) {
					$usedIDs[] = $galleryImage->gallery_file_id;
				}	
			
		 		foreach($images as $id) {
					if(!in_array($id, $usedIDs)) {
						$sql = "SELECT file_path, file_id
								FROM " . DB_PREFIX . "files
								WHERE file_id=$id";
						$image_result = $this->_SqlConnection->SqlQuery($sql);
					
						if($image = mysql_fetch_object($image_result)) {
							
							$imageResizer = new ImageConverter($image->file_path);
							$sizes = $imageResizer->CalcSizeByMax($imgmax);
				
							$fileName = $imageResizer->SaveResizedTo($sizes[0], $sizes[1], $thumbnailFolder, $sizes[0] . 'x' . $sizes[1] . '_');
							if(file_exists($fileName)) {
								$sql = "INSERT INTO " . DB_PREFIX . "gallery (gallery_id, gallery_file_id, gallery_image_thumbnail, gallery_image, gallery_orderid)
										VALUES($pageData->gallery_id, $image->file_id,'$fileName','$image->file_path', $orderid)";
								$this->_SqlConnection->SqlQuery($sql);
								$sizes = $imageResizer->CalcSizeByMax($outputSize);
								if($sizes[0] < $imageResizer->Size[0] && $sizes[1] < $imageResizer->Size[1])
									$imageResizer->SaveResizedTo($sizes[0], $sizes[1], $thumbnailFolder, $sizes[0] . 'x' . $sizes[1] . '_');
							}
							
						}
						$orderid++;
					}
				}
			}
			return $this->_EditPageOverview($PageID);
 		}
 		
		function _EditPageNewImage($PageID) {
			
			
			$sql = "SELECT page.*, gallery.* 
					FROM (" . DB_PREFIX . "pages page 
					LEFT JOIN " . DB_PREFIX . "pages_gallery gallery ON page.page_id = gallery.page_id)
					WHERE page.page_id=$PageID AND page.page_type='gallery' 
					LIMIT 1";

			$pageResult = $this->_SqlConnection->SqlQuery($sql);
			$pageData = mysql_fetch_object($pageResult);
			$usedIDs = array();
			
			$sql = "SELECT gallery_file_id
					FROM " . DB_PREFIX . "gallery
					WHERE gallery_id = {$pageData->gallery_id}";
			$img_result = $this->_SqlConnection->SqlQuery($sql);
			while($galleryImage = mysql_fetch_object($img_result)) {
				$usedIDs[] = $galleryImage->gallery_file_id;
			}	
			
			$sql = "SELECT file_path, file_id, file_name
		 			FROM " . DB_PREFIX . "files
		 			WHERE file_type LIKE 'image/%'
		 			ORDER BY file_name ASC";
			$imagesResult = $this->_SqlConnection->SqlQuery($sql);
	 		$imgmax = 100;
	 		$thumbnailfolder = $this->_Config->Get('thumbnailfolder', 'data/thumbnails/');
	 		$first = false;
	 		$images = array();
			while($image = mysql_fetch_object($imagesResult)) {
				if(!$first)
					$first = true;
				$imageResizer = new ImageConverter($image->file_path);
				$sizes = $imageResizer->CalcSizeByMax($imgmax);
				$marginTop = round(($imgmax - $sizes[1]) / 2);
					$marginBottom = $imgmax - $sizes[1] - $marginTop;
				$gif = '';
				if(substr($image->file_path, -4) == '.gif')
					$gif = '.png';
				$prefix = $sizes[0] . 'x' . $sizes[1] . '_';
				$fileName = $imageUrl = $thumbnailfolder . '/' .  $prefix . basename($image->file_path . $gif);
				
				if(!file_exists($fileName))
					$fileName = $imageResizer->SaveResizedTo($sizes[0], $sizes[1], $thumbnailfolder, $prefix);
				if(file_exists($fileName) && !in_array($image->file_id, $usedIDs))
					$images[] = array('IMAGE_FILE_ID' => $image->file_id ,
									'IMAGE_FILENAME' => $image->file_name,
									'IMAGE_SRC' => generateUrl($fileName),
									'IMAGE_MARGIN_TOP' => $marginTop,
									'IMAGE_MARGIN_BOTTOM' => $marginBottom,
									'IMAGE_WIDTH' => $sizes[0],
									'IMAGE_HEIGHT' => $sizes[1]);
				 
			}
			if(!$first)
				return $this->_EditPageOverview($PageID);
				
			$this->_ComaLate->SetReplacement('LANG_ADD_IMAGES', $this->_Translation->GetTranslation('add_images'));
			$this->_ComaLate->SetReplacement('LANG_RESET', $this->_Translation->GetTranslation('reset'));
			$this->_ComaLate->SetReplacement('LANG_IMAGE', $this->_Translation->GetTranslation('image'));
			$this->_ComaLate->SetReplacement('LANG_SELECT', $this->_Translation->GetTranslation('select'));
			$this->_ComaLate->SetReplacement('LANG_ADD', $this->_Translation->GetTranslation('add'));
			$this->_ComaLate->SetReplacement('LANG_BACK', $this->_Translation->GetTranslation('back'));
			$this->_ComaLate->SetReplacement('PAGE_ID', $PageID);
			$this->_ComaLate->SetReplacement('IMAGES', $images);
			$template = '<fieldset>
 					<legend>{LANG_ADD_IMAGES}</legend>
 					<form action="{ADMIN_FORM_URL}" method="post">
						<input type="hidden" name="{ADMIN_FORM_PAGE}" value="pagestructure" />
						<input type="hidden" name="action" value="editPage" />
						<input type="hidden" name="action2" value="addNewImage" />
						<input type="hidden" name="pageID" value="{PAGE_ID}" />
						<div class="row">
 					
 					<IMAGES:loop>
					<div class="imageblock">
						<!--<a href="index.php?page={PAGE_ID}&amp;imageID={IMAGE_ID}">-->
							<img style="margin-top:{IMAGE_MARGIN_TOP}px;margin-bottom:{IMAGE_MARGIN_BOTTOM}px;width:{IMAGE_WIDTH}px;height:{IMAGE_HEIGHT}px;" src="{IMAGE_SRC}" alt="{LANG_IMAGE}: {IMAGE_FILENAME}" title="{IMAGE_FILENAME}" />
						<!--</a>-->
						<div class="actions">
						<input type="checkbox" name="images[]" id="image{IMAGE_FILE_ID}" value="{IMAGE_FILE_ID}"/><label class="input" for="image{IMAGE_FILE_ID}" >{LANG_SELECT}</label>
						</div>
					</div>
					</IMAGES>
					</div>
 						<div class="row">
							<input type="reset" value="{LANG_RESET}" class="button" />
							<input type="submit" value="{LANG_ADD}" class="button" />
							<a class="button" href="{ADMIN_LINK_URL}page=pagestructure&amp;action=editPage&amp;pageID={PAGE_ID}">{LANG_BACK}</a>
						</div>
					</form>
				</fieldset>';
			return $template; 
		}
		
		function _EditPageMoveDown($PageID) {
			$imageID = GetPostOrGet('imageID');
			
			$sql = "SELECT gallery.gallery_id
					FROM (" . DB_PREFIX . "pages page
					LEFT JOIN " . DB_PREFIX . "pages_gallery gallery ON page.page_id = gallery.page_id)
					WHERE page.page_id=$PageID AND page.page_type='gallery'
					LIMIT 1";
			$pageResult = $this->_SqlConnection->SqlQuery($sql);
			$pageData = mysql_fetch_object($pageResult);
			$galleryID = $pageData->gallery_id;
		 	
		 	$sql = "SELECT *
		 			FROM " . DB_PREFIX . "gallery
		 			WHERE gallery_id=$galleryID AND gallery_file_id=$imageID";
		 	$firstImageResult = $this->_SqlConnection->SqlQuery($sql);
		 	$firstImage = mysql_fetch_object($firstImageResult);
		 	$firstID = $firstImage->gallery_file_id;
		 	$firstOrderid = $firstImage->gallery_orderid;
		 	
		 	$sql = "SELECT *
		 			FROM " . DB_PREFIX . "gallery
		 			WHERE gallery_id=$galleryID AND gallery_orderid > $firstOrderid
		 			ORDER BY gallery_orderid ASC";
		 	$secondImageResult = $this->_SqlConnection->SqlQuery($sql);
		 	if($secondImage = mysql_fetch_object($secondImageResult)) {
		 		$secondID = $secondImage->gallery_file_id;
		 		$secondOrderid = $secondImage->gallery_orderid;
		 		
		 		$sql = "UPDATE " . DB_PREFIX . "gallery
		 				SET gallery_orderid=$secondOrderid 
		 				WHERE gallery_id=$galleryID AND gallery_file_id=$firstID";
		 		$this->_SqlConnection->SqlQuery($sql);
		 		
		 		$sql = "UPDATE " . DB_PREFIX . "gallery
		 				SET gallery_orderid=$firstOrderid 
		 				WHERE gallery_id=$galleryID AND gallery_file_id=$secondID";
		 		$this->_SqlConnection->SqlQuery($sql);
		 	}
		 	return $this->_EditPageOverview($PageID);
		}
		
 		function _EditPageMoveUp($PageID) {
 			$imageID = GetPostOrGet('imageID');
			$sql = "SELECT gallery.gallery_id
					FROM (" . DB_PREFIX . "pages page
					LEFT JOIN " . DB_PREFIX . "pages_gallery gallery ON page.page_id = gallery.page_id)
					WHERE page.page_id=$PageID AND page.page_type='gallery'
					LIMIT 1";
			$pageResult = $this->_SqlConnection->SqlQuery($sql);
			$pageData = mysql_fetch_object($pageResult);
			$galleryID = $pageData->gallery_id;
		 	
		 	$sql = "SELECT *
		 			FROM " . DB_PREFIX . "gallery
		 			WHERE gallery_id=$galleryID AND gallery_file_id=$imageID";
		 	$firstImageResult = $this->_SqlConnection->SqlQuery($sql);
		 	$firstImage = mysql_fetch_object($firstImageResult);
		 	$firstID = $firstImage->gallery_file_id;
		 	$firstOrderid = $firstImage->gallery_orderid;
		 	
		 	$sql = "SELECT *
		 			FROM " . DB_PREFIX . "gallery
		 			WHERE gallery_id=$galleryID AND gallery_orderid < $firstOrderid
		 			ORDER BY gallery_orderid DESC";
		 	$secondImageResult = $this->_SqlConnection->SqlQuery($sql);
		 	if($secondImage = mysql_fetch_object($secondImageResult)) {
		 		$secondID = $secondImage->gallery_file_id;
		 		$secondOrderid = $secondImage->gallery_orderid;
		 		
		 		$sql = "UPDATE " . DB_PREFIX . "gallery
		 				SET gallery_orderid=$secondOrderid 
		 				WHERE gallery_id=$galleryID AND gallery_file_id=$firstID";
		 		$this->_SqlConnection->SqlQuery($sql);
		 		
		 		$sql = "UPDATE " . DB_PREFIX . "gallery
		 				SET gallery_orderid=$firstOrderid 
		 				WHERE gallery_id=$galleryID AND gallery_file_id=$secondID";
		 		$this->_SqlConnection->SqlQuery($sql);
		 	}
		 	return $this->_EditPageOverview($PageID);
 		}
 		
 		function _EditPageOverview($PageID) {
 			  			 
 			  if(!is_numeric($PageID))
				return false;
			
			$this->_ComaLate->SetReplacement('LANG_TITLE', $this->_Translation->GetTranslation('title'));
			$this->_ComaLate->SetReplacement('LANG_EDIT_GALLERY', $this->_Translation->GetTranslation('edit_gallery'));
			$this->_ComaLate->SetReplacement('LANG_TITLE_INFO', $this->_Translation->GetTranslation('the_title_is_someting_like_a_headline_of_the_page'));
			$this->_ComaLate->SetReplacement('LANG_APPLY', $this->_Translation->GetTranslation('apply'));
			$this->_ComaLate->SetReplacement('LANG_BACK', $this->_Translation->GetTranslation('back'));
			$this->_ComaLate->SetReplacement('LANG_REGENERATE_THUMBNAILS', $this->_Translation->GetTranslation('regenerate_thumbnails'));
			$this->_ComaLate->SetReplacement('LANG_ADD_IMAGES', $this->_Translation->GetTranslation('add_images'));
			$this->_ComaLate->SetReplacement('LANG_GALLERY', $this->_Translation->GetTranslation('gallery'));
			$this->_ComaLate->SetReplacement('LANG_IMAGE', $this->_Translation->GetTranslation('image'));
			$this->_ComaLate->SetReplacement('PAGE_ID', $PageID);
			$this->_ComaLate->SetReplacement('LANG_MOVE_UP', $this->_Translation->GetTranslation('move_up'));
			$this->_ComaLate->SetReplacement('LANG_MOVE_DOWN', $this->_Translation->GetTranslation('move_down'));
			$this->_ComaLate->SetReplacement('LANG_EDIT', $this->_Translation->GetTranslation('edit'));
			$this->_ComaLate->SetReplacement('LANG_DELETE', $this->_Translation->GetTranslation('delete'));
			// Load the images of the gallery with file-information
			$sql = 'SELECT gallery.gallery_file_id, gallery.gallery_image, gallery.gallery_description, gallery.gallery_image_thumbnail, page.page_title
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
			$thumbnailfolder = $this->_Config->Get('thumbnailfolder', 'data/thumbnails/');
			
			while($image = mysql_fetch_object($imagesResult)) {
				if($title == '')
					$title = $image->page_title;
				$imageResizer = new ImageConverter($image->gallery_image);
				$sizes = $imageResizer->CalcSizeByMax($imgmax);
				$gif = '';
				if(substr($image->gallery_image, -4) == '.gif')
					$gif = '.png';
				$prefix = $sizes[0] . 'x' . $sizes[1] . '_';
				$fileName = $imageUrl = $thumbnailfolder . '/' .  $prefix . basename($image->gallery_image . $gif);
				if(!file_exists($fileName))
					$fileName = $imageResizer->SaveResizedTo($sizes[0], $sizes[1], $thumbnailfolder, $prefix);
					$marginTop = round(($imgmax - $sizes[1]) / 2);
					$marginBottom = $imgmax - $sizes[1] - $marginTop;
					if(file_exists($fileName))
						$images[] = array('IMAGE_TITLE' => $image->gallery_description,
								'IMAGE_MARGIN_TOP' => $marginTop,
								'IMAGE_MARGIN_BOTTOM' => $marginBottom,
								'IMAGE_ID' => $imageID++,
								'IMAGE_WIDTH' => $sizes[0],
								'IMAGE_HEIGHT' => $sizes[1],
								'IMAGE_SRC' => generateUrl($fileName),
								'IMAGE_FILE_ID' => $image->gallery_file_id);

			}
			$this->_ComaLate->SetReplacement('IMAGES', $images);
			$this->_ComaLate->SetReplacement('PAGE_TITLE', $title);
 			$template = '<fieldset>
 					<legend>{LANG_EDIT_GALLERY}</legend>
 					<form action="{ADMIN_FORM_URL}" method="post">
						<input type="hidden" name="{ADMIN_FORM_PAGE}" value="pagestructure" />
						<input type="hidden" name="action" value="editPage" />
						<input type="hidden" name="action2" value="saveTitle" />
						<input type="hidden" name="pageID" value="{PAGE_ID}" />
						<div class="row">
 							<label class="row" for="pageTitle">
 								<strong>{LANG_TITLE}:</strong>
 								<span class="info">{LANG_TITLE_INFO}</span>
 							</label>
 							<input type="text" name="pageTitle" id="pageTitle" value="{PAGE_TITLE}" />
 						</div>
 						<div class="row">
							<input type="submit" value="{LANG_APPLY}" class="button" />
						</div>
					</form>
					<div class="row">
						<a href="{ADMIN_LINK_URL}page=pagestructure&amp;action=editPage&amp;action2=addNewImageDialog&amp;pageID={PAGE_ID}" class="button">{LANG_ADD_IMAGES}</a>
						<a href="{ADMIN_LINK_URL}page=pagestructure&amp;action=editPage&amp;action2=regenerateThumbnails&amp;pageID={PAGE_ID}" class="button">{LANG_REGENERATE_THUMBNAILS}</a>
						<a href="{ADMIN_LINK_URL}page=pagestructure" class="button">{LANG_BACK}</a>
					</div>
					<div class="row">
 					
 					<IMAGES:loop>
					<div class="imageblock">
						<a href="index.php?page={PAGE_ID}&amp;imageID={IMAGE_ID}">
							<img style="margin-top:{IMAGE_MARGIN_TOP}px;margin-bottom:{IMAGE_MARGIN_BOTTOM}px;width:{IMAGE_WIDTH}px;height:{IMAGE_HEIGHT}px;" src="{IMAGE_SRC}" alt="{LANG_IMAGE}: {IMAGE_TITLE}" title="{IMAGE_TITLE}" />
						</a>
						<div class="actions">
						<a href="{ADMIN_LINK_URL}page=pagestructure&amp;action=editPage&amp;pageID={PAGE_ID}&amp;action2=moveImageUp&amp;imageID={IMAGE_FILE_ID}"><img src="./img/up.png" alt="{LANG_MOVE_UP}" title="{LANG_MOVE_UP}"/></a>
						<a href="{ADMIN_LINK_URL}page=pagestructure&amp;action=editPage&amp;pageID={PAGE_ID}&amp;action2=editImage&amp;imageID={IMAGE_FILE_ID}"><img src="./img/edit.png" alt="{LANG_EDIT}" title="{LANG_EDIT}"/></a>
						<a href="{ADMIN_LINK_URL}page=pagestructure&amp;action=editPage&amp;pageID={PAGE_ID}&amp;action2=removeImage&amp;imageID={IMAGE_FILE_ID}"><img src="./img/del.png" alt="{LANG_DELETE}" title="{LANG_DELETE}"/></a>
						<a href="{ADMIN_LINK_URL}page=pagestructure&amp;action=editPage&amp;pageID={PAGE_ID}&amp;action2=moveImageDown&amp;imageID={IMAGE_FILE_ID}"><img src="./img/down.png" alt="{LANG_MOVE_DOWN}" title="{LANG_MOVE_DOWN}"/></a>
						</div>
					</div>
					</IMAGES>
					</div>
 					
 					</fieldset>';
 			return $template;
 		}
 	}
?>