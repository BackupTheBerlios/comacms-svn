<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: edit_galleryPage.php				#
 # created		: 2005-09-12					#
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
	
		function Save($page_id) {
			global $user;
			$page_title = GetPostOrGet('page_title');
			
			$sql = "UPDATE " . DB_PREFIX . "pages " .
				"SET page_creator=$user->ID, page_date=" . mktime() . ", page_title='$page_title' " .
				"WHERE page_id=$page_id";
			db_result($sql);
		}
		
		function Edit($page_id) {
			$action2 = GetPostOrGet('action2');
			
			$out = '';
			//global $_SERVER;
			//return "editor";
			$action2 = strtolower($action2);
		 	switch ($action2) {
		 		case 'add_new_dialog':		$out .= $this->_editAddNewDialog($page_id);
		 						break;
		 		case 'add_new':			$out .= $this->_editAddNew($page_id);
		 						break;		
		 		default:			$out .= $this->_editOverView($page_id);
		 	}
		 	return $out;
		}
		
		/**
		 * @access private
		 * @return string 
		 */
		 function _editAddNew($page_id) {
		 	$images = GetPostOrGet('images');
		 	$out = '';
		 	
		 	$sql = "SELECT * FROM " . DB_PREFIX . "gallery ";
		 	$sql = "SELECT page.*, gallery.*
				FROM (" . DB_PREFIX . "pages page 
				LEFT JOIN " . DB_PREFIX . "pages_gallery gallery ON page.page_id = gallery.page_id)
				WHERE page.page_id=$page_id AND page.page_type='gallery'
				LIMIT 0,1";

			$page_result = db_result($sql);
			$page_data = mysql_fetch_object($page_result);
		 	
		 	foreach($images as $id) {
				$sql = "SELECT file_path, file_id
					FROM " . DB_PREFIX . "files
					WHERE file_id=$id";
				$image_result = db_result($sql);
				
				if($image = mysql_fetch_object($image_result)) {
					$thumb = str_replace('/upload/', '/thumbnails/', $image->file_path);
					preg_match("'^(.*)\.(gif|jpe?g|png|bmp)$'i", $thumb, $ext);
					if(strtolower($ext[2]) == 'gif')
						$thumb .= '.png';
					$succes = true;
					$imgmax = 100;
					if(!file_exists($thumb))
						$succes = generateThumb($image->file_path, $imgmax);
					if(file_exists($thumb) || $succes) {
						$out .=  "\r\n" . $image->file_path;
						$sql = "INSERT INTO " . DB_PREFIX . "gallery (gallery_id, gallery_file_id, gallery_image_thumbnail, gallery_image)
							VALUES($page_data->gallery_id, $image->file_id,'$thumb','$image->file_path')";
						db_result($sql);
					}
				}
			}
		 	return $out;
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
		 	$out = "Bilder auswählen\r\n" .
		 		"<form  action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"post\">" .
		 		"\t<input type=\"hidden\" name=\"page\" value=\"pagestructure\" />" .
				"\t<input type=\"hidden\" name=\"action\" value=\"edit\" />" .
				"\t<input type=\"hidden\" name=\"page_id\" value=\"$page_id\" />" .
				"\t<input type=\"hidden\" name=\"action2\" value=\"add_new\" />" .
		 		"\t<table>" .
		 		"\t\t<tr>" .
		 		"\t\t\t<td>" .
		 		"\t\t\t\tBilder:" .
		 		"\t\t\t\t<span class=\"info\">TODO</span>" .
		 		"\t\t\t</td>" .
		 		"\t\t\t<td>";
		 	/*$sql = "SELECT file.*, image.*
		 		FROM (" . DB_PREFIX . "files file
		 		LEFT JOIN " . DB_PREFIX . "gallery image
		 		ON file.file_id = image.gallery_file_id)
		 		WHERE file.file_type LIKE 'image/%'
		 		ORDER BY file.file_name ASC";*/
		 		
		 	$sql = "SELECT *
		 		FROM " . DB_PREFIX . "files
		 		WHERE file_type LIKE 'image/%'
		 		ORDER BY file_name ASC";
	 		$images_result = db_result($sql);
			while($image = mysql_fetch_object($images_result)) {
				$thumb = str_replace('/upload/', '/thumbnails/', $image->file_path);
				preg_match("'^(.*)\.(gif|jpe?g|png|bmp)$'i", $thumb, $ext);
				if(strtolower($ext[2]) == 'gif')
					$thumb .= '.png';
				$succes = true;
				$imgmax = 100;
				if(!file_exists($thumb))
					$succes = generateThumb($image->file_path, $imgmax);
				if((file_exists($thumb) || $succes )  && !in_array($image->file_id,$ids)) {
					$sizes = getimagesize($thumb);
					$margin_top = round(($imgmax - $sizes[1]) / 2);
					$margin_bottom = $imgmax - $sizes[1] - $margin_top;
					$out .= "\t\t\t\t<div class=\"imageblock\">" .
						"\t\t\t\t\t<a href=\"" . generateUrl($image->file_path) . "\">" .
						"\t\t\t\t\t<img style=\"margin-top:" . $margin_top . "px;margin-bottom:" . $margin_bottom . "px;width:" . $sizes[0] . "px;height:" . $sizes[1] . "px;\" src=\"" . generateUrl($thumb) . "\" alt=\"$thumb\" /></a><br />" .
						"\t\t\t\t\t<input type=\"checkbox\" name=\"images[]\" value=\"$image->file_id\"/>Auswählen" .
						"\t\t\t\t</div>";
					}
				}
			$out .= "</td>" .
				"</tr>" .
				"<tr>" .
				"<td colspan=\"2\">" .
				"<input class=\"button\" type=\"reset\" value=\"Auswahl rückgängig machen\" />&nbsp;" .
				"<input class=\"button\" type=\"Submit\" value=\"Zur Galerie hinzufügen\"/>" .
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
		function _editOverView($page_id) {
			$out = '';
			$sql = "SELECT page.*, gallery.* " .
				"FROM (" . DB_PREFIX . "pages page " .
				"LEFT JOIN " . DB_PREFIX . "pages_gallery gallery ON page.page_id = gallery.page_id)" .
				"WHERE page.page_id=$page_id AND page.page_type='gallery' " .
				"LIMIT 0,1";

			$page_result = db_result($sql);
			$page_data = mysql_fetch_object($page_result);
			$out .= "<form action=\"" . $_SERVER['PHP_SELF'] . "\">" .
				"<input type=\"hidden\" name=\"page\" value=\"pagestructure\" />" .
				"<input type=\"hidden\" name=\"action\" value=\"save\" />" .
				"<input type=\"hidden\" name=\"page_id\" value=\"$page_data->page_id\" />" .
				"<table>" .
				"<tr><td>Titel:</td><td><input name=\"page_title\" value=\"$page_data->page_title\"/></td></tr>" .
				"<tr><td></td></tr>" .
				"</table>" .
				"</form>" .
				"<a href=\"" . $_SERVER['PHP_SELF'] . "?page=pagestructure&amp;action=edit&amp;action2=add_new_dialog&amp;page_id=$page_id\">Bilder hinzufügen</a>";
				$sql = "SELECT * " .
					"FROM " . DB_PREFIX . "gallery " .
					"WHERE gallery_id=$page_data->gallery_id";
				$images = db_result($sql);
				$imgmax = 100;
				while($image = mysql_fetch_object($images)) {
					$sizes = getimagesize($image->gallery_image_thumbnail);
					$margin_top = round(($imgmax - $sizes[1]) / 2);
					$margin_bottom = $imgmax - $sizes[1] - $margin_top;
					$out .= "\t\t\t\t<div class=\"imageblock\">" .
						"\t\t\t\t\t<a href=\"" . generateUrl($image->gallery_image) . "\">" .
						"\t\t\t\t\t<img style=\"margin-top:" . $margin_top . "px;margin-bottom:" . $margin_bottom . "px;width:" . $sizes[0] . "px;height:" . $sizes[1] . "px;\" src=\"" . generateUrl($image->gallery_image_thumbnail) . "\" alt=\"$image->gallery_image_thumbnail\" /></a><br />" .
						"\t\t\t\t</div>";
					//$out .= "$image->gallery_image_thumbnail <br />";
				}
			return $out;
		}
	}

?>