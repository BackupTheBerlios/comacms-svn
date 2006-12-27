<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2006 The ComaCMS-Team
 * @subpackage Articles
 */
 #----------------------------------------------------------------------
 # file                 : articles_admin.php
 # created              : 2006-03-01
 # copyright            : (C) 2005-2006 The ComaCMS-Team
 # email                : comacms@williblau.de
 #----------------------------------------------------------------------
 # This program is free software; you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation; either version 2 of the License, or
 # (at your option) any later version.
 #----------------------------------------------------------------------
 	/**
 	 * @ignore
 	 */
 	require_once __ROOT__ . '/classes/admin/admin_module.php';
 	require_once __ROOT__ . 'modules/articles/articles.class.php';
 	require_once __ROOT__ . '/classes/imageconverter.php';
 	
 	/**
 	 * @package ComaCMS
 	 * @subpackage Articles
 	 */
 	class Admin_Module_Articles extends Admin_Module {
 		
 		/**
		 * @access public
 		 * @param Sql SqlConnection
 		 * @param User User
 		 * @param array Lang
 		 * @param Config Config
 		 * @param ComaLate ComaLate
 		 * @param ComaLib ComaLib
 		 */
 		/*function Admin_Module_Articles(&$SqlConnection, &$User, &$Lang, &$Config, &$ComaLate, &$ComaLib) {
 			$this->_SqlConnection = &$SqlConnection;
 			$this->_User = &$User;	
 			$this->_Lang = &$Lang;
 			$this->_Config = &$Config;
 			$this->_ComaLate = &$ComaLate;
 			$this->_ComaLib = &$ComaLib;
 			
 			
 		}*/
 		function _Init() {
 			$this->_Articles = new Articles(&$this->_SqlConnection, &$this->_ComaLib, &$this->_User, &$this->_Config);
 			
 		}
 		
 		/**
 		 * @access public
 		 * @param string Action 
 		 * @return string
 		 */
 		function GetPage($Action = '') {
 			$out = '';
 			switch (strToLower($Action)) {
 				case 'new':		$out .= $this->_newArticle();
 							break;
				case 'add':		$nextpage = $this->_Articles->_addArticle();
							if ($nextpage == "addImage") {
								$id = $this->_Articles->_getLastID();
								$out .= $this->_setImage($id);
							}
							else {
								$out .= $this->_homePage();
							}
							break;
 				case 'edit':		$out .= $this->_editArticle();
 							break;
 				case 'save':		$out .= $this->_Articles->_saveArticle();
 							$out .= $this->_homePage();
 							break;
 				case 'delete':		$sure = $this->_Articles->_deleteArticle();
							if($sure != '') {
								$out .= $sure;
							}
							else {
								$out .= $this->_homePage();
							}
							break;
				case 'setimage':
								$id = GetPostOrGet("article_id");
								if(is_numeric($id))
									$out .= $this->_setImage($id);
								else
									$out .= $this->_homePage();
							break;
				case 'saveimage':	$out .= $this->_Articles->_saveImage();
							$out .= $this->_homePage();
							break;
 				default:		$out .= $this->_homePage();
 							break;
 			}
 			return $out;
 		}
 		
 		/**
	 	 * @return string
	 	 * @var array admin_lang
	 	 */
	 	function _newArticle() {
	 		$out = "\t\t\t<script type=\"text/javascript\" language=\"JavaScript\" src=\"system/functions.js\"></script>
	 			<form method=\"post\" action=\"admin.php\">
				<input type=\"hidden\" name=\"page\" value=\"module_articles\" />
				<input type=\"hidden\" name=\"action\" value=\"add\" />
				<fieldset>
					<div class=\"row\">
						<label>Titel: <span class=\"info\">Hier den Titel des Artikels eingeben</span></label>
						<input type=\"text\" name=\"article_title\" maxlength=\"100\" value=\"\" class=\"article_input\" />
					</div>
					<div class=\"row\">
						<label>Beschreibung: <span class=\"info\">Hier eine Zusammenfassung oder Vorschau eingeben.(maximal 200 Zeichen)</span></label>
						<textarea rows=\"4\" cols=\"60\" name=\"article_description\" class=\"article_input\"></textarea>
					</div>
					<div class=\"row\">
						<label>Text: <span class=\"info\">Hier den gesammten Text des Artikels eingeben.</span></label>
						
							<script type=\"text/javascript\" language=\"javascript\">
								writeButton(\"img/button_fett.png\",\"Formatiert Text Fett\",\"**\",\"**\",\"Fetter Text\",\"f\");
								writeButton(\"img/button_kursiv.png\",\"Formatiert Text kursiv\",\"//\",\"//\",\"Kursiver Text\",\"k\");
								writeButton(\"img/button_unterstrichen.png\",\"Unterstreicht den Text\",\"__\",\"__\",\"Unterstrichener Text\",\"u\");
								writeButton(\"img/button_ueberschrift.png\",\"Markiert den Text als &Uuml;berschrift\",\"== \",\" ==\",\"?berschrift\",\"h\");
							</script>
							<textarea id=\"editor\" cols=\"60\" rows=\"6\" name=\"article_text\" class=\"article_input\"></textarea>
					</div>
					<div class=\"row\">
						<label>Bild: <span class=\"info\">Das ist ein kleines Bild das zu dem Arikel angezeigt wird.</span></label>
						<input type=\"checkbox\" name=\"add_image\" id=\"add_image_checkbox\" checked=\"checked\" value=\"add\"/><label class=\"simple\" for=\"add_image_checkbox\">Nach dem Eintragen des Arikels noch ein Bild ausw&auml;hlen<br/>
							(ein Bild kann auch sp&auml;ter noch &uuml;ber die Bearbeiten-Funktion hinzugef&uuml;gt/ver&auml;ndert werden)</label>
					</div>
					<div class=\"row\">
						Eingelogt als {$this->_User->Showname}
					</div>
					<div class=\"row\">
						<input type=\"submit\" class=\"button\" value=\"Eintragen\" /><input type=\"reset\" class=\"button\" value=\"Leeren\" />
					</div>
				</fieldset>
				<br /> 
			</form>\r\n";
	 		return $out;
	 	}
	 	
	 	/**
	 	 * @access private
	 	 * @return sring
	 	 */
	 	 
	 	function _editArticle() {
	 		$id = GetPostOrGet('article_id');
	 		$sql = "SELECT *
	 			FROM " . DB_PREFIX . "articles
	 			WHERE article_id=$id";
	 		$article_result = db_result($sql);
	 		if($article = mysql_fetch_object($article_result)) {
	 			$thumbnailfoler = $this->_Config->Get('thumbnailfolder', 'data/thumbnails/');
	 			$imgmax = 100; 
	 			$out = "\t\t\t<script type=\"text/javascript\" language=\"JavaScript\" src=\"system/functions.js\"></script>
	 			<form method=\"post\" action=\"admin.php\">
				<input type=\"hidden\" name=\"page\" value=\"module_articles\" />
				<input type=\"hidden\" name=\"action\" value=\"save\" />
				<input type=\"hidden\" name=\"article_id\" value=\"$id\" />
				<fieldset>
					<div class=\"row\">
						<label>Titel: <span class=\"info\">Hier den Titel des Artikels eingeben</span></label>
						<input type=\"text\" name=\"article_title\" maxlength=\"100\" value=\"$article->article_title\" class=\"article_input\" />
					</div>
					<div class=\"row\">
						<label>Beschreibung: <span class=\"info\">Hier eine Zusammenfassung oder Vorschau eingeben.(maximal 200 Zeichen)</span></label>
						<textarea name=\"article_description\" rows=\"4\" cols=\"60\" class=\"article_input\">$article->article_description</textarea>
					</div>
					<div class=\"row\">
						<label>Text: <span class=\"info\">Hier den gesammten Text des Artikels eingeben.</span></label>
						<script type=\"text/javascript\" language=\"javascript\">
							writeButton(\"img/button_fett.png\",\"Formatiert Text Fett\",\"**\",\"**\",\"Fetter Text\",\"f\");
							writeButton(\"img/button_kursiv.png\",\"Formatiert Text kursiv\",\"//\",\"//\",\"Kursiver Text\",\"k\");
							writeButton(\"img/button_unterstrichen.png\",\"Unterstreicht den Text\",\"__\",\"__\",\"Unterstrichener Text\",\"u\");
							writeButton(\"img/button_ueberschrift.png\",\"Markiert den Text als &Uuml;berschrift\",\"=== \",\" ===\",\"&Uuml;berschrift\",\"h\");
						</script>
						<textarea id=\"editor\" cols=\"60\" rows=\"6\" name=\"article_text\" class=\"article_input\">$article->article_text</textarea>
					</div>
					<div class=\"row\">
						<label>Bild: <span class=\"info\">Das ist ein kleines Bild das zu dem Arikel angezeigt wird.</span></label>
						" . ((file_exists($thumbnailfoler . $imgmax . '_' . basename($article->article_image))) ? "<img style=\"float:left\" src=\"". generateUrl($thumbnailfoler . $imgmax . '_' . basename($article->article_image)) . "\"/>" : '<b>noch kein Bild festgelegt</b><br />') . "Wenn das Bild gesetzt oder ver&auml;ndert wird, gehen alle ungespeicherten Ver&auml;nderungen an den Texten verloren!<br /><a class=\"button\" href=\"admin.php?page=module_articles&amp;action=setimage&amp;article_id=$id\">Bild setzen/ver&auml;ndern</a>
					</div>
					<div class=\"row\">
						<input type=\"submit\" class=\"button\" value=\"{$this->_Lang['save']}\" /><input type=\"reset\" class=\"button\" value=\"{$this->_Lang['reset']}\" />
					</div>
				</fieldset>
				<br />
			</form>\r\n";
	 			return $out;
	 		}
	 	}
	 	
 		/**
		 * @access private
		 * @return string
		 */
		function _homePage() {	
	 		$thumbnailfolder = $this->_Config->Get('thumbnailfolder', 'data/thumbnails/');
	 		$imgmax = 100;
	 		$out = "<h2>{$this->_Lang['articles_module']}</h2>"; 
	 		$out .= "<a href=\"admin.php?page=module_articles&amp;action=new\" class=\"button\">{$this->_Lang['write_new_article']}</a><br /> 
	 			<table class=\"text_table full_width\">
					<thead>
						<tr>
							<th class=\"table_date_width\">{$this->_Lang['date']}</th>
							<th>{$this->_Lang['title']}</th>
							<th>{$this->_Lang['picture']}</th>
							<th>{$this->_Lang['description']}</th>
							<th>{$this->_Lang['creator']}</th>
							<th class=\"actions\">{$this->_Lang['actions']}</th>
						</tr>
					</thead>\r\n";
			$sql = "SELECT *
				FROM " . DB_PREFIX . "articles
				ORDER BY article_date DESC";
			$articles_result = db_result($sql);
			while($article = mysql_fetch_object($articles_result)) {
				$image = new ImageConverter($article->article_image);
				$size = $image->CalcSizeByMax($imgmax);
				$resizedFileName = $thumbnailfolder . '/' . $size[0] . 'x' . $size[1] . '_' . basename($article->article_image);
				if(!file_exists($resizedFileName))
					$image->SaveResizedTo($size[0], $size[1],$thumbnailfolder, $size[0] . 'x' . $size[1] . '_');
				$out .= "<tr>
						<td class=\"table_date_width\">" . date('d.m.Y H:i', $article->article_date) . "</td>
						<td>$article->article_title</td>
						<td class=\"article_image\">" . ((file_exists($resizedFileName)) ? "<img alt=\"Image for $article->article_title\" src=\"". generateUrl($resizedFileName) . "\"/>" : '') . "</td>
						<td class=\"articles\">" . nl2br($article->article_description) . "</td>
						<td>" . $this->_ComaLib->GetUserByID($article->article_creator) . "</td>
						<td>
							<a href=\"special.php?page=module&amp;moduleName=articles&amp;action=show&amp;articleId=$article->article_id\" title=\"Anschauen\"><img src=\"./img/view.png\" alt=\"Anschauen\" title=\"Anschauen\" /></a> 
							<!--<img scr=\"./img/info.png\"  alt=\"Infos\" title=\"Infos\" />-->
							<a href=\"admin.php?page=module_articles&amp;action=edit&amp;article_id=$article->article_id\" title=\"Bearbeiten\"><img src=\"./img/edit.png\" alt=\"Bearbeiten\" title=\"Bearbeiten\" /></a>
							<a href=\"admin.php?page=module_articles&amp;action=delete&amp;article_id=$article->article_id\" title=\"L&ouml;schen\"><img src=\"./img/del.png\" alt=\"L&ouml;schen\" title=\"L&ouml;schen\" /></a>
						</td>
					</tr>";
			}
			$out .= "</table>";
			return $out;
	 	}
	 	
	 	/**
	 	 * @access private
	 	 * @return sring
	 	 */
	 	function _setImage($article_id) {
	 		if(!is_numeric($article_id))
	 			return $this->_homePage();
	 		$sql = "SELECT *
				FROM " . DB_PREFIX . "articles
				WHERE article_id=$article_id";
			$article_result = db_result($sql);
			if($article = mysql_fetch_object($article_result)) {
	 			$out = '';
	 			$sql = "SELECT file_path
					FROM " . DB_PREFIX . "files
					WHERE file_type LIKE 'image/%'
					ORDER BY file_name ASC";
				$images_result = db_result($sql);
				$imgmax = 100;
				$imgmax2 = 100;
				$thumbnailfolder = 'data/thumbnails/';
				$out .= "<form action=\"admin.php\" method=\"post\"><div class=\"imagesblock\">
				<input type=\"hidden\" name=\"page\" value=\"module_articles\"/>
				<input type=\"hidden\" name=\"action\" value=\"saveImage\"/>
				<input type=\"hidden\" name=\"article_id\" value=\"$article_id\"/>";
				while($imageData = mysql_fetch_object($images_result)) {
					$imageUrl = $imageData->file_path;
					if(file_exists($imageUrl)) {
						$image = new ImageConverter($imageUrl);
						$size = $image->CalcSizeByMax($imgmax);
						$resizedFileName = $thumbnailfolder . '/' . $size[0] . 'x' . $size[1] . '_' . basename($imageUrl); 
						if(!file_exists($resizedFileName))
							$resizedFileName = $image->SaveResizedTo($size[0], $size[1], $thumbnailfolder, $size[0] . 'x' . $size[1] . '_');
		
						if(file_exists($resizedFileName) && $resizedFileName !== false) {
							$margin_top = round(($imgmax - $size[1]) / 2);
							$margin_bottom = $imgmax - $size[1] - $margin_top;
							$out .= "<div class=\"imageblock\">
							<a href=\"" . generateUrl($resizedFileName) . "\">
							<img style=\"margin-top:" . $margin_top . "px;margin-bottom:" . $margin_bottom . "px;width:" . $size[0] . "px;height:" . $size[1] . "px;\" src=\"" . generateUrl($resizedFileName) . "\" alt=\"". basename($imageData->file_path) . "\" /></a><br />
						<input type=\"radio\" name=\"image_path\" " .(($article->article_image  == $imageData->file_path) ? 'checked="checked" ' : '') . " value=\"" . $imageData->file_path . "\"/></div>";
						}
						
					}
				}
				$out .= "</div><input type=\"submit\" value=\"{$this->_Lang['apply']}\" class=\"button\"/><a href=\"admin.php?page=module_articles&amp;action=edit&amp;article_id=$article_id\" class=\"button\">{$this->_Lang['back']}</a></form>";
				return $out;
			}
	 	}
	 	
	 	/**
	 	 * @access public
	 	 */
	 	 function GetTitle() {
	 	 	return $this->_Lang['articles_module'];
	 	 }
 	}
?>