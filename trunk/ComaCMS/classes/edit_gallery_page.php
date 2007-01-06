<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : edit_galleryPage.php
 # created              : 2005-09-12
 # copyright            : (C) 2005-2006 The ComaCMS-Team
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
	class Edit_Gallery_Page {
		
		/**
		 * @access public
		 * @return void
		 * @param page_id integer
		 */
		function NewPage($page_id) {
			$sql = "INSERT INTO " . DB_PREFIX . "pages_gallery (page_id)
				VALUES ($page_id)";
			db_result($sql);
		}
	
		function GetSavePage($page_id) {
			global $user;
			$page_title = GetPostOrGet('pageTitle');
			
			$sql = "UPDATE " . DB_PREFIX . "pages " .
				"SET page_creator=$user->ID, page_date=" . mktime() . ", page_title='$page_title' " .
				"WHERE page_id=$page_id";
			db_result($sql);
			header("Location: admin.php?page=pagestructure&action=editPage&pageID=$page_id");
		}
		
		function GetEditPage($pageID) {
			$action2 = GetPostOrGet('action2');
			$out = '';
			
		 	switch ($action2) {
		 		case 'addNewImageDialog':	$out .= $this->_editAddNewDialog($pageID);
		 						break;
		 		case 'addNewImage':		$out .= $this->_editAddNew($pageID);
		 						break;
		 		case 'removeImage':		$out .= $this->_editRemoveImage($pageID);
		 						break;
		 		case 'moveImageUp':		$out .= $this->_editUp($pageID);
		 						break;
		 		case 'moveImageDown':		$out .= $this->_editDown($pageID);
		 						break;
		 		case 'editImage':		$out .= $this->_editImage($pageID);
		 						break;
		 		case 'saveImage':		$out .= $this->_saveImage($pageID);
		 						break;
		 		default:			$out .= $this->_editOverView($pageID);
		 	}
		 	return $out;
		}
		
		/**
		 * @param integer PageID
		 * @access private
		 * @return string
		 */
		function _saveImage($PageID) {
			if(!is_numeric($PageID))
				return $this->_editOverView($PageID);
			$sql = "SELECT gallery.gallery_id
				FROM (" . DB_PREFIX . "pages page
				LEFT JOIN " . DB_PREFIX . "pages_gallery gallery ON page.page_id = gallery.page_id)
				WHERE page.page_id={$PageID} AND page.page_type='gallery'
				LIMIT 1";
			$pageResult = db_result($sql);	
			if($pageData = mysql_fetch_object($pageResult)) {
				$galleryID = $pageData->gallery_id;
				$imageID = GetPostOrGet('imageID');
				$imageDescription = GetPostOrGet('imageDescription');
				$sql = "UPDATE " . DB_PREFIX . "gallery
					SET `gallery_description` = '{$imageDescription}'
					WHERE gallery_id={$galleryID} AND gallery_file_id={$imageID}
					LIMIT 1";
				db_result($sql);
			}
			return $this->_editOverView($PageID);
		}
		
		/**
		 * @param integer PageID
		 * @access private
		 * @return string
		 */
		function _editImage($PageID) {
			global $config, $translation;
			if(!is_numeric($PageID))
				return $this->_editOverView($PageID); 
			$imageID = GetPostOrGet('imageID');
			
			$sql = "SELECT gallery.gallery_id
				FROM (" . DB_PREFIX . "pages page
				LEFT JOIN " . DB_PREFIX . "pages_gallery gallery ON page.page_id = gallery.page_id)
				WHERE page.page_id={$PageID} AND page.page_type='gallery'
				LIMIT 1";
			$pageResult = db_result($sql);	
			if($pageData = mysql_fetch_object($pageResult)) {
				$galleryID = $pageData->gallery_id;
				$sql = "SELECT *
		 			FROM " . DB_PREFIX . "gallery
		 			WHERE gallery_id={$galleryID} AND gallery_file_id={$imageID}
		 			LIMIT 1";
		 		$imageDataResult = db_result($sql);
		 		if($imageData = mysql_fetch_object($imageDataResult)) {
		 			$thumbnailfoler = $config->Get('thumbnailfolder', 'data/thumbnails/');
					$out = "\t\t\t\t<fieldset> 
							<legend>" . $translation->GetTranslation('modify_image_description') . "</legend>
							<form action=\"admin.php\" method=\"post\">
								<input type=\"hidden\" name=\"pageID\" value=\"{$PageID}\"/>
								<input type=\"hidden\" name=\"page\" value=\"pagestructure\"/>
								<input type=\"hidden\" name=\"action\" value=\"editPage\"/>
								<input type=\"hidden\" name=\"action2\" value=\"saveImage\"/>
								<input type=\"hidden\" name=\"imageID\" value=\"{$imageID}\"/>					
								<div class=\"imagebox\">
									<img alt=\"{$imageData->gallery_description}\" title=\"{$imageData->gallery_description}\" src=\"" . generateUrl(resizeImageToMaximum($imageData->gallery_image, $thumbnailfoler, 400)) . "\"/>
								</div>
								<div class=\"row\">
									<label>
										<strong>" . $translation->GetTranslation('image_description') . ":</strong>
										<span class=\"info\">" . $translation->GetTranslation('todo') . "</span>
									</label>
									<input type=\"text\" name=\"imageDescription\" value=\"{$imageData->gallery_description}\" />
								</div>
								<div class=\"row\">
									<a class=\"button\" href=\"admin.php?page=pagestructure&amp;action=editPage&amp;pageID={$PageID}\">" . $translation->GetTranslation('back') . "</a>
									<input class=\"button\" type=\"submit\" value=\"" . $translation->GetTranslation('apply') . "\" />
								</div>
							</form>
						</fieldset>";
					return $out;
		 		}
			}	
			return $this->_editOverView($PageID); 
		}
		
		/**
		 * @access private
		 * @return string 
		 */
		 function _editUp($page_id) {
		 	$image_id = GetPostOrGet('imageID');
			// TODO: why? check this function (also _editDown())??
			$sure = GetPostOrGet('sure');
			$sql = "SELECT gallery.gallery_id
				FROM (" . DB_PREFIX . "pages page
				LEFT JOIN " . DB_PREFIX . "pages_gallery gallery ON page.page_id = gallery.page_id)
				WHERE page.page_id=$page_id AND page.page_type='gallery'
				LIMIT 0,1";
			$page_result = db_result($sql);
			$page_data = mysql_fetch_object($page_result);
			$gallery_id = $page_data->gallery_id;
		 	$sql = "SELECT *
		 		FROM " . DB_PREFIX . "gallery
		 		WHERE gallery_id=$gallery_id AND gallery_file_id=$image_id";
		 	$first_image_result = db_result($sql);
		 	$first_image = mysql_fetch_object($first_image_result);
		 	$first_id = $first_image->gallery_file_id;
		 	$first_orderid = $first_image->gallery_orderid;
		 	
		 	$sql = "SELECT *
		 		FROM " . DB_PREFIX . "gallery
		 		WHERE gallery_id=$gallery_id AND gallery_orderid < $first_orderid
		 		ORDER BY gallery_orderid DESC";
		 	$second_image_result = db_result($sql);
		 	if($second_image = mysql_fetch_object($second_image_result)) {
		 		$second_id = $second_image->gallery_file_id;
		 		$second_orderid = $second_image->gallery_orderid;
		 		$sql = "UPDATE " . DB_PREFIX . "gallery
		 			SET gallery_orderid=$second_orderid 
		 			WHERE gallery_id=$gallery_id AND gallery_file_id=$first_id";
		 		db_result($sql);
		 		$sql = "UPDATE " . DB_PREFIX . "gallery
		 			SET gallery_orderid=$first_orderid 
		 			WHERE gallery_id=$gallery_id AND gallery_file_id=$second_id";
		 		db_result($sql);
		 	}	
		 	header("Location: admin.php?page=pagestructure&action=editPage&pageID=$page_id");
		}
		
		/**
		 * @access private
		 * @return string 
		 */
		 function _editDown($page_id) {
		 	$image_id = GetPostOrGet('imageID');
			$sure = GetPostOrGet('sure');
			$sql = "SELECT gallery.gallery_id
				FROM (" . DB_PREFIX . "pages page
				LEFT JOIN " . DB_PREFIX . "pages_gallery gallery ON page.page_id = gallery.page_id)
				WHERE page.page_id=$page_id AND page.page_type='gallery'
				LIMIT 0,1";
			$page_result = db_result($sql);
			$page_data = mysql_fetch_object($page_result);
			$gallery_id = $page_data->gallery_id;
		 	$sql = "SELECT *
		 		FROM " . DB_PREFIX . "gallery
		 		WHERE gallery_id=$gallery_id AND gallery_file_id=$image_id";
		 	$first_image_result = db_result($sql);
		 	$first_image = mysql_fetch_object($first_image_result);
		 	$first_id = $first_image->gallery_file_id;
		 	$first_orderid = $first_image->gallery_orderid;
		 	
		 	$sql = "SELECT *
		 		FROM " . DB_PREFIX . "gallery
		 		WHERE gallery_id=$gallery_id AND gallery_orderid > $first_orderid
		 		ORDER BY gallery_orderid ASC";
		 	$second_image_result = db_result($sql);
		 	if($second_image = mysql_fetch_object($second_image_result)) {
		 		$second_id = $second_image->gallery_file_id;
		 		$second_orderid = $second_image->gallery_orderid;
		 		$sql = "UPDATE " . DB_PREFIX . "gallery
		 			SET gallery_orderid=$second_orderid 
		 			WHERE gallery_id=$gallery_id AND gallery_file_id=$first_id";
		 		db_result($sql);
		 		$sql = "UPDATE " . DB_PREFIX . "gallery
		 			SET gallery_orderid=$first_orderid 
		 			WHERE gallery_id=$gallery_id AND gallery_file_id=$second_id";
		 		db_result($sql);
		 	}	
		 	header("Location: admin.php?page=pagestructure&action=editPage&pageID=$page_id");
		}
		 
		/**
		 * @access private
		 * @return string 
		 */
		 function _editAddNew($page_id) {
		 	$images = GetPostOrGet('images');
		 	$out = '';
		 	$sql = "SELECT page.*, gallery.*
				FROM (" . DB_PREFIX . "pages page 
				LEFT JOIN " . DB_PREFIX . "pages_gallery gallery ON page.page_id = gallery.page_id)
				WHERE page.page_id=$page_id AND page.page_type='gallery'
				LIMIT 0,1";

			$page_result = db_result($sql);
			$page_data = mysql_fetch_object($page_result);
		 	$sql = "SELECT *
		 		FROM " . DB_PREFIX ."gallery
		 		WHERE gallery_id=$page_data->gallery_id
		 		ORDER BY gallery_orderid DESC
		 		LIMIT 0,1";
		 	$orderid_result = db_result($sql);
		 	$orderid = 0;
		 	if($orderid_res = mysql_fetch_object($orderid_result))
		 		$orderid = $orderid_res->gallery_orderid + 1;
			$thumb_folder = 'data/thumbnails/';
			$imgmax = 100;
			if(count($images) > 0) {
		 		foreach($images as $id) {
					$sql = "SELECT file_path, file_id
						FROM " . DB_PREFIX . "files
						WHERE file_id=$id";
					$image_result = db_result($sql);
					
					if($image = mysql_fetch_object($image_result)) {
						$thumb = $thumb_folder . $imgmax . '_' . basename($image->file_path);
						preg_match("'^(.*)\.(gif|jpe?g|png|bmp)$'i", $thumb, $ext);
						if(strtolower($ext[2]) == 'gif')
							$thumb .= '.png';
						if(file_exists($thumb)) {
							$sql = "INSERT INTO " . DB_PREFIX . "gallery (gallery_id, gallery_file_id, gallery_image_thumbnail, gallery_image, gallery_orderid)
								VALUES($page_data->gallery_id, $image->file_id,'$thumb','$image->file_path', $orderid)";
							db_result($sql);
						}
					}
					$orderid++;
				}
			}
			header("Location: admin.php?page=pagestructure&action=editPage&pageID=$page_id");
		}
		
		/**
		 * @access private
		 * @return string 
		 */
		 function _editAddNewDialog($page_id) {
		 	$sql = "SELECT page.*, gallery.* " .
				"FROM (" . DB_PREFIX . "pages page " .
				"LEFT JOIN " . DB_PREFIX . "pages_gallery gallery ON page.page_id = gallery.page_id)" .
				"WHERE page.page_id=$page_id AND page.page_type='gallery' " .
				"LIMIT 0,1";

			$page_result = db_result($sql);
			$page_data = mysql_fetch_object($page_result);
			$ids = array();
			$sql = "SELECT gallery_file_id
				FROM " . DB_PREFIX . "gallery
				WHERE gallery_id = $page_data->gallery_id";
			$img_result = db_result($sql);
			while($img = mysql_fetch_object($img_result)) {
				$ids[] = $img->gallery_file_id;
			}	
		 	$out = "Bilder ausw&auml;hlen\r\n" .
		 		"<form  action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"post\">" .
		 		"\t<input type=\"hidden\" name=\"page\" value=\"pagestructure\" />" .
				"\t<input type=\"hidden\" name=\"action\" value=\"editPage\" />" .
				"\t<input type=\"hidden\" name=\"pageID\" value=\"$page_id\" />" .
				"\t<input type=\"hidden\" name=\"action2\" value=\"addNewImage\" />" .
		 		"\t<table>" .
		 		"\t\t<tr>" .
		 		"\t\t\t<td>" .
		 		"\t\t\t\tBilder:" .
		 		"\t\t\t\t<span class=\"info\">TODO</span>" .
		 		"\t\t\t</td>" .
		 		"\t\t\t<td>";
		 		
		 	$sql = "SELECT *
		 		FROM " . DB_PREFIX . "files
		 		WHERE file_type LIKE 'image/%'
		 		ORDER BY file_name ASC";
	 		$images_result = db_result($sql);
	 		$imgmax = 100;
	 		$thumb_folder = 'data/thumbnails/';
	 		$img_count = 0;
			while($image = mysql_fetch_object($images_result)) {
				$thumb = basename($image->file_path);
				preg_match("'^(.*)\.(gif|jpe?g|png|bmp)$'i", $thumb, $ext);
				if(strtolower($ext[2]) == 'gif')
					$thumb .= '.png';
				$succes = true;
				if(!file_exists($thumb_folder . $imgmax . '_' . $thumb))
					$succes = generateThumb($image->file_path, $thumb_folder . $imgmax . '_', $imgmax);
				if((file_exists($thumb_folder . $imgmax . '_' . $thumb) || $succes )  && !in_array($image->file_id, $ids)) {
					$img_count++;
					$sizes = getimagesize($thumb_folder . $imgmax . '_' . $thumb);
					$margin_top = round(($imgmax - $sizes[1]) / 2);
					$margin_bottom = $imgmax - $sizes[1] - $margin_top;
					$out .= "\t\t\t\t<div class=\"imageblock\">" .
						"\t\t\t\t\t<a href=\"" . generateUrl($image->file_path) . "\">" .
						"\t\t\t\t\t<img style=\"margin-top:" . $margin_top . "px;margin-bottom:" . $margin_bottom . "px;width:" . $sizes[0] . "px;height:" . $sizes[1] . "px;\" src=\"" . generateUrl($thumb_folder . $imgmax . '_' . $thumb) . "\" alt=\"" . $imgmax . "_$thumb\" /></a><br />" .
						"\t\t\t\t\t<input type=\"checkbox\" name=\"images[]\" value=\"$image->file_id\"/>Ausw&auml;hlen" .
						"\t\t\t\t</div>";
				}
			}
			if($img_count == 0)
				header("Location: admin.php?page=pagestructure&action=editPage&pageID=$page_id");
			$out .= "</td>" .
				"</tr>" .
				"<tr>" .
				"<td colspan=\"2\">" .
				"<input class=\"button\" type=\"reset\" value=\"Auswahl r&uuml;ckg&auml;ngig machen\" />&nbsp;" .
				"<input class=\"button\" type=\"Submit\" value=\"Zur Galerie hinzuf&uuml;gen\"/>" .
				"</td>" .
				"</tr>" .
				"</table>" .
				"</form>";
			return $out;
		}
		
		/**
		 * @access private
		 * @return string 
		 */
		function _editRemoveImage($page_id) {
			global $translation;
					
			$out = '';
			$image_id = GetPostOrGet('imageID');
			$sure = GetPostOrGet('sure');
			$sql = "SELECT gallery.gallery_id
				FROM (" . DB_PREFIX . "pages page
				LEFT JOIN " . DB_PREFIX . "pages_gallery gallery ON page.page_id = gallery.page_id)
				WHERE page.page_id=$page_id AND page.page_type='gallery'
				LIMIT 0,1";
			$page_result = db_result($sql);
			$page_data = mysql_fetch_object($page_result);
			$gallery_id = $page_data->gallery_id;
			
			if($sure == 1) {
				$sql = "DELETE FROM " . DB_PREFIX . "gallery
					WHERE gallery_id=$gallery_id AND gallery_file_id=$image_id";
				db_result($sql);
				header("Location: admin.php?page=pagestructure&action=editPage&pageID=$page_id");
			}
			else{
				$sql = "SELECT *
					FROM " . DB_PREFIX . "gallery
					WHERE gallery_id=$gallery_id AND gallery_file_id=$image_id";
				$image_result = db_result($sql);
				$image = mysql_fetch_object($image_result);
				$out .= "<img src=\"" . generateUrl($image->gallery_image_thumbnail) . "\" alt=\"$image->gallery_image_thumbnail\" />
					M&ouml;chten sie das Bild wirklich aus der Galerie entfernen?<br />
					<a href=\"admin.php?page=pagestructure&amp;action=editPage&amp;pageID=$page_id&amp;action2=removeImage&amp;imageID=$image_id&amp;sure=1\" class=\"button\">" . $translation->GetTranslation('yes') . "</a>
		 			<a href=\"admin.php?page=pagestructure&amp;action=editPage&amp;pageID=$page_id\" class=\"button\">" . $translation->GetTranslation('no') . "</a>
					";
			}
			return $out;			
		}
		
		/**
		 * @access private
		 * @return string 
		 */
		function _editOverView($page_id) {
			global $translation;
			
			$out = '';
			$sql = "SELECT page.*, gallery.*
				FROM (" . DB_PREFIX . "pages page
				LEFT JOIN " . DB_PREFIX . "pages_gallery gallery ON page.page_id = gallery.page_id)
				WHERE page.page_id=$page_id AND page.page_type='gallery'
				LIMIT 0,1";

			$page_result = db_result($sql);
			$page_data = mysql_fetch_object($page_result);
			$out .= "<form action=\"admin.php\">
				<input type=\"hidden\" name=\"page\" value=\"pagestructure\" />
				<input type=\"hidden\" name=\"action\" value=\"savePage\" />
				<input type=\"hidden\" name=\"pageID\" value=\"$page_data->page_id\" />
				<table>
				<tr><td>Titel:</td><td><input name=\"pageTitle\" value=\"$page_data->page_title\"/></td></tr>
				<tr><td colspan=\"2\"><input type=\"submit\" class=\"button\" value=\"" . $translation->GetTranslation('apply') . "\"/></td></tr>
				</table>
				</form>
				<a href=\"admin.php?page=pagestructure&amp;action=editPage&amp;action2=addNewImageDialog&amp;pageID=$page_id\" class=\"button\">Bilder hinzuf&uuml;gen</a>";
				$sql = "SELECT *
					FROM " . DB_PREFIX . "gallery
					WHERE gallery_id=$page_data->gallery_id
					ORDER BY gallery_orderid ASC";
				$images = db_result($sql);
				$imgmax = 100;
				while($image = mysql_fetch_object($images)) {
					if(!file_exists($image->gallery_image_thumbnail)) {
						$path = pathinfo($image->gallery_image_thumbnail);
						$imgmax1 = 100;
						$upload_path = 'data/upload/';
						if(file_exists($upload_path . substr($path['basename'], strlen($imgmax . '_'))))
							generateThumb($upload_path . substr($path['basename'], strlen($imgmax . '_')), $path['dirname']. '/' . $imgmax . '_', $imgmax);

					}
					if(file_exists($image->gallery_image_thumbnail)) {
						$sizes = getimagesize($image->gallery_image_thumbnail);
						$margin_top = round(($imgmax - $sizes[1]) / 2);
						$margin_bottom = $imgmax - $sizes[1] - $margin_top;
						$out .= "\t\t\t\t<div class=\"imageblock\">
						<a href=\"" . generateUrl($image->gallery_image) . "\">
						<img style=\"margin-top:{$margin_top}px;margin-bottom:{$margin_bottom}px;width:{$sizes[0]}px;height:{$sizes[1]}px;\" src=\"" . generateUrl($image->gallery_image_thumbnail) . "\" alt=\"{$image->gallery_description}\" title=\"{$image->gallery_description}\" /></a>
						
						<div class=\"actions\">
						<a href=\"admin.php?page=pagestructure&amp;action=editPage&amp;pageID=$page_id&amp;action2=moveImageUp&amp;imageID=$image->gallery_file_id\"><img src=\"./img/up.png\" height=\"16\" width=\"16\" alt=\"" . $translation->GetTranslation('move_up') . "\" title=\"" . $translation->GetTranslation('move_up') . "\"/></a>
						<a href=\"admin.php?page=pagestructure&amp;action=editPage&amp;pageID=$page_id&amp;action2=editImage&amp;imageID=$image->gallery_file_id\"><img src=\"./img/edit.png\" height=\"16\" width=\"16\" alt=\"" . $translation->GetTranslation('edit') . "\" title=\"" . $translation->GetTranslation('edit') . "\"/></a>
						<a href=\"admin.php?page=pagestructure&amp;action=editPage&amp;pageID=$page_id&amp;action2=removeImage&amp;imageID=$image->gallery_file_id\"><img src=\"./img/del.png\" height=\"16\" width=\"16\" alt=\"" . $translation->GetTranslation('delete') . "\" title=\"" . $translation->GetTranslation('delete') . "\"/></a>
						<a href=\"admin.php?page=pagestructure&amp;action=editPage&amp;pageID=$page_id&amp;action2=moveImageDown&amp;imageID=$image->gallery_file_id\"><img src=\"./img/down.png\" height=\"16\" width=\"16\" alt=\"" . $translation->GetTranslation('move_down') . "\" title=\"" . $translation->GetTranslation('move_down') . "\"/></a>
						</div></div>";
					}
				}
			return $out;
		}
	}
?>
