<?php
/**
 * @package ComaCMS
 * @subpackage News
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : news_admin.php
 # created              : 2006-02-18
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
	require_once __ROOT__ . '/modules/news/news.class.php';
	 	
	/**
	 * @package ComaCMS
	 * @subpackage News 
	 */
	class Admin_Module_News extends Admin_Module{
		
 		
 		/**
 		 * @access public
 		 * @param string Action 
 		 * @return string
 		 */
		function GetPage($Action = '') {
			$out = '';
			switch ($Action) {
				case 'add':
						$out .= $this->_AddPage(); 					
						break;
				case 'save':
						$out .= $this->_SavePage();					
						break;
				case 'delete':
						$out .= $this->_DeletePage();					
						break;
				case 'edit':
						$out .= $this->_EditPage();					
						break;
				case 'new':
						$out .= $this->_NewPage();					
						break;	
				default:
						$out .= $this->_HomePage();
						break;
			}
			return $out;
		}
		
		/**
		 * @access private
		 * @return string
		 */
		function _SavePage() {
			// get the transmitted vars
			$newsID = GetPostOrGet('newsID');
			$title = GetPostOrGet('newsTitle');
			$text = GetPostOrGet('newsText');
			// some content and an numeric $newsID?
			if(is_numeric($newsID) && $title != '' && $text != '') {
				$news = new News($this->_SqlConnection, $this->_ComaLib, $this->_User, $this->_Config);
				// Update it!
				$news->UpdateMessage($newsID, $title, $text);
			}
			// go back to the default-view
			return $this->_HomePage();
		}
		
		/**
		 * @access private
		 * @return string
		 */
		function _EditPage() {
			$newsID = GetPostOrGet('newsID');
			if(is_numeric($newsID)) {
				$news = new News($this->_SqlConnection, $this->_ComaLib, $this->_User, $this->_Config);
				$newsMessage = $news->GetMessage($newsID);
				if(count($newsMessage) > 0) {
					$out = "<h2>" . $this->_Translation->GetTranslation('edit_a_news_message') . "</h2>
			<form action=\"admin.php\" method=\"post\">
				<input type=\"hidden\" name=\"page\" value=\"module_news\" />
				<input type=\"hidden\" name=\"action\" value=\"save\" />
				<input type=\"hidden\" name=\"newsID\" value=\"$newsID\" />
			<fieldset>
			<legend>" . $this->_Translation->GetTranslation('news_message') . "</legend>
			<div class=\"row\">
				<label for=\"newsTitle\">" . $this->_Translation->GetTranslation('title') . "<span class=\"info\">" . $this->_Translation->GetTranslation('todo') . "</span></label>
				<input name=\"newsTitle\" id=\"newsTitle\" value=\"{$newsMessage['NEWS_TITLE']}\" type=\"text\"/>
			</div>
			<div class=\"row\">
				<label for=\"newsText\">" . $this->_Translation->GetTranslation('text') . "<span class=\"info\">" . $this->_Translation->GetTranslation('todo') . "</span></label>
				<textarea rows=\"5\" name=\"newsText\" id=\"newsText\">{$newsMessage['NEWS_TEXT']}</textarea>
			</div>
			<div class=\"row\">
				<input type=\"submit\" class=\"button\" value=\"" . $this->_Translation->GetTranslation('save') . "\"/>
				<a class=\"button\" href=\"admin.php?page=module_news\">" . $this->_Translation->GetTranslation('back') . "</a>
			</div>
			</fieldset>
			</form>
			";
			
					return $out;
				}
			}
			else
				return $this->_HomePage();
		}
		
		/**
		 * @access private
		 * @return string
		 */
		function _DeletePage() {
			$newsID = GetPostOrGet('newsID');
			if(is_numeric($newsID)) {
				$confirmation = GetPostOrGet('confirmation');
				$news = new News($this->_SqlConnection, $this->_ComaLib, $this->_User, $this->_Config);
				$newsMessage = $news->GetMessage($newsID);
				if(count($newsMessage) > 0) {
					if($confirmation == 1)
						$news->DeleteMessage($newsID);
					else {
						$out = "<h2>" . $this->_Translation->GetTranslation('delete_news_message') . "?</h2>
							<p>" . sprintf($this->_Translation->GetTranslation('do_you_really_want_to_delete_the_news_message_%news_title%_from_the_%date%?'), $newsMessage['NEWS_TITLE'], date('d.m.Y H:i:s', $newsMessage['NEWS_DATE'])) . "</p>
							<a href=\"admin.php?page=module_news&amp;action=delete&amp;newsID=$newsID&amp;confirmation=1\" class=\"button\">" . $this->_Translation->GetTranslation('yes') . "</a>
							<a href=\"admin.php?page=module_news\" class=\"button\">" . $this->_Translation->GetTranslation('no') . "</a>";
						return $out;
					}		
				}
			}
			return $this->_HomePage();
		}
		
		/**
		 * @access private
		 * @return string
		 */
		function _AddPage() {
			$news = new News($this->_SqlConnection, $this->_ComaLib, $this->_User, $this->_Config);
			$title = GetPostOrGet('newsTitle');
			$text = GetPostOrGet('newsText');
			$news->AddMessage($title, $text);
			return $this->_HomePage();
		}
		
		/**
		 * @access private
		 * @return string
		 */
		function _NewPage() {
			$out = "<h2>" . $this->_Translation->GetTranslation('write_a_new_news_message') . "</h2>
			<form action=\"admin.php\" method=\"post\">
				<input type=\"hidden\" name=\"page\" value=\"module_news\" />
				<input type=\"hidden\" name=\"action\" value=\"add\" />
			<fieldset>
			<legend>" . $this->_Translation->GetTranslation('new_news_message') . "</legend>
			<div class=\"row\">
				<label for=\"newsTitle\">" . $this->_Translation->GetTranslation('title') . "<span class=\"info\">" . $this->_Translation->GetTranslation('todo') . "</span></label>
				<input name=\"newsTitle\" id=\"newsTitle\" value=\"\" type=\"text\"/>
			</div>
			<div class=\"row\">
				<label for=\"newsText\">" . $this->_Translation->GetTranslation('text') . "<span class=\"info\">" . $this->_Translation->GetTranslation('todo') . "</span></label>
				<textarea rows=\"5\" name=\"newsText\" id=\"newsText\"></textarea>
			</div>
			<div class=\"row\">
				<input type=\"submit\" class=\"button\" value=\"" . $this->_Translation->GetTranslation('save') . "\"/>
				<a class=\"button\" href=\"admin.php?page=module_news\">" . $this->_Translation->GetTranslation('back') . "</a>
			</div>
			</fieldset>
			</form>
			";
			return $out;
		}
		
		/**
		 * @access private
		 * @return string
		 */
		function _HomePage() {
			$news = new News($this->_SqlConnection, $this->_ComaLib, $this->_User, $this->_Config);
			$newsArray = $news->FillArray(-1, false, true);
			$out = "<h2>" . $this->_Translation->GetTranslation('news_overview') . "</h2>
				<a class=\"button\" href=\"admin.php?page=module_news&amp;action=new\" title=\"" . $this->_Translation->GetTranslation('write_a_new_news_message') . "\">" . $this->_Translation->GetTranslation('write_a_new_news_message') . "</a>
				<table class=\"text_table full_width\">
				<thead>
					<tr>
						<th class=\"table_date_width\">Datum</th>
						<th class=\"small_width\">Titel</th>
						<th>Text</th>
						<th class=\"small_width\">Autor</th>
						<th class=\"actions\">Aktionen</th>
					</tr>
				</thead>
				<tbody>";
				foreach($newsArray as $newsEntrie) {
					$out .= "<tr>
							<td>" . date('d.m.Y H:i:s', $newsEntrie['NEWS_DATE']) . "</td>
							<td>{$newsEntrie['NEWS_TITLE']}</td>
							<td>{$newsEntrie['NEWS_TEXT']}</td>
							<td>{$newsEntrie['NEWS_AUTHOR']}</td>
							<td>
							<a href=\"admin.php?page=module_news&amp;action=edit&amp;newsID={$newsEntrie['NEWS_ID']}\" title=\"" . sprintf($this->_Translation->GetTranslation('edit_the_news_message_%news_title%_from_the_%date%'), $newsEntrie['NEWS_TITLE'], date('d.m.Y H:i:s', $newsEntrie['NEWS_DATE'])) . "\"><img alt=\"" . $this->_Translation->GetTranslation('edit') . "\" src=\"./img/edit.png\" /></a>
							<a href=\"admin.php?page=module_news&amp;action=delete&amp;newsID={$newsEntrie['NEWS_ID']}\" title=\"" . sprintf($this->_Translation->GetTranslation('delete_the_news_message_%news_title%_from_the_%date%'), $newsEntrie['NEWS_TITLE'], date('d.m.Y H:i:s', $newsEntrie['NEWS_DATE'])) . "\"><img  alt=\"" . $this->_Translation->GetTranslation('delete') . "\" src=\"./img/del.png\" /></a>
							</td>
						</tr>";
				}
			$out .= "</tbody>
				</table>";
			return $out;
		}
		
		/**
		 * @access public
		 * @return string The title of the actual-module-page
		 */
		function GetTitle() {
			return 'News-Module';
		}
	}
?>