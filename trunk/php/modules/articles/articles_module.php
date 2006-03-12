<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2006 The ComaCMS-Team
 * @subpackage Articles
 */
 #----------------------------------------------------------------------#
 # file			: module_articles.php				#
 # created		: 2006-02-19					#
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
	require_once('classes/module.php');	
	
 	/**
	 * @package ComaCMS
	 * @subpackage Articles 
	 */
	class Module_Articles extends Module{
		
		function Module_Articles(&$SqlConnection, &$User, &$Lang, &$Config, &$ComaLate, &$ComaLib) {
 			$this->_SqlConnection = &$SqlConnection;
 			$this->_User = &$User;
 			$this->_Config = &$Config;
 			$this->_Lang = &$Lang;
 			$this->_ComaLate = &$ComaLate;
 			$this->_ComaLib = &$ComaLib;
 		}
 		
 		function UseModule($Identifer, $Parameters) {
 			$Parameters = explode('&', $Parameters);
 			$preview= false;
 			$count = 6;
 			foreach($Parameters as $parameter){
 				$parameter = explode('=', $parameter, 2);
 				if(empty($parameter[1]))
 					$parameter[1] = true;
 				$$parameter[0] = $parameter[1];
 			}
 			if($preview) {
 				return $this->_ArtuckesPreview($count); 
 			}	
 		}
 		
 		function _ArtuckesPreview($count = 6){
 			$sql = "SELECT *
				FROM " . DB_PREFIX . "articles
				ORDER BY article_date DESC
				LIMIT 0, $count";
			$articlesResult = $this->_SqlConnection->SqlQuery($sql);
			$imgMax = 100;
			$thumbnailFolder = $this->_Config->Get('thumbnailfolder', 'data/thumbnails/');
			$dateFormat = $this->_Config->Get('articles_date_format', 'd.m.Y');
			$dateFormat .= ' ' . $this->_Config->Get('articles_time_format', 'H:i:s');
			$showAuthor = $this->_Config->Get('articles_display_author', 1);
			$articlesArray = array();
			while($article = mysql_fetch_object($articlesResult)) {
				$thumbnail = '';
				$style = '';
				if($article->article_image != '') {
					$fileName = basename($article->article_image);
					
					if(file_exists($thumbnailFolder . $imgMax . '_' . $fileName)) {
					//$size = getimagesize($inlinemenu_folder . $imgmax . '_' . $filename);
					
					$thumbnail = '<img class="article_image" title="' . $article->article_title . '" alt="' . $article->article_title . '" src="' . generateUrl($thumbnailFolder . $imgMax . '_' . $fileName) . '" />';
					$size = getimagesize($thumbnailFolder . $imgMax . '_' . $fileName);
					$style = ' style="min-height:' . ($size[1] - (23 * $showAuthor)) . 'px"';
					}
				}
				$author = '';
				if($showAuthor) {
					$author = $this->_ComaLib->GetUserByID($article->article_creator);
				}
				$articlesArray[] = array(	'ARTICLE_ID' => $article->article_id,
								'ARTICLE_TITLE' => $article->article_title,
								'ARTICLE_THUMBNAIL' => $thumbnail,
								'ARTICLE_STYLE' => $style,
								'ARTICLE_DESCRIPTION' => nl2br($article->article_description),
								'ARTICLE_DATE' => date($dateFormat, $article->article_date),
								'ARTICLE_AUTHOR' => $author
								);
			}
			$this->_ComaLate->SetReplacement('ARTICLESPREVIEW', $articlesArray);
			$articlesTitle =  $this->_Config->Get('articles_title', '');
			if($articlesTitle != '')
				$articlesTitle = '<h3>' . $articlesTitle . '</h3>';
			$this->_ComaLate->SetReplacement('ARTICLES_BIG_TITLE', $articlesTitle);
 			$articlePreviewString = '</p>
 			<div class="articles-block">
 			{ARTICLES_BIG_TITLE}
 			<ARTICLESPREVIEW:loop>
 				<div class="article">
				<div class="article-title">
					<span class="article-date">{ARTICLE_DATE}</span>
					<strong>{ARTICLE_TITLE}</strong>
				</div><div class="article_inside"{ARTICLE_STYLE}>
				{ARTICLE_THUMBNAIL}{ARTICLE_DESCRIPTION} <a href="article.php?id={ARTICLE_ID}" title="Den vollst&auml;ndigen Artikel \'{ARTICLE_TITLE}\' lesen">mehr...</a></div>
				<div class="article-author">{ARTICLE_AUTHOR}</div>
			</div>	
 			</ARTICLESPREVIEW>
 			</div>
 			<p>';
 			return $articlePreviewString;
 		}
 		
	}
?>