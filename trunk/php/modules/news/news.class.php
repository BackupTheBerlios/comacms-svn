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

		var $_SqlConnection;
		var $_ComaLib;
		var $_User;
		var $_Config;
    		function News(&$SqlConnection, &$ComaLib, &$User, &$Config) {
    			$this->_SqlConnection = &$SqlConnection;
    			$this->_ComaLib = &$ComaLib;
    			$this->_User = &$User;
    			$this->_Config = &$Config;

    		}
    		
    		function FillArray($Maximum = 6) {
    			$entries = array();
    			$sql = "SELECT *
				FROM " . DB_PREFIX . "news
				ORDER BY date DESC LIMIT 0, $Maximum";
			$entriesResult = $this->_SqlConnection->SqlQuery($sql);	
			$displayAuthor = false;
			if($this->_Config->Get('news_display_author', 1) == 1)
				$displayAuthor = true;
			$dateFormat = $this->_Config->Get('news_date_format', 'd.m.Y');
			$dateFormat .= ' ' . $this->_Config->Get('news_time_format', 'H:i:s');
			while($entrie = mysql_fetch_object($entriesResult)) {
				$newsAuthor = '';
				
				if($displayAuthor)
					$newsAuthor = $this->_ComaLib->GetUserByID($entrie->userid);
				$entries[] = array(	'NEWS_DATE' => date($dateFormat, $entrie->date),
							'NEWS_TEXT' => nl2br($entrie->text),
							'NEWS_AUTHOR' => $newsAuthor,
							'NEWS_TITLE' => $entrie->title);
    			}
    			return $entries;
		}
	}
?>