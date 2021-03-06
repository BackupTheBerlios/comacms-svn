<?php
/**
 * @package ComaCMS
 * @subpackage News
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : news.class.php
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
	 * @package ComaCMS
	 * @subpackage News 
	 */
	class News {

		/**
		 * @access private
		 * @var Sql
		 */
		var $_SqlConnection;
		
		/**
		 * @access private
		 * @var ComaLib
		 */
		var $_ComaLib;
		
		/**
		 * @access private
		 * @var User
		 */
		var $_User;
		
		/**
		 * @access private
		 * @var Config
		 */
		var $_Config;
		
		/**
		 * @param Sql SqlConnection
		 * @param ComaLib ComaLib
		 * @param User User
		 * @param Config Config
		 */
    		function News(&$SqlConnection, &$ComaLib, &$User, &$Config) {
    			$this->_SqlConnection = &$SqlConnection;
    			$this->_ComaLib = &$ComaLib;
    			$this->_User = &$User;
    			$this->_Config = &$Config;

    		}
    		
    		/**
    		 * Update a News-Message
    		 * @param integer NewsID
    		 * @param string Title
    		 * @param string Text
    		 * @return void
    		 * @access public
    		 */
    		function UpdateMessage($NewsID, $Title, $Text) {
    			// is there some content and is the value of $NewsID plausible?
    			if(is_numeric($NewsID) && $Title != '' && $Text != '') {
    				$html = $this->_GenerateHtml($Text);
    				$sql = "UPDATE " . DB_PREFIX . "news
    					SET title= '$Title', text= '$Text', text_html='$html' WHERE id=$NewsID";
    				$this->_SqlConnection->SqlQuery($sql);
    			}
    		}
    		
    		/**
    		 * Adds a new News-Message
    		 * @access public
    		 * @param string Title
    		 * @param string Text
    		 * @return void
    		 */
    		function AddMessage($Title, $Text) {
    			// is there some content?
    			if($Title != '' && $Text != '') {
    				$html = $this->_GenerateHtml($Text);
    				$sql = "INSERT INTO " . DB_PREFIX . "news (title, text, date, userid, text_html)
    					VALUES ('$Title', '$Text', '" . mktime() . "', {$this->_User->ID}, '$html')";
    				$this->_SqlConnection->SqlQuery($sql);
    			}
    			
    		}
    		
    		function _generateHtml($Text) {
    			preg_match_all("#\[\[(.+?)\]\]#s", $Text, $links);
				$link_list = array();
				$linkNr = 1;
				// replace all links with a short uniqe id to replace them later back
				foreach($links[1] as $link) {
					$link_list[$linkNr] = $link;
					$Text = str_replace("[[$link]]", "[[%$linkNr%]]", $Text);
					$linkNr++;
				}
				
				foreach($link_list as $linkNr => $link) {
					if(preg_match("#^(.+?)\|(.+?)$#i", $link, $link2))				
						$Text = str_replace("[[%$linkNr%]]", "<a href=\"" . TextActions::MakeLink($link2[1]) . "\">" . $link2[2] . "</a>", $Text);
					else
						$Text = str_replace("[[%$linkNr%]]", "<a href=\"" . TextActions::MakeLink($link) . "\">" . $link . "</a>", $Text);
				}
				$Text = nl2br($Text);
				return $Text;
    		}
    		
    		/**
    		 * Delete a News-Message by ID
    		 * @access public
    		 * @param integer NewsID
    		 * @return void
    		 */
    		function DeleteMessage($NewsID = -1) {
    			if(is_numeric($NewsID)) {
    				$sql = "DELETE FROM " . DB_PREFIX . "news
    					WHERE id=$NewsID";
    				$this->_SqlConnection->SqlQuery($sql);
    			}
    		}
    		
    		/**
    		 * Get a News-Message by ID
    		 * @access public
    		 * @param integer NewsID
    		 * @return array
    		 */
    		function GetMessage($NewsID = -1) {
    			if(is_numeric($NewsID)) {
    				$sql = "SELECT date, text, userid, title, id, text_html
    					FROM " . DB_PREFIX ."news
    					WHERE id=$NewsID
    					LIMIT 1";
    				$messageResult = $this->_SqlConnection->SqlQuery($sql);
    				// check if there is a record in the mysql table if not return an empty array
    				if($message = mysql_fetch_object($messageResult)) {
    					$messageArray = array(	'NEWS_DATE' => $message->date,
							'NEWS_TEXT' => $message->text,
							'NEWS_TEXT_HTML' => $message->text_html,
							'NEWS_AUTHOR' => $message->userid,
							'NEWS_TITLE' => $message->title,
							'NEWS_ID' => $message->id);
					return $messageArray;
    				}	
    				else
    					return array();
    				
    			}
    		}
    		
    		/**
    		 * Fill an array with the data of News-Messages, which is ready to paste in a ComaLate-Template
    		 * @access public
    		 * @param integer Maximum The maximum count of News-Messages, which should be loaded, if it is -1 all News-Messages will be loaded
    		 * @param boolean ParserDate Should the timsamp of each News-Message parsed to a hunam-readable value?
    		 * @param boolean DisplayAutor Put the author into the array? if it's 'false' the value of the config is decisive if not the name will be shown
    		 * @return array A ComaLate ready Array
    		 */
    		function FillArray($Maximum = 6, $ConvertTimestamp = true, $DisplayAuthor = false) {
    			$entries = array();
    			$sql = "SELECT *
				FROM " . DB_PREFIX . "news
				ORDER BY date DESC
				LIMIT 0, $Maximum";
    			// if $Maximum is -1 then show all entries
    			if($Maximum == -1)
    				$sql = "SELECT *
					FROM " . DB_PREFIX . "news
					ORDER BY date DESC";
			$entriesResult = $this->_SqlConnection->SqlQuery($sql);	
			$displayAuthor = false;
			if($this->_Config->Get('news_display_author', 1) == 1)
				$displayAuthor = true;
			if($DisplayAuthor)
				$displayAuthor = true;
			$dateFormat = '';
			// get the date-format-string if the date should be human-readable
			if($ConvertTimestamp) {
				$dateFormat = $this->_Config->Get('news_date_format', 'd.m.Y');
				$dateFormat .= ' ' . $this->_Config->Get('news_time_format', 'H:i:s');
			}
			// paste all entries into the array
			while($entrie = mysql_fetch_object($entriesResult)) {
				$newsAuthor = '';
				// set the author if it should be so
				if($displayAuthor)
					$newsAuthor = $this->_ComaLib->GetUserByID($entrie->userid);
				
				$entries[] = array(	'NEWS_DATE' => ($ConvertTimestamp) ? date($dateFormat, $entrie->date) : $entrie->date, // a real date-string or the timestamp?
							'NEWS_TEXT' => $entrie->text,
							'NEWS_TEXT_HTML' => $entrie->text_html,
							'NEWS_AUTHOR' => $newsAuthor,
							'NEWS_TITLE' => $entrie->title,
							'NEWS_ID' => $entrie->id);
    			}
    			return $entries;
		}
	}
?>