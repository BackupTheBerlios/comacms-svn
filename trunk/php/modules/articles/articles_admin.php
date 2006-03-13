<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : article_admin.php
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
 	require_once('classes/admin/admin_module.php');
 	require_once('modules/articles/articles.class.php');
 	
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
 		function Admin_Module_Articles(&$SqlConnection, &$User, &$Lang, &$Config, &$ComaLate, &$ComaLib) {
 			$this->_SqlConnection = &$SqlConnection;
 			$this->_User = &$User;	
 			$this->_Lang = &$Lang;
 			$this->_Config = &$Config;
 			$this->_ComaLate = &$ComaLate;
 			$this->_ComaLib = &$ComaLib;
 		}
 		
 		/**
 		 * @access public
 		 * @param string Action 
 		 * @return string
 		 */
 		function GetPage($Action = '') {
 			$out = '';
 			switch ($Action) {
 				case 'new':	$out .= $this->_newPage();
 						break;
 				default:	$out .= $this->_homePage();
 						break;
 			}
 			return $out;
 		}
 		
 		/**
	 	 * @return string
	 	 * @var array admin_lang
	 	 */
	 	function _newArticle() {
	 		global $user;
	 		$out = "\t\t\t<script type=\"text/javascript\" language=\"JavaScript\" src=\"system/functions.js\"></script>
	 			<form method=\"post\" action=\"admin.php\">
				<input type=\"hidden\" name=\"page\" value=\"articles\" />
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
								writeButton(\"img/button_ueberschrift.png\",\"Markiert den Text als �berschrift\",\"=== \",\" ===\",\"?berschrift\",\"h\");
							</script>
							<textarea id=\"editor\" cols=\"60\" rows=\"6\" name=\"article_text\" class=\"article_input\"></textarea>
					</div>
					<div class=\"row\">
						<label>Bild: <span class=\"info\">Das ist ein kleines Bild das zu dem Arikel angezeigt wird.</span></label>
						<input type=\"checkbox\" name=\"add_image\" id=\"add_image_checkbox\" checked=\"checked\" value=\"add\"/><label class=\"simple\" for=\"add_image_checkbox\">Nach dem Eintragen des Arikels noch ein Bild ausw&auml;hlen<br/>
							(ein Bild kann auch sp&auml;ter noch &uuml;ber die Bearbeiten-Funktion hinzugef&uuml;gt/ver&auml;ndert werden)</label>
					</div>
					<div class=\"row\">
						Eingelogt als $user->Showname
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
		 * @return string
		 */
		function _homePage() {	
	 		$thumbnailfoler = $this->_Config->Get('thumbnailfolder', 'data/thumbnails/');
	 		$imgmax = 100; 
	 		$out = "<a href=\"admin.php?page=articles&amp;action=new\" class=\"button\">{$this->_Lang['write_new_article']}</a><br /> 
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
				$out .= "<tr>
						<td class=\"table_date_width\">" . date('d.m.Y H:i', $article->article_date) . "</td>
						<td>$article->article_title</td>
						<td class=\"article_image\">" . ((file_exists($thumbnailfoler . $imgmax . '_' . basename($article->article_image))) ? "<img alt=\"A Logo\" src=\"". generateUrl($thumbnailfoler . $imgmax . '_' . basename($article->article_image)) . "\"/>" : '') . "</td>
						<td class=\"articles\">" . nl2br($article->article_description) . "</td>
						<td>" . $this->_ComaLib->GetUserByID($article->article_creator) . "</td>
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