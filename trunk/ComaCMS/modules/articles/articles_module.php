<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2006 The ComaCMS-Team
 * @subpackage Articles
 */
 #----------------------------------------------------------------------
 # file                 : module_articles.php
 # created              : 2006-02-19
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
	require_once __ROOT__ . '/classes/module.php';	
	require_once __ROOT__ . '/classes/imageconverter.php';
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
 			foreach($Parameters as $parameter){
 				$parameter = explode('=', $parameter, 2);
 				if(empty($parameter[1]))
 					$parameter[1] = true;
 				$$parameter[0] = $parameter[1];
 			}
 			if($preview) {
 				return $this->_ArticlesPreview(); 
 			}	
 		}
 		
 		function _ArticlesPreview(){
			$count = $this->_Config->Get('articles_display_count', 6);
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
					$image = new ImageConverter($article->article_image);
					$size = $image->Size; 
					if($size[0] > $imgMax && $size[1] > $imgMax)
						$size = $image->CalcSizeByMax($imgMax);
					$resizedFileName = $thumbnailFolder . '/' . $size[0] . 'x' . $size[1] . '_' . basename($article->article_image);
					if(!file_exists($resizedFileName))
						$image->SaveResizedTo($size[0], $size[1], $thumbnailFolder, $size[0] . 'x' . $size[1] . '_');
					if(file_exists($resizedFileName))
							$thumbnail = '<img class="article_image" style="padding-right:' . (($imgMax - $size[0])/2) . 'px;padding-left:' . (($imgMax - $size[0])/2) . 'px;" title="' . $article->article_title . '" alt="' . $article->article_title . '" src="' . generateUrl($resizedFileName) . '" />';
					$style = ' style="min-height:' . ($size[1] - (19 * $showAuthor)) . 'px"';
					//}
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
				{ARTICLE_THUMBNAIL}{ARTICLE_DESCRIPTION} <a href="special.php?page=module&amp;moduleName=articles&amp;action=show&amp;articleId={ARTICLE_ID}" title="Den vollst&auml;ndigen Artikel \'{ARTICLE_TITLE}\' lesen">mehr...</a></div>
				<div class="article-author">{ARTICLE_AUTHOR}</div>
			</div>	
 			</ARTICLESPREVIEW>
 			</div>
 			<p>';
 			return $articlePreviewString;
 		}
 		
 		function GetPage($Action) {
 			$output = ' ';
 			switch ($Action) {
				case 'show':
							$articleId = GetPostOrGet('articleId');
							if(is_numeric($articleId))
								$output = $this->_ShowArticlePage($articleId);
							else
								$output = $this->_OverviewPage();
						break;
				default:
						$output = $this->_OverviewPage();
 			}
 			return $output;
 		}
 		
 		function _ShowArticlePage($ArticleId) {
 			$sql = "SELECT *
				FROM " . DB_PREFIX . "articles
				WHERE article_id = $ArticleId";
			$output = '';
			$author = '';
			$thumbnail = '';
			$imgMax = 300;
			$thumbnailFolder = $this->_Config->Get('thumbnailfolder', 'data/thumbnails/');
			$dateFormat = $this->_Config->Get('articles_date_format', 'd.m.Y');
			$dateFormat .= ' ' . $this->_Config->Get('articles_time_format', 'H:i:s');
			$showAuthor = $this->_Config->Get('articles_display_author', 1);
			$articlesResult = $this->_SqlConnection->SqlQuery($sql);
			if($article = mysql_fetch_object($articlesResult)) {
				
				if($article->article_image != '') {
					$image = new ImageConverter($article->article_image);
					$size = $image->Size; 
					if($size[0] > $imgMax && $size[1] > $imgMax)
						$size = $image->CalcSizeByMax($imgMax);
					$resizedFileName = $thumbnailFolder . '/' . $size[0] . 'x' . $size[1] . '_' . basename($article->article_image);
					if(!file_exists($resizedFileName))
						$image->SaveResizedTo($size[0], $size[1],$thumbnailFolder, $size[0] . 'x' . $size[1] . '_');
					if(file_exists($resizedFileName))
						$thumbnail = '<img class="article_image" title="' . $article->article_title . '" alt="' . $article->article_title . '" src="' . generateUrl($resizedFileName) . '" />';
				}	
				if($showAuthor) {
					$author = $this->_ComaLib->GetUserByID($article->article_creator);
				}
				$this->_ComaLate->SetReplacement('ARTICLE_TITLE', $article->article_title);
				$this->_ComaLate->SetReplacement('ARTICLE_DESCRIPTION', $article->article_description);
				$this->_ComaLate->SetReplacement('ARTICLE_TEXT', $article->article_html);
				$this->_ComaLate->SetReplacement('ARTICLE_DATE', date($dateFormat, $article->article_date));
				$this->_ComaLate->SetReplacement('ARTICLE_AUTHOR', $author);
				$this->_ComaLate->SetReplacement('ARTICLE_IMAGE', $thumbnail);
				$output = '<h2>{ARTICLE_TITLE}</h2>
					<div class="article_show">
						<div class="article_date" >{ARTICLE_DATE}</div>
						<div>{ARTICLE_IMAGE}</div>
						<div class="article_description"><p>{ARTICLE_DESCRIPTION}</p></div>
						<div class="article_text">{ARTICLE_TEXT}</div>
						<div class="article_author">{ARTICLE_AUTHOR}</div>
					</div>
					<a href="special.php?page=module&amp;moduleName=articles">Zur &Uuml;bersicht</a>';
					
			}
			return $output;
 		}
 		
 		function _OverviewPage() {
 			$sql = "SELECT *
				FROM " . DB_PREFIX . "articles
				ORDER BY article_date DESC";
			$articlesResult = $this->_SqlConnection->SqlQuery($sql);
			$imgMax = 120;
			$thumbnailFolder = $this->_Config->Get('thumbnailfolder', 'data/thumbnails/');
			$dateFormat = $this->_Config->Get('articles_date_format', 'd.m.Y');
			$dateFormat .= ' ' . $this->_Config->Get('articles_time_format', 'H:i:s');
			$showAuthor = $this->_Config->Get('articles_display_author', 1);
 			$articlesArray = array();
			while($article = mysql_fetch_object($articlesResult)) {
				
				$thumbnail = '';
				if($article->article_image != '') {
					if(file_exists($article->article_image)) {
						$image = new ImageConverter($article->article_image);

						$size = $image->Size; 
						if($size[0] > $imgMax && $size[1] > $imgMax)
							$size = $image->CalcSizeByMax($imgMax);

						$resizedFileName = $thumbnailFolder . $size[0] . 'x' . $size[1] . '_' . basename($article->article_image);
						if(!file_exists($resizedFileName))
							$image->SaveResizedTo($size[0], $size[1],$thumbnailFolder, $size[0] . 'x' . $size[1] . '_');

						if(file_exists($resizedFileName))
							$thumbnail = '<img class="article_image" title="' . $article->article_title . '" alt="' . $article->article_title . '" src="' . generateUrl($resizedFileName) . '" />';
					}
				}	
				$author = '';
				if($showAuthor) {
					$author = $this->_ComaLib->GetUserByID($article->article_creator);
				}

				$articlesArray[] = array(	'ARTICLE_ID' => $article->article_id,
								'ARTICLE_TITLE' => $article->article_title,
								'ARTICLE_THUMBNAIL' => $thumbnail,
								'ARTICLE_DESCRIPTION' => nl2br($article->article_description),
								'ARTICLE_DATE' => date($dateFormat, $article->article_date),
								'ARTICLE_AUTHOR' => $author
								);
			}
			$articlesTitle =  $this->_Config->Get('articles_title', '');
			if($articlesTitle != '')
				$articlesTitle = '<h2>' . $articlesTitle . '</h2>';
			$this->_ComaLate->SetReplacement('ARTICLES_BIG_TITLE', $articlesTitle);
			
 			$this->_ComaLate->SetReplacement('ARTICLESOVERVIEW', $articlesArray);
 			$articleOverviewString = '{ARTICLES_BIG_TITLE}
 			<ol class="articles_overview">
 			<ARTICLESOVERVIEW:loop>
 				<li><h2>{ARTICLE_TITLE}</h2>
 				<div class="article_date">{ARTICLE_DATE}</div>
 				<div>{ARTICLE_THUMBNAIL}
 					<div class="article_description">{ARTICLE_DESCRIPTION}</div>
 					<div class="article_link"><a href="special.php?page=module&amp;moduleName=articles&amp;action=show&amp;articleId={ARTICLE_ID}">Den Artikel &quot;{ARTICLE_TITLE}&quot; weiterlesen...</a></div>
 				</div>
 				<div class="article_author">{ARTICLE_AUTHOR}</div>
 				</li>
 			</ARTICLESOVERVIEW>
 			</ol>'; 
 			
 			return $articleOverviewString;
 		}
 		
 		function getTitle() {
 			return "Articles";
 		}
 		
	}
?>