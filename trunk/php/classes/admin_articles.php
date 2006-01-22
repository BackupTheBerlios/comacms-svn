<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: admin_articles.php				#
 # created		: 2005-10-07					#
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
	 class Admin_Articles {
	 	/**
	 	 * @access public
	 	 * @param string action
	 	 * @param array admin_lang
	 	 * @return string
	 	 */
	 	function GetPage($action, $admin_lang) {
	 		$out = "\t\t\t<h3>" . $admin_lang['articles'] . "</h3><hr />\r\n";
		 	$action = strtolower($action);
		 	switch ($action) {
		 		case 'new':		$out .= $this->_newArticle($admin_lang);
		 					break;
		 		case 'add':		$out .= $this->_addArticle($admin_lang);
		 					break;
		 		case 'edit':		$out .= $this->_editArticle($admin_lang);
		 					break;
		 		case 'save':		$out .= $this->_saveArticle($admin_lang);
		 					break;
		 		case 'delete':		$out .= $this->_deleteArticle($admin_lang);
		 					break;
		 		case 'setimage':	$out .= $this->_setImageArticle($admin_lang);
		 					break;
		 		case 'saveimage':	$out .= $this->_saveImageArticle($admin_lang);
		 					break;
		 		default:		$out .= $this->_homePage($admin_lang);
		 	}
	 		return $out;
	 	}
	 	
	 	function _saveImageArticle($admin_lang) {
	 		$file_path = GetPostOrGet('image_path');
	 		$article_id = GetPostOrGet('article_id');
	 		if(file_exists($file_path)) {
	 			$sql = "UPDATE ".DB_PREFIX."articles SET 
					article_image= '$file_path'
					WHERE article_id=$article_id";
				db_result($sql);
			}
			header('Location: admin.php?page=articles');
	 	}
	 	
	 	function _setImageArticle($admin_lang) {
	 		$article_id = GetPostOrGet('article_id');
	 		$sql = "SELECT *
				FROM " . DB_PREFIX . "articles
				WHERE article_id=$article_id";
			$article_result = db_result($sql);
			if($article = mysql_fetch_object($article_result)) {
	 			$out = '';
	 			$sql = "SELECT *
					FROM " . DB_PREFIX . "files
					WHERE file_type LIKE 'image/%'
					ORDER BY file_name ASC";
				$images_result = db_result($sql);
				$imgmax = 100;
				$imgmax2 = 300;
				$inlinemenu_folder = 'data/thumbnails/';
				$out .= "<form action=\"admin.php\" method=\"post\"><div class=\"imagesblock\">
				<input type=\"hidden\" name=\"page\" value=\"articles\"/>
				<input type=\"hidden\" name=\"action\" value=\"saveimage\"/>
				<input type=\"hidden\" name=\"article_id\" value=\"$article_id\"/>";
				while($image = mysql_fetch_object($images_result)) {
					$thumb = basename($image->file_path);
					preg_match("'^(.*)\.(gif|jpe?g|png|bmp)$'i", $thumb, $ext);
					if(strtolower($ext[2]) == 'gif')
						$thumb .= '.png';
					$orig_sizes = getimagesize($image->file_path);
					$succes = true;
					if(!file_exists($inlinemenu_folder . $imgmax . '_' . $thumb))
						$succes = generateThumb($image->file_path, $inlinemenu_folder . $imgmax . '_', $imgmax);
					if((file_exists($inlinemenu_folder . $imgmax . '_' . $thumb) || $succes) && $orig_sizes[0] >= $imgmax2) {
						$sizes = getimagesize($inlinemenu_folder . $imgmax . '_' . $thumb);
						$margin_top = round(($imgmax - $sizes[1]) / 2);
						$margin_bottom = $imgmax - $sizes[1] - $margin_top;
						$out .= "<div class=\"imageblock\">
					<a href=\"" . generateUrl($image->file_path) . "\">
					<img style=\"margin-top:" . $margin_top . "px;margin-bottom:" . $margin_bottom . "px;width:" . $sizes[0] . "px;height:" . $sizes[1] . "px;\" src=\"" . generateUrl($inlinemenu_folder . $imgmax . '_' .$thumb) . "\" alt=\"$thumb\" /></a><br />
					<input type=\"radio\" name=\"image_path\" " .(($article->article_image  == $image->file_path) ? 'checked="checked" ' : '') . " value=\"$image->file_path\"/></div>";
					}
				}
				$out .= "</div><input type=\"submit\" value=\"" . $admin_lang['apply'] . "\" class=\"button\"/><a href=\"admin.php?page=articles&amp;action=edit&amp;article_id=$article_id\" class=\"button\">" . $admin_lang['back'] . "</a></form>";
				return $out;
			}	
	 	}
	 	
	 	function _deleteArticle () {
	 		$id = GetPostOrGet('article_id');
	 		$sure = GetPostOrGet('sure');
			if($sure !== null) {
							
				if($sure == 1) {
					$sql = "DELETE FROM " . DB_PREFIX . "articles
						WHERE article_id=$id";
					db_result($sql);
				}
			}
			else {
				$sql = "SELECT *
					FROM " . DB_PREFIX . "articles
					WHERE article_id=$id";
				$article_result = db_result($sql);
				if($row = mysql_fetch_object($article_result)) {
					$out = "Den News Eintrag &quot;" . $row->article_title . "&quot; wirklich löschen?<br />
			<a href=\"admin.php?page=articles&amp;action=delete&amp;article_id=" . $id . "&amp;sure=1\" title=\"Wirklich Löschen\" class=\"button\">Ja</a>
			<a href=\"admin.php?page=articles\" title=\"Nicht Löschen\" class=\"button\">Nein</a>";
			
					return $out;
				}
			}
			header('Location: admin.php?page=articles');
		}
	 	
	 	/**
	 	 * @return string
	 	 * @var array admin_lang
	 	 */
	 	function _addArticle($admin_lang) {
	 		global $user;
	 		$title =  GetPostOrGet('article_title');
	 		$description =  GetPostOrGet('article_description');
	 		$text = GetPostOrGet('article_text');
	 		if(strlen($description) > 200)
	 			$description = substr($description, 0, 200);
	 		if($title !== null && $description !== null && $text !== null) {
				$sql = "INSERT INTO " . DB_PREFIX . "articles
					(article_title, article_description, article_text, article_html, article_creator, article_date)
					VALUES ('$title', '$description', '$text', '" . convertToPreHtml($text) . "', '$user->id', '" . mktime() . "')";
				db_result($sql);
			}
			if(GetPostOrGet('add_image') == 'add') {
				$id = mysql_insert_id();
				header("Location: admin.php?page=articles&action=setimage&article_id=$id");
			}
			else
				header('Location: admin.php?page=articles');	
	 	}
	 	
	 	/**
	 	 * @return string
	 	 * @var array admin_lang
	 	 */
	 	function _newArticle($admin_lang) {
	 		global $user;
	 		$out = "\t\t\t<script type=\"text/javascript\" language=\"JavaScript\" src=\"system/functions.js\"></script>
	 			<form method=\"post\" action=\"admin.php\">
				<input type=\"hidden\" name=\"page\" value=\"articles\" />
				<input type=\"hidden\" name=\"action\" value=\"add\" />
				<table class=\"input_table\">
					<tr>
						<td>Titel: <span class=\"info\">Hier den Titel des Artikels eingeben</span></td>
						<td class=\"article_input\"><input type=\"text\" name=\"article_title\" maxlength=\"100\" value=\"\" class=\"article_input\" /></td>
					</tr>
					<tr>
						<td>Beschreibung: <span class=\"info\">Hier eine Zusammenfassung oder Vorschau eingeben.(maximal 200 Zeichen)</span></td>
						<td><textarea rows=\"4\" cols=\"60\" name=\"article_description\" class=\"article_input\"></textarea></td>
					</tr>
					<tr>
						<td>Text: <span class=\"info\">Hier den gesammten Text des Artikels eingeben.</span></td>
						<td>
							<script type=\"text/javascript\" language=\"javascript\">
								writeButton(\"img/button_fett.png\",\"Formatiert Text Fett\",\"**\",\"**\",\"Fetter Text\",\"f\");
								writeButton(\"img/button_kursiv.png\",\"Formatiert Text kursiv\",\"//\",\"//\",\"Kursiver Text\",\"k\");
								writeButton(\"img/button_unterstrichen.png\",\"Unterstreicht den Text\",\"__\",\"__\",\"Unterstrichener Text\",\"u\");
								writeButton(\"img/button_ueberschrift.png\",\"Markiert den Text als Überschrift\",\"=== \",\" ===\",\"?berschrift\",\"h\");
							</script>
							<textarea id=\"editor\" cols=\"60\" rows=\"6\" name=\"article_text\" class=\"article_input\"></textarea></td>
					</tr>
					<tr>
						<td>Bild: <span class=\"info\">Das ist ein kleines Bild das zu dem Arikel angezeigt wird.</span></td>
						<td><input type=\"checkbox\" name=\"add_image\" id=\"add_image_checkbox\" checked=\"checked\"/ value=\"add\"><label for=\"add_image_checkbox\">Nach dem Eintragen des Arikels noch ein Bild auswählen<br/>
							(ein Bild kann auch später noch über die Bearbeiten-Funktion hinzugefügt/verändert werden)</label></td>
					</tr>
					<tr>
						<td>Eingelogt als $user->Showname</td><td><input type=\"submit\" class=\"button\" value=\"Eintragen\" /><input type=\"reset\" class=\"button\" value=\"Leeren\" /></td>
					</tr>
				</table>
				<br /> 
			</form>\r\n";
	 		return $out;
	 	}
	 	
	 	/**
	 	 * @return string
	 	 * @var array admin_lang
	 	 */
	 	function _editArticle($admin_lang) {
	 		global $user, $config;
	 		$id = GetPostOrGet('article_id');
	 		$sql = "SELECT *
	 			FROM " . DB_PREFIX . "articles
	 			WHERE article_id=$id";
	 		$article_result = db_result($sql);
	 		if($article = mysql_fetch_object($article_result)) {
	 			$thumbnailfoler = $config->Get('thumbnailfolder', 'data/thumbnails/');
	 			$imgmax = 100; 
	 			$out = "\t\t\t<script type=\"text/javascript\" language=\"JavaScript\" src=\"system/functions.js\"></script>
	 			<form method=\"post\" action=\"admin.php\">
				<input type=\"hidden\" name=\"page\" value=\"articles\" />
				<input type=\"hidden\" name=\"action\" value=\"save\" />
				<input type=\"hidden\" name=\"article_id\" value=\"$id\" />
				<table class=\"input_table\">
					<tr class=\"input_labels\">
						<td>Titel: <span class=\"info\">Hier den Titel des Artikels eingeben</span></td>
						<td><input type=\"text\" name=\"article_title\" maxlength=\"100\" value=\"$article->article_title\" class=\"article_input\" /></td>
					</tr>
					<tr>
						<td>Beschreibung: <span class=\"info\">Hier eine Zusammenfassung oder Vorschau eingeben.(maximal 200 Zeichen)</span></td>
						<td><textarea name=\"article_description\" rows=\"4\" cols=\"60\" class=\"article_input\">$article->article_description</textarea></td>
					</tr>
					<tr>
						<td>Text: <span class=\"info\">Hier den gesammten Text des Artikels eingeben.</span></td>
						<td>
							<script type=\"text/javascript\" language=\"javascript\">
								writeButton(\"img/button_fett.png\",\"Formatiert Text Fett\",\"**\",\"**\",\"Fetter Text\",\"f\");
								writeButton(\"img/button_kursiv.png\",\"Formatiert Text kursiv\",\"//\",\"//\",\"Kursiver Text\",\"k\");
								writeButton(\"img/button_unterstrichen.png\",\"Unterstreicht den Text\",\"__\",\"__\",\"Unterstrichener Text\",\"u\");
								writeButton(\"img/button_ueberschrift.png\",\"Markiert den Text als Überschrift\",\"=== \",\" ===\",\"Überschrift\",\"h\");
							</script>
							<textarea id=\"editor\" cols=\"60\" rows=\"6\" name=\"article_text\" class=\"article_input\">$article->article_text</textarea></td>
					</tr>
					<tr>
						<td>Bild: <span class=\"info\">Das ist ein kleines Bild das zu dem Arikel angezeigt wird.</span></td>
						<td>" . ((file_exists($thumbnailfoler . $imgmax . '_' . basename($article->article_image))) ? "<img style=\"float:left\" src=\"". generateUrl($thumbnailfoler . $imgmax . '_' . basename($article->article_image)) . "\"/>" : '<b>noch kein Bild festgelegt</b><br />') . "Wenn das Bild gesetzt oder verändert wird, gehen alle ungespeicherten Veränderungen an den Texten verloren!<br /><a class=\"button\" href=\"admin.php?page=articles&amp;action=setimage&amp;article_id=$id\">Bild setzen/verändern</a></td>
					</tr>
					<tr>
						<td>&nbsp;</td><td><input type=\"submit\" class=\"button\" value=\"" . $admin_lang['save'] . "\" /><input type=\"reset\" class=\"button\" value=\"" . $admin_lang['reset'] . "\" /></td>
					</tr>
				</table>
				<br />
			</form>\r\n";
	 			return $out;
	 		}
	 		header('Location: admin.php?page=articles');
	 	}
	 	
	 	/**
	 	 * @return string
	 	 * @var array admin_lang
	 	 */
	 	function _saveArticle($admin_lang) {
	 		$id = GetPostOrGet('article_id');
	 		$title =  GetPostOrGet('article_title');
	 		$description =  GetPostOrGet('article_description');
	 		$text = GetPostOrGet('article_text');
	 		if(strlen($description) > 200)
	 			$description = substr($description, 0, 200);
	 		if($title !== null && $description !== null && $text !== null && is_numeric($id)) {
				$sql = "UPDATE ".DB_PREFIX."articles SET 
					article_title= '$title', 
					article_description= '$description', 
					article_text= '$text',
					article_html= '" . convertToPreHtml($text) . "'
					WHERE article_id=$id";
				db_result($sql);
			}
			header('Location: admin.php?page=articles');
	 	}
	 	
	 	/**
	 	 * @return string
	 	 * @var array admin_lang
	 	 */
	 	function _homePage($admin_lang) {
	 		global $config;
	 		
	 		$thumbnailfoler = $config->Get('thumbnailfolder', 'data/thumbnails/');
	 		$imgmax = 100; 
	 		$out = "<a href=\"admin.php?page=articles&amp;action=new\" class=\"button\">Neuen Artikel schreiben</a><br /> 
	 			<table class=\"articles\">
					<thead>
						<tr>
							<td>" . $admin_lang['date'] . "</td>
							<td>Titel</td>
							<td>Bild</td>
							<td>Beschreibung</td>
							<td>" . $admin_lang['creator'] . "</td>
							<td>" . $admin_lang['actions'] . "</td>
						</tr>
					</thead>\r\n";
			$sql = "SELECT *
				FROM " . DB_PREFIX . "articles
				ORDER BY article_date DESC";
			$articles_result = db_result($sql);
			while($article = mysql_fetch_object($articles_result)) {
				$out .= "<tr>
						<td>" . date('d.m.Y H:i', $article->article_date) . "</td>
						<td>$article->article_title</td>
						<td class=\"article_image\">" . ((file_exists($thumbnailfoler . $imgmax . '_' . basename($article->article_image))) ? "<img src=\"". generateUrl($thumbnailfoler . $imgmax . '_' . basename($article->article_image)) . "\"/>" : '') . "</td>
						<td class=\"articles\">" . nl2br($article->article_description) . "</td>
						<td>" . getUserByID($article->article_creator) . "</td>
						<td>
							<a href=\"article.php?id=$article->article_id\" title=\"Anschauen\"><img src=\"./img/view.png\" alt=\"Anschauen\" title=\"Anschauen\" /></a> 
							<!--<img scr=\"./img/info.png\"  alt=\"Infos\" title=\"Infos\" />-->
							<a href=\"admin.php?page=articles&amp;action=edit&amp;article_id=$article->article_id\" title=\"Bearbeiten\"><img src=\"./img/edit.png\" alt=\"Bearbeiten\" title=\"Bearbeiten\" /></a>
							<a href=\"admin.php?page=articles&amp;action=delete&amp;article_id=$article->article_id\" title=\"L?schen\"><img src=\"./img/del.png\" alt=\"L?schen\" title=\"L?schen\" /></a>
						</td>
					</tr>";
			}
			$out .= "</table>";
			return $out;
	 	}
	 }
?>
