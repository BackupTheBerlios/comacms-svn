<?php
/*****************************************************************************
 *
 *  file		: gallery.php
 *  created		: 2005-06-24
 *  copyright		: (C) 2005 The Comasy-Team
 *  email		: comasy@williblau.de
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
	@include_once('config.php');
	if(file_exists('./install/') && !file_exists('./.svn/'))
	{
		if(defined('CMS_INSTALLED'))
			die('Please remove the install-folder id would be better.');
		else
			header('location: install/install.html');
	}

	include('functions.php');
	_start();
	//
	// load vars
	//
	$sql = "SELECT *
		FROM " . $d_pre . "vars";
	$var_result = db_result($sql);
	while($var_data = mysql_fetch_object($var_result))
	{
		$_N_ = "internal_" . $var_data->name;
		$$_N_ = $var_data->value;
	}
	//end
	
	if(@$_GET['site'])
	{
		$_site = $_GET['site'];
	}
	else
	{
		$_site = @$_POST['site'];
	}
	if($_site == '')
	{
		$_site = @$internal_default_site;
	}
	if($_site == '')
	{
		$_site = 'home';
	}
	if(@$internal_style == '')
	{
		$internal_style = 'clear';
	}

	$title = @$internal_gallery_pre . '[galleryname]' . @$inertal_gallery_past;


	$menue = generatemenue(@$internal_style, $_site);


	//load style
	$stylefile = './styles/' . $internal_style . '/mainpage.php';
	$_file = fopen($stylefile, 'r');
	$page = fread($_file, filesize($stylefile));
	
	$page = str_replace('[title]', $title, $page);
	$page = str_replace('[text]', $text, $page);
	$page = str_replace('[menue]', $menue, $page);
	
	echo $page;
	_end();
?>