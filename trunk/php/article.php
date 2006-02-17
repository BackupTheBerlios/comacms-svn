<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: articles.php					#
 # created		: 2005-08-29					#
 # copyright		: (C) 2005 The ComaCMS-Team			#
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
	define("COMACMS_RUN", true);
	
	include("common.php");
		
	$page_id = GetPostOrGet('id');
	$text = '';
	if(is_numeric($page_id))	{
		$sql = "SELECT *
			FROM " . DB_PREFIX . "articles
			WHERE article_id='$page_id'";
		$article_result = $sqlConnection->SqlQuery($sql);
		$image = '';
		if($article_data = mysql_fetch_object($article_result)) {
			$imgmax = 300;
			$inlinemenu_folder = $config->Get('thumbnailfolder','data/thumbnails/');	
			$thumb = basename($article_data->article_image);
			preg_match("'^(.*)\.(gif|jpe?g|png|bmp)$'i", $thumb, $ext);
			if(strtolower(@$ext[2]) == 'gif')
				$thumb .= '.png';
			//$orig_sizes = getimagesize($image->file_path);
			$succes = true;
			if(!file_exists($inlinemenu_folder . $imgmax . '_' . $thumb))
				$succes = generateThumb($article_data->article_image, $inlinemenu_folder . $imgmax . '_', $imgmax);
			if(file_exists($inlinemenu_folder . $imgmax . '_' . $thumb) || $succes) {
				$image = "<img class=\"article_image\" src=\"" . generateUrl($inlinemenu_folder . $imgmax . '_' . $thumb) . "\" />";
			}
			
			$title = "Artikel:&nbsp;$article_data->article_title";
			$position = $article_data->article_title;
			$text .= "\t\t\t<h3>$article_data->article_title</h3><hr /><br />$image
				" . $article_data->article_html;
		}
	}
	if($text == '')	{
		$sql = "SELECT *
			FROM " . DB_PREFIX . "articles
			ORDER BY article_date DESC";
		$article_result = $sqlConnection->SqlQuery($sql);
		
		$title = "Artikelliste";
		
		$text = "\t\t\t<h3>Artikelliste</h3><hr /><br />\r\n";
		$text .= "\t\t\t<table class=\"tablestyle\">\r\n";
		$text .= "\t\t\t\t<thead>
					<tr>
						<td>Titel</td>
						<td>Datum</td>
						<td>Beschreibung</td>
						<td>Autor</td>
					</tr>
				</thead>\r\n";
		
		while($articles_data = mysql_fetch_object($article_result))	{
			$text .= "\t\t\t\t<tr>
					<td><a href=\"article.php?id=$articles_data->article_id\">" . $articles_data->article_title . "</a></td>
					<td>" . date('d.m.Y H:i:s', $articles_data->article_date) . "</td>
					<td>" . nl2br($articles_data->article_description) . "</td>
					<td>" . getUserByID($articles_data->article_creator) . "</td>
				</tr>\r\n";
		}
		
		$text .= "\t\t\t</table>";
	}
	
	$output->SetReplacement('TEXT', $text);
	$output->Title .= $title;
	$output->SetCondition('notathome', true);	
	if(isset($position))
		$output->SetReplacement('PATH', "<a href=\"article.php\">Artikel</a> -> <a href=\"article.php?id=$article_data->article_id\">$position</a>");
	else
		$output->SetReplacement('PATH', "<a href=\"article.php\">Artikel</a>");
	$outputpage = new OutputPage($sqlConnection);
	$output->SetReplacement('MENU' , $outputpage->GenerateMenu());
	$output->SetReplacement('MENU2' , $outputpage->GenerateMenu(2));
	//
	// end
	//
	$output->PrintOutput();
	echo "\r\n<!-- rendered in " . round(getmicrotime(microtime()) - getmicrotime($starttime), 4) . ' seconds with ' . $sqlConnection->QueriesCount .' SQL queries -->';
?>