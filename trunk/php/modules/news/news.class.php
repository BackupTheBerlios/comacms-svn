<?php
/**
 * @package ComaCMS
 * @subpackage News
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: news.class.php				#
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
    		 * Adds a new News-Message
    		 * @access public
    		 * @return void
    		 */
    		function AddMessage($Title, $Text) {
    			// is there some content?
    			if($Title != '' && $Text != '') {
    				$sql = "INSERT INTO " . DB_PREFIX . "news (title, text, date, userid)
    					VALUES ('$Title', '$Text', '" . mktime() . "', {$this->_User->ID})";
    				$this->_SqlConnection->SqlQuery($sql);
    			}
    			
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
    				$sql = "SELECT *
    					FROM " . DB_PREFIX ."news
    					WHERE id=$NewsID
    					LIMIT 0,1";
    				$messageResult = $this->_SqlConnection->SqlQuery($sql);
    				// check if there is a record in the mysql table if not return an empty array
    				if($message = mysql_fetch_object($messageResult)) {
    					$messageArray = array(	'NEWS_DATE' => $message->date,
							'NEWS_TEXT' => $message->text,
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
    		function FillArray($Maximum = 6, $ParserDate = true, $DisplayAuthor = false) {
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
			if($ParserDate) {
				$dateFormat = $this->_Config->Get('news_date_format', 'd.m.Y');
				$dateFormat .= ' ' . $this->_Config->Get('news_time_format', 'H:i:s');
			}
			// paste all entries into the array
			while($entrie = mysql_fetch_object($entriesResult)) {
				$newsAuthor = '';
				// set the author if it should be so
				if($displayAuthor)
					$newsAuthor = $this->_ComaLib->GetUserByID($entrie->userid);
				
				$entries[] = array(	'NEWS_DATE' => ($ParserDate) ? date($dateFormat, $entrie->date) : $entrie->date, // a real date-string or the timestamp?
							'NEWS_TEXT' => nl2br($entrie->text),
							'NEWS_AUTHOR' => $newsAuthor,
							'NEWS_TITLE' => $entrie->title,
							'NEWS_ID' => $entrie->id);
    			}
    			return $entries;
		}
	}
?>