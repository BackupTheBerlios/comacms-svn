<?php
/*****************************************************************************
 *
 *  file		: articles.php
 *  created		: 2005-08-29
 *  copyright	: (C) 2005 The ComaCMS-Team
 *  email		: comacms@williblau.de
 *
 *****************************************************************************/

/*****************************************************************************
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *****************************************************************************/
	define("COMACMS_RUN", true);
	
	include("common.php");
		
	
	if(isset($extern_page_id))	{
		$sql = "SELECT *
			FROM " . DB_PREFIX . "articles
			WHERE article_id='$extern_page_id'";
		$article_result = db_result($sql);
		if(!($article_data = mysql_fetch_object($article_result)))
			header("Location: special.php?page=404&notfound=g:$extern_page");
		
		$title = "Artikel:&nbsp;$article_data->article_title";
		$position = $article_data->article_title;
		
		$text = '';
		$text .= "\t\t\t<h3>$article_data->article_title</h3><hr /><br />
			$article_data->article_html";
	}
	else	{
		$sql = "SELECT *
			FROM " . DB_PREFIX . "articles";
		$article_result = db_result($sql);
		
		$title = "Artikelliste";
		
		$text = "\t\t\t<h3>Artikelliste</h3><hr /><br />\r\n";
		$text .= "\t\t\t<table>\r\n";
		$text .= "\t\t\t\t<tr>
					<td>Titel</td>
					<td>Datum</td>
					<td>Beschreibung</td>
					<td>Autor</td>
				</tr>\r\n";
		
		while($articles_data = mysql_fetch_object($article_result))	{
			$text .= "\t\t\t\t<tr>
					<td><a href=\"article.php?page_id=$articles_data->article_id\">" . $articles_data->article_title . "</a></td>
					<td>" . date('d.m.Y H:i:s', $articles_data->article_date) . "</td>
					<td>" . nl2br($articles_data->article_description) . "</td>
					<td>" . getUserByID($articles_data->article_creator) . "</td>
				</tr>\r\n";
		}
		
		$text .= "\t\t\t</table>";
	}
	
	if($page->FindTag('inlinemenu')) {
		$page->ReplaceTagInTemplate('inlinemenu', '');
		$page->Template = preg_replace("/\<forinlinemenu\>(.+?)\<\/forinlinemenu\>/s", "", $page->Template);
	}
	$page->SetText($text);
	
	//
	// insert data into style
	//
	/*
	$page = str_replace("[title]", $title, $page);
	$page = str_replace("[text]", $text, $page);
	$page = str_replace("[menu]", generatemenu(@$internal_style, 1, $extern_page), $page);
	$page = str_replace("[menu2]", generatemenu(@$internal_style, 2, $extern_page), $page);
	if(isset($position))
		$page = str_replace("[position]", "<a href=\"article.php\">Artikel</a>-><a href=\"article.php?page_id=$article_data->article_id\">$position</a>", $page);
	else
		$page = str_replace("[position]", "<a href=\"article.php\">Artikel</a>", $page);
	$page = str_replace("[inlinemenu]", '', $page);
	$page = preg_replace("/\<forinlinemenu\>(.+?)\<\/forinlinemenu\>/s", "", $page);*/
	//
	// end
	//
	echo $page->OutputHTML();
?>