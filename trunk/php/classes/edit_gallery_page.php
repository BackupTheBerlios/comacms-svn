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
			header("Location: admin.php?page=pagestructure&action=edit&page_id=$page_id");
		}
		
		function Edit($page_id) {
			$action2 = GetPostOrGet('action2');
			$out = '';
			$action2 = strtolower($action2);
		 	switch ($action2) {
		 		case 'add_new_dialog':		$out .= $this->_editAddNewDialog($page_id);
		 						break;
		 		case 'add_new':			$out .= $this->_editAddNew($page_id);
		 						break;
		 		case 'delete':			$out .= $this->_editRemoveImage($page_id);
		 						break;
		 		case 'up':			$out .= $this->_editUp($page_id);
		 						break;
		 		case 'down':			$out .= $this->_editDown($page_id);
		 						break;
		 		default:			$out .= $this->_editOverView($page_id);
		 	}
		 	return $out;
		}
		
		/**
		 * @access private
		 * @return string 
		 */
		 function _editUp($page_id) {
		 	$image_id = GetPostOrGet('image_id');
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
		 	header("Location: admin.php?page=pagestructure&action=edit&page_id=$page_id");
		}
		
		/**
		 * @access private
		 * @return string 
		 */
		 function _editDown($page_id) {
		 	$image_id = GetPostOrGet('image_id');
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
		 	header("Location: admin.php?page=pagestructure&action=edit&page_id=$page_id");
		 	
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
						//$out .=  "\r\n" . $image->file_path;
						$sql = "INSERT INTO " . DB_PREFIX . "gallery (gallery_id, gallery_file_id, gallery_image_thumbnail, gallery_image, gallery_orderid)
							VALUES($page_data->gallery_id, $image->file_id,'$thumb','$image->file_path', $orderid)";
						db_result($sql);
					}
				}
				$orderid++;
			}
			header("Location: admin.php?page=pagestructure&action=edit&page_id=$page_id");
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
		function _editRemoveImage($page_id) {
			global $admin_lang;
					
			$out = '';
			$image_id = GetPostOrGet('image_id');
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
				header("Location: admin.php?page=pagestructure&action=edit&page_id=$page_id");
			}
			else{
				$sql = "SELECT *
					FROM " . DB_PREFIX . "gallery
					WHERE gallery_id=$gallery_id AND gallery_file_id=$image_id";
				$image_result = db_result($sql);
				$image = mysql_fetch_object($image_result);
				$out .= "<img src=\"" . generateUrl($image->gallery_image_thumbnail) . "\" alt=\"$image->gallery_image_thumbnail\" />
					Möchten sie das Bild wirklich aus der Galerie entfernen?<br />
					<a href=\"admin.php?page=pagestructure&amp;action=edit&amp;page_id=$page_id&amp;action2=delete&amp;image_id=$image_id&amp;sure=1\" class=\"button\">" . $admin_lang['yes'] . "</a>
		 			<a href=\"admin.php?page=pagestructure&amp;action=edit&amp;page_id=$page_id\" class=\"button\">" . $admin_lang['no'] . "</a>
					";
			}
			return $out;			
		}
		
		/**
		 * @access private
		 * @return string 
		 */
		function _editOverView($page_id) {
			global $admin_lang;
			
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
				<input type=\"hidden\" name=\"action\" value=\"save\" />
				<input type=\"hidden\" name=\"page_id\" value=\"$page_data->page_id\" />
				<table>
				<tr><td>Titel:</td><td><input name=\"page_title\" value=\"$page_data->page_title\"/></td></tr>
				<tr><td colspan=\"2\"><input type=\"submit\" class=\"button\" value=\"" . $admin_lang['apply'] . "\"/><input type=\"reset\" class=\"button\" value=\"" . $admin_lang['reset'] . "\"/></td></tr>
				</table>
				</form>
				<a href=\"admin.php?page=pagestructure&amp;action=edit&amp;action2=add_new_dialog&amp;page_id=$page_id\" class=\"button\">Bilder hinzufügen</a>";
				$sql = "SELECT *
					FROM " . DB_PREFIX . "gallery
					WHERE gallery_id=$page_data->gallery_id
					ORDER BY gallery_orderid ASC";
				$images = db_result($sql);
				$imgmax = 100;
				while($image = mysql_fetch_object($images)) {
					$sizes = getimagesize($image->gallery_image_thumbnail);
					$margin_top = round(($imgmax - $sizes[1]) / 2);
					$margin_bottom = $imgmax - $sizes[1] - $margin_top;
					$out .= "\t\t\t\t<div class=\"imageblock\">
						\t\t\t\t\t<a href=\"" . generateUrl($image->gallery_image) . "\">
						\t\t\t\t\t<img style=\"margin-top:" . $margin_top . "px;margin-bottom:" . $margin_bottom . "px;width:" . $sizes[0] . "px;height:" . $sizes[1] . "px;\" src=\"" . generateUrl($image->gallery_image_thumbnail) . "\" alt=\"$image->gallery_image_thumbnail\" /></a><br />
						\t\t\t\t\t<a href=\"admin.php?page=pagestructure&amp;action=edit&amp;page_id=$page_id&amp;action2=up&amp;image_id=$image->gallery_file_id\"><img src=\"./img/up.jpg\" height=\"16\" width=\"16\" alt=\"" . $admin_lang['move_up'] . "\" title=\"" . $admin_lang['move_up'] . "\"/></a>
						\t\t\t\t\t<a href=\"admin.php?page=pagestructure&amp;action=edit&amp;page_id=$page_id&amp;action2=delete&amp;image_id=$image->gallery_file_id\"><img src=\"./img/del.jpg\" height=\"16\" width=\"16\" alt=\"" . $admin_lang['delete'] . "\" title=\"" . $admin_lang['delete'] . "\"/></a>
						\t\t\t\t\t<a href=\"admin.php?page=pagestructure&amp;action=edit&amp;page_id=$page_id&amp;action2=down&amp;image_id=$image->gallery_file_id\"><img src=\"./img/down.jpg\" height=\"16\" width=\"16\" alt=\"" . $admin_lang['move_down'] . "\" title=\"" . $admin_lang['move_down'] . "\"/></a>
						\t\t\t\t</div>";
					//$out .= "$image->gallery_image_thumbnail <br />";
				}
			return $out;
		}
	}
?>