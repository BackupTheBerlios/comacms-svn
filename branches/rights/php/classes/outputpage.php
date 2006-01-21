<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: outputpge.php					#
 # created		: 2005-09-01					#
 # copyright		: (C) 2005 The ComaCMS-Team			#
 # email		: comacms@williblau.de				#
 #----------------------------------------------------------------------#
 # This program is free software; you can redistribute it and/or modify	#
 # it under the terms of the GNU General Public License as published by	#
 # the Free Software Foundation; either version 2 of the License, or	#
 # (at your option) any later version.					#
 #----------------------------------------------------------------------#

	/**
	 * 
	 */
 	require_once('./system/functions.php');
 	 
 	/**
 	 * @package ComaCMS
 	 */
	class OutputPage {
		
		/**
		 * @access public
		 * @var string
		 */
		var $Text;
		
		/**
		 * @access public
		 * @var string
		 */
		var $Template;
		
		/**
		 * @access public
		 * @var string
		 */
		var $Templatefolder;
		
		/**
		 * @access public
		 * @var string This value will replace the template tag [text]
		 */
		var $Title;
		
		/**
		 * @access public
		 * @var string 
		 */
		var $Position;
		
		/**
		 * @access public
		 * @var integer
		 */
		var $PageID;
		
		/**
		 * @return void
		 */
		function SetText($text, $compiled = true) {
			if(empty($text)) {
				$text = '';
				$compiled = true;
			}
			if($compiled)
				$this->Text = $text;
			else
				$this->Text = convertToPreHtml($text);	
		}
		
		/**
		 * @return void
		 */
		function ReplaceTagInTemplate($tag, $replace) {
			$this->Template = str_replace("[$tag]", $replace, $this->Template);
		}
		
		/**
		 * @return void
		 */
		function ReplaceTagInText($tag, $replace) {
			$this->Text = str_replace("[$tag]", $replace, $this->Text);
		}
		
		/**
		 * @return void
		 */	
		function PositionOfPage($page_id , $between = ' > ', $link=true) {
			$sql = "SELECT *
				FROM " . DB_PREFIX . "pages
				WHERE page_id=$page_id";
			$actual_result = db_result($sql);
			$actual = mysql_fetch_object($actual_result);
			$parent_id = $actual->page_parent_id;
			$way_to_root = '';		
			
			while($parent_id != 0) {
				$sql = "SELECT *
					FROM " . DB_PREFIX . "pages
					WHERE page_id=$parent_id";
				$parent_result = db_result($sql);
				$parent = mysql_fetch_object($parent_result);
				$parent_id = $parent->page_parent_id;
				$page_title = $parent->page_title;
				if($link)
					$page_title = '<a href="index.php?page=' . $parent->page_name . '">' . $page_title . '</a>';
				$way_to_root = $page_title . $between . $way_to_root;
			}
			
			if($link)
				$actual_page_title = '<a href="index.php?page=' . $actual->page_name . '">' . $actual->page_title . '</a>';
			$this->Position = $way_to_root . $actual_page_title;
		}
		
		/**
		 * @return string
		 */
		function GenerateMenu($menuid = 1) {
			include($this->Templatefolder . '/menu.php');
			$menu_out = '';
			$sql = "SELECT *
				FROM " . DB_PREFIX . "menu
				WHERE menu_menuid='$menuid'
				ORDER BY menu_orderid ASC";
			$menu_result = db_result($sql);
			while($menu_data = mysql_fetch_object($menu_result)) {
				if($menuid == 1)
						$menu_str = $menu_link;
				else
						$menu_str = $menu_link2;
				$menu_str = str_replace('[TEXT]', $menu_data->menu_text, $menu_str);
				$link = $menu_data->menu_link;
					if(substr($link, 0, 2) == 'l:')
					$link = @$internal_page_root . 'index.php?page=' . substr($link, 2);
					if(substr($link, 0, 2) == 'g:')
						$link = @$internal_page_root . 'gallery.php?page=' . substr($link, 2);
					if(substr($link, 0, 2) == 'a:')
						$link = @$internal_page_root . 'admin.php?page=' . substr($link, 2);
						
				$menu_str = str_replace('[LINK]', $link, $menu_str);
				$new = $menu_data->menu_new;
					if($new == 'yes')
					$new = 'target="_blank" ';
					else
					$new = '';
				$menu_str = str_replace('[NEW]', $new, $menu_str);
				$menu_out .= $menu_str . "\r\n";
			}
			return $menu_out;
		}
		
		/**
		 * @return bool
		 */
		function FindTag($tag) {
			if(strpos($this->Template, '[' . $tag . ']') === false) // Important: there must be three '='
				return false;
			return true;
		}
		
		/**
		 * @return void
		 */
		function LoadPage($pagename, $user) {
			$load_old = false;
			$change = GetPostOrGet('change');
			if(is_numeric($change) && $user->isLoggedIn && $change != 0)
				$load_old = true;
			else
				$change = 0;
			if($load_old)
				$sql = "SELECT *
					FROM " . DB_PREFIX . "pages_history
					WHERE page_id=$pagename
					ORDER BY page_date ASC
					LIMIT " . ($change - 1) . ",1";
			else
				$sql = "SELECT *
					FROM " . DB_PREFIX . "pages
					WHERE page_name='$pagename'";
			$page_result = db_result($sql);
			if(!($page_data =  mysql_fetch_object($page_result))) {
				$sql = "SELECT *
					FROM " . DB_PREFIX . "pages
					WHERE page_id='$pagename'";	
				$page_result = db_result($sql);
				if(!($page_data =  mysql_fetch_object($page_result))) {
					header("Location: special.php?page=404&want=$pagename");
					die();
				}
			}
			if(!$load_old && $page_data->page_access == 'deleted' && !$user->accessRghts->delete) {
				header("Location: special.php?page=410&want=$pagename"); //HTTP 410 Gone
				die();
			}
			
			/*if(!$load_old) {
				if($page_data->page_access == 'deleted')
					die('deleted');
				if($page_data->page_access == 'hidden')
					if(!$user->IsLoggedIn)	
						header("Location: special.php?page=login&want=$page_data->page_id");
			}*/
			//TODO: generate a warning if an 'old' page is shown
			$this->Title = $page_data->page_title;
			$this->PositionOfPage($page_data->page_id);
			$this->PageID = $page_data->page_id;
			if($page_data->page_type == 'text') {
				include('./classes/textpage.php');
				$textpage = new TextPage($page_data->page_id, $change);
				$this->Text = $textpage->HTML;
			}
			elseif($page_data->page_type == 'gallery') {
				include('./classes/gallerypage.php');
				$gallerypage = new GalleryPage($page_data->page_id);
				$this->Text = $gallerypage->HTML;
			}
			if($load_old || $page_data->page_access == 'deleted')
				$this->Text = "<div class=\"warning\">Sie befinden sich auf einer Seite, die so wie Sie sie sehen, nicht mehr existiert.</div>" . $this->Text;
		}
		
		/**
		 * @return void
		 */
		function LoadTemplate($templatefolder) {
			if(empty($templatefolder))
				$templatefolder = './styles/clear';
			$this->Templatefolder = $templatefolder;
			$template_file = fopen($templatefolder . '/mainpage.php', 'r');
			$this->Template = fread($template_file, filesize($templatefolder . '/mainpage.php'));
			fclose($template_file);
		}
		
		/**
		 * @return string
		 */
		function OutputHTML() {
			global $config;
			if($this->PageID == $config->Get('default_page', '0'))
				$this->Template = preg_replace("/\<notathome\>(.+?)\<\/notathome\>/s", '', $this->Template);
			else
				$this->Template = preg_replace("/\<notathome\>(.+?)\<\/notathome\>/s", "$1", $this->Template);

			$this->Template = str_replace('[PAGENAME]', $config->Get('pagename', ''), $this->Template);
			$this->Template = str_replace('[TEXT]', $this->Text, $this->Template);
			$this->Template = str_replace('[TITLE]', $this->Title, $this->Template);
			$this->Template = str_replace('[POSITION]', $this->Position, $this->Template);
			$this->Template = str_replace('[STYLE_PATH]', $this->Templatefolder, $this->Template);
			if($this->FindTag('MENU'))
				$this->Template = str_replace('[MENU]', $this->GenerateMenu(1), $this->Template);;
			if($this->FindTag('MENU2'))
				$this->Template = str_replace('[MENU2]', $this->GenerateMenu(2), $this->Template);;
			return $this->Template;
		}
	}
?>