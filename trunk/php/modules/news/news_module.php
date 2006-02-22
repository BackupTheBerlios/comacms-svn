<?php
/**
 * @package ComaCMS
 * @subpackage News
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: news_module.php				#
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
	require_once('modules/news/news.class.php');
	/**
	 * @package ComaCMS
	 * @subpackage News 
	 */
	class Module_News extends Module{
		
		function Module_News(&$SqlConnection, &$User, &$Lang, &$Config, &$ComaLate, &$ComaLib) {
 			$this->_SqlConnection = &$SqlConnection;
 			$this->_User = &$User;
 			$this->_Config = &$Config;
 			$this->_Lang = &$Lang;
 			$this->_ComaLate = &$ComaLate;
 			$this->_ComaLib = &$ComaLib;
 		}
 		
 		function UseModule($Identifer, $Parameters) {
 			$Parameters = explode('&', $Parameters);
 			$count = 6;
 			$block = false;
 			foreach($Parameters as $parameter){
 				$parameter = explode('=', $parameter, 2);
 				if(empty($parameter[1]))
 					$parameter[1] = true;
 				$$parameter[0] = $parameter[1];
 			}
 			$newsTitle =  $this->_Config->Get('news_title', '');
 			if($newsTitle != '')
				$newsTitle = '<h3>' . $newsTitle . '</h3>';
			$this->_ComaLate->SetReplacement('NEWS_BIG_TITLE', $newsTitle);	
			$news = new News($this->_SqlConnection, $this->_ComaLib, $this->_User, $this->_Config);
			$newsArray = $news->FillArray($count);
 			$this->_ComaLate->SetReplacement('NEWS', $newsArray);
 			$newsSring = '</p><div class="news-block">
 						{NEWS_BIG_TITLE}
 						<NEWS:loop>
 						<div class="news">					
							<div class="news-title">
							<span class="news-date">{NEWS_DATE}</span>{NEWS_TITLE}
							</div>
							{NEWS_TEXT}
							<div class="news-author">{NEWS_AUTHOR}</div>
						</div>
 						</NEWS>
 					</div><p>';
 			return $newsSring;
 		}
	}
?>