<?php
/**
 * @package ComaCMS
 * @subpackage News
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: news_admin.php				#
 # created		: 2006-02-18					#
 # copyright		: (C) 2005-2006 The ComaCMS-Team		#
 # email		: comacms@williblau.de				#
 #----------------------------------------------------------------------#
 # This program is free software; you can redistribute it and/or modify	#
 # it under the terms of the GNU General Public License as published by	#
 # the Free Software Foundation; either version 2 of the License, or	#
 # (at your option) any later version.					#
 #----------------------------------------------------------------------#
 
 	/**
	 * @ignore
	 */
	require_once('classes/admin/admin_module.php');
	require_once('modules/news/news.class.php');
	 	
	/**
	 * @package ComaCMS
	 * @subpackage News 
	 */
	class Admin_Module_News extends Admin_Module{
		
		/**
 		 * @param Sql SqlConnection
 		 * @param User User
 		 * @param array Lang
 		 * @param Config Config
 		 * @param ComaLate ComaLate
 		 * @param ComaLib ComaLib
 		 */
		function Admin_Module_News(&$SqlConnection, &$User, &$Lang, &$Config, &$ComaLate, &$ComaLib) {
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
				case 'add':
						$out .= $this->_AddPage(); 					
						break;
				case 'save':
						$out .= $this->_HomePage();					
						break;
				case 'delete':
						$out .= $this->_DeletePage();					
						break;
				case 'edit':
						$out .= $this->_HomePage();					
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
					$out = "<h2>{$this->_Lang['delete_news_message']}?</h2>
						<p>" . sprintf($this->_Lang['do_you_really_want_to_delete_the_news_message_%news_title%_from_the_%date%?'], $newsMessage['NEWS_TITLE'], date('d.m.Y H:i:s', $newsMessage['NEWS_DATE'])) . "</p>
						<a href=\"admin.php?page=module_news&amp;action=delete&amp;newsID=$newsID&amp;confirmation=1\" class=\"button\">{$this->_Lang['yes']}</a>
						<a href=\"admin.php?page=module_news\" class=\"button\">{$this->_Lang['no']}</a>";
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
			$out = "<h2>{$this->_Lang['write_a_new_news_message']}</h2>
			<form action=\"admin.php\" method=\"post\">
				<input type=\"hidden\" name=\"page\" value=\"module_news\" />
				<input type=\"hidden\" name=\"action\" value=\"add\" />
			<fieldset>
			<legend>{$this->_Lang['new_news_message']}</legend>
			<div class=\"row\">
				<label for=\"newsTitle\">{$this->_Lang['title']}<span class=\"info\">{$this->_Lang['todo']}</span></label>
				<input name=\"newsTitle\" id=\"newsTitle\" value=\"\" type=\"text\"/>
			</div>
			<div class=\"row\">
				<label for=\"newsText\">{$this->_Lang['text']}<span clss=\"info\">{$this->_Lang['todo']}</span></label>
				<textarea name=\"newsText\" id=\"newsText\"></textarea>
			</div>
			<div class=\"row\">
				<input type=\"submit\" class=\"button\" value=\"{$this->_Lang['save']}\"/>
				<a class=\"button\" href=\"admin.php?page=module_news\">{$this->_Lang['back']}</a>
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
			$out = "<h2>{$this->_Lang['news_overview']}</h2>
				<a class=\"button\" href=\"admin.php?page=module_news&amp;action=new\" title=\"{$this->_Lang['write_a_new_news_message']}\">{$this->_Lang['write_a_new_news_message']}</a>
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
							<a href=\"admin.php?page=module_news&amp;action=edit\" title=\"" . sprintf($this->_Lang['edit_the_news_message_%news_title%_from_the_%date%'], $newsEntrie['NEWS_TITLE'], date('d.m.Y H:i:s', $newsEntrie['NEWS_DATE'])) . "\"><img alt=\"{$this->_Lang['edit']}\" src=\"./img/edit.png\" /></a>
							<a href=\"admin.php?page=module_news&amp;action=delete&amp;newsID={$newsEntrie['NEWS_ID']}\" title=\"" . sprintf($this->_Lang['delete_the_news_message_%news_title%_from_the_%date%'], $newsEntrie['NEWS_TITLE'], date('d.m.Y H:i:s', $newsEntrie['NEWS_DATE'])) . "\"><img  alt=\"{$this->_Lang['delete']}\" src=\"./img/del.png\" /></a>
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