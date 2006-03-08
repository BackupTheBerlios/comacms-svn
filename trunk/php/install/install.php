<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: install.php					#
 # created		: 2005-06-17					#
 # copyright		: (C) 2005 The ComaCMS-Team			#
 # email		: comacms@williblau.de				#
 #----------------------------------------------------------------------#
 # This program is free software; you can redistribute it and/or modify	#
 # it under the terms of the GNU General Public License as published by	#
 # the Free Software Foundation; either version 2 of the License, or	#
 # (at your option) any later version.					#
 #----------------------------------------------------------------------#

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"> 
<html>
<head>
	<title>Installation</title>

</head>

<body>
<?

	$create = "DROP TABLE IF EXISTS " . $db_prefix . "articles;
		CREATE TABLE " . $db_prefix . "articles (
			article_id int(20) unsigned NOT NULL auto_increment,
			article_title varchar(100) NOT NULL default '',
			article_description varchar(200) NOT NULL default '',
			article_image varchar(200) NOT NULL default '',
			article_text text NOT NULL,
			article_html text,
			article_creator int(10) NOT NULL default '0',
			article_date int(20) unsigned default NULL,
			PRIMARY KEY  (article_id)
		);
		DROP TABLE IF EXISTS " . $db_prefix . "config;
		CREATE TABLE " . $db_prefix . "config (
			config_name varchar(255) NOT NULL default '',
			config_value varchar(255) NOT NULL default '',
			PRIMARY KEY  (config_name)
		);
		DROP TABLE IF EXISTS " . $db_prefix . "dates;
		CREATE TABLE " . $db_prefix . "dates (
			date_id int(10) unsigned NOT NULL auto_increment,
			date_date int(20) unsigned default NULL,
			date_topic varchar(150) NOT NULL default '',
			date_place varchar(60) NOT NULL default '',
			date_creator int(10) unsigned default NULL,
			PRIMARY KEY  (date_id)
		);
		DROP TABLE IF EXISTS " . $db_prefix . "files;
		CREATE TABLE " . $db_prefix . "files (
			file_id int(10) unsigned NOT NULL auto_increment,
			file_name varchar(255) NOT NULL default '',
			file_path varchar(255) NOT NULL default '',
			file_downloads int(10) NOT NULL default '0',
			file_size int(20) NOT NULL default '0',
			file_md5 varchar(150) NOT NULL default '',
			file_type varchar(100) NOT NULL default '',
			file_date int(25) NOT NULL default '0',
			PRIMARY KEY  (file_id)
		);
		DROP TABLE IF EXISTS " . $db_prefix . "guestbook;
		CREATE TABLE " . $db_prefix . "guestbook (
			id int(10) NOT NULL auto_increment,
			name varchar(20) default '0',
			mail varchar(50) default NULL,
			icq varchar(13) default NULL,
			homepage varchar(100) default NULL,
			message text,
			`date` varchar(20) default '0',
			ip varchar(16) default '0.0.0.0',
			host varchar(30) default '0',
			PRIMARY KEY  (id)
		);
		DROP TABLE IF EXISTS " . $db_prefix . "inlinemenu;
		CREATE TABLE " . $db_prefix . "inlinemenu (
			page_id int(10) unsigned NOT NULL default '0',
			inlinemenu_image varchar(150) NOT NULL default '',
			inlinemenu_image_thumb varchar(255) NOT NULL default '',
			inlinemenu_html text NOT NULL,
			inlinemenu_image_title varchar(100) NOT NULL default '',
			PRIMARY KEY  (page_id)
		);
		DROP TABLE IF EXISTS " . $db_prefix . "inlinemenu_entries;
		CREATE TABLE " . $db_prefix . "inlinemenu_entries (
			inlineentry_id int(10) unsigned NOT NULL auto_increment,
			inlineentry_sortid int(10) NOT NULL default '0',
			inlineentry_page_id int(10) NOT NULL default '0',
			inlineentry_type enum('link','download','text','intern') NOT NULL default 'link',
			inlineentry_text text NOT NULL,
			inlineentry_link varchar(255) NOT NULL default '',
  			PRIMARY KEY  (inlineentry_id)
		);
		DROP TABLE IF EXISTS " . $db_prefix . "menu;
		CREATE TABLE " . $db_prefix . "menu (
			menu_id int(10) unsigned NOT NULL auto_increment,
			menu_link varchar(255) NOT NULL default '',
  			menu_text varchar(30) NOT NULL default '',
  			menu_new enum('yes','no') NOT NULL default 'no',
			menu_orderid int(10) unsigned default NULL,
			menu_menuid int(10) NOT NULL default '1',
			menu_page_id int(10) NOT NULL default '0',
			PRIMARY KEY  (menu_id),
			KEY `menu_page_id` (menu_page_id)
		);
		DROP TABLE IF EXISTS " . $db_prefix . "news;
		CREATE TABLE " . $db_prefix . "news (
			id int(10) unsigned NOT NULL auto_increment,
			userid int(10) unsigned NOT NULL default '0',
			`date` int(20) NOT NULL default '0',
			title varchar(60) NOT NULL default '',
			`text` text NOT NULL,
			PRIMARY KEY  (id)
		);
		DROP TABLE IF EXISTS " . $db_prefix . "online;
		CREATE TABLE " . $db_prefix . "online (
			online_id varchar(100) NOT NULL default '',
			ip varchar(16) default '0.0.0.0',
			lastaction varchar(20) default '0',
			page varchar(30) default NULL,
			lang varchar(5) default NULL,
			userid int(10) NOT NULL default '0',
			host varchar(110) NOT NULL default '',
			PRIMARY KEY  (online_id)
		);
		DROP TABLE IF EXISTS " . $db_prefix . "pages;
		CREATE TABLE " . $db_prefix . "pages (
			page_id int(10) unsigned NOT NULL auto_increment,
			page_name varchar(50) NOT NULL default '',
			page_type enum('text','gallery') NOT NULL default 'text',
			page_title varchar(120) NOT NULL default '',
			page_parent_id int(10) NOT NULL default '0',
			page_creator int(15) NOT NULL default '0',
			page_date int(20) NOT NULL default '0',
			page_access enum('public','private','hidden','deleted') NOT NULL default 'public',
			page_lang varchar(5) NOT NULL default '',
			page_edit_comment varchar(100) NOT NULL default '',
			PRIMARY KEY  (page_id)
		);
		DROP TABLE IF EXISTS " . $db_prefix . "pages_text;
		CREATE TABLE " . $db_prefix . "pages_text (
			page_id int(10) NOT NULL default '0',
			text_page_text text NOT NULL,
			text_page_html text NOT NULL,
			PRIMARY KEY  (page_id)
		);
		DROP TABLE IF EXISTS " . $db_prefix . "pages_history;
		CREATE TABLE " . $db_prefix . "pages_history (
  			id int(10) NOT NULL auto_increment,
  			page_id int(10) NOT NULL default '0',
			page_type longtext NOT NULL,
			page_name varchar(20) NOT NULL default '',
			page_title varchar(100) NOT NULL default '',
			page_parent_id int(10) NOT NULL default '0',
			page_lang varchar(5) NOT NULL default '',
			page_creator int(10) NOT NULL default '0',
			page_date int(20) NOT NULL default '0',
			page_edit_comment varchar(100) NOT NULL default '',
			PRIMARY KEY  (id)
		);
		DROP TABLE IF EXISTS " . $db_prefix . "pages_text_history;
		CREATE TABLE " . $db_prefix . "pages_text_history (
			id int(10) NOT NULL auto_increment,
			page_id int(10) NOT NULL default '0',
			text_page_text text NOT NULL,
			PRIMARY KEY  (id)
		);
		DROP TABLE IF EXISTS " . $db_prefix . "sitedata_history;
		CREATE TABLE " . $db_prefix . "sitedata_history (
			id int(10) NOT NULL auto_increment,
			`type` varchar(15) NOT NULL default '',
			name varchar(20) NOT NULL default '',
			title varchar(100) NOT NULL default '',
			`text` text NOT NULL,
			lang varchar(5) NOT NULL default '',
			creator int(10) NOT NULL default '0',
			`date` varchar(20) NOT NULL default '0',
			PRIMARY KEY  (id)
		);
		DROP TABLE IF EXISTS " . $db_prefix . "users;
		CREATE TABLE " . $db_prefix . "users (
			user_id int(10) NOT NULL auto_increment,
			user_name varchar(30) NOT NULL default '',
			user_showname varchar(40) NOT NULL default '',
			user_password varchar(100) NOT NULL default '',
			user_registerdate varchar(20) default '0',
			user_admin enum('y','n') default 'n',
			user_icq varchar(12) default '0',
			user_email varchar(200) NOT NULL default '',
			PRIMARY KEY  (user_id)
		);
		DROP TABLE IF EXISTS " . $db_prefix . "pages_gallery;
		CREATE TABLE " . $db_prefix . "pages_gallery (
			page_id INT( 10 ) NOT NULL ,
			gallery_id INT( 10 ) NOT NULL AUTO_INCREMENT ,
			PRIMARY KEY ( gallery_id ) ,
			INDEX ( page_id )
		);
		DROP TABLE IF EXISTS " . $db_prefix . "gallery;
		CREATE TABLE " . $db_prefix . "gallery (
			gallery_id INT( 10 ) NOT NULL ,
			gallery_file_id MEDIUMINT( 10 ) NOT NULL ,
			gallery_orderid INT( 10 ) DEFAULT '0' NOT NULL ,
			gallery_image_thumbnail VARCHAR( 255 ) NOT NULL ,
			gallery_image VARCHAR( 255 ) NOT NULL
		);
		DROP TABLE IF EXISTS " . $db_prefix . "smilies;
		CREATE TABLE " . $db_prefix . "smilies (
			smilie_id int(10) NOT NULL auto_increment,
			smilie_path varchar(250) NOT NULL default '',
			smilie_text varchar(100) NOT NULL default '',
			smilie_title varchar(250) NOT NULL default '',
			PRIMARY KEY  (smilie_id),
			UNIQUE KEY smilie_text (smilie_text)
		);
		DROP TABLE IF EXISTS " . $db_prefix . "auth;
        	CREATE TABLE " . $db_prefix . "auth (
			auth_group_id INT( 20 ) ,
			auth_user_id INT( 20 ) ,
			auth_page_id INT( 20 ) DEFAULT '0' NOT NULL ,
			auth_view TINYINT( 1 ) DEFAULT '1' NOT NULL ,
			auth_edit TINYINT( 1 ) DEFAULT '0' NOT NULL ,
			auth_delete TINYINT( 1 ) DEFAULT '0' NOT NULL ,
			auth_new_sub TINYINT( 1 ) DEFAULT '0' NOT NULL
		);
		DROP TABLE IF EXISTS " . $db_prefix . "groups;
		CREATE TABLE " . $db_prefix . "groups (
			group_id int(20) NOT NULL auto_increment,
			group_name varchar(40) NOT NULL default '',
			group_description text NOT NULL,
			group_manager int(20) NOT NULL default '0',
			PRIMARY KEY  (group_id),
			UNIQUE KEY group_name (group_name)
		);
		DROP TABLE IF EXISTS " . $db_prefix . "roup_users;
		CREATE TABLE " . $db_prefix . "group_users (
			group_id INT( 20 ) NOT NULL ,
			user_id INT( 20 ) NOT NULL
		);			
		INSERT INTO " . $db_prefix . "users (user_name, user_showname, user_password, user_registerdate, user_admin, user_icq)
		VALUES ('$admin_name', '$admin_showname', '" . md5($admin_password) . "', '" . mktime() . "', 'y', '');
		INSERT INTO " . $db_prefix . "config (config_name, config_value)
		VALUES ('style', 'clear');
		INSERT INTO " . $db_prefix . "config (config_name, config_value)
		VALUES ('default_page', '1');
		INSERT INTO " . $db_prefix . "config (config_name, config_value)
		VALUES ('install_date', '" . mktime() . "');
		INSERT INTO " . $db_prefix . "config (config_name, config_value)
		VALUES ('pagename', 'ComaCMS');
		INSERT INTO " . $db_prefix . "config (config_name, config_value)
		VALUES ('news_date_format', 'd.m.Y');
		INSERT INTO " . $db_prefix . "config (config_name, config_value)
		VALUES ('news_time_format', 'H:i:s');
		INSERT INTO " . $db_prefix . "config (config_name, config_value)
		VALUES ('news_display_count', '6');
		INSERT INTO " . $db_prefix . "menu (menu_link, menu_text, menu_new, menu_orderid, menu_menuid, menu_page_id)
		VALUES ('l:home', 'Home', 'no', 0, 1, 1);";
		//TODO: make sure that the id of the default page is everytime the right one 

	if($admin_name == "" || $admin_showname == "" || $admin_password == "")
		die("Die Angaben zum Adminaccount sind unvollständig..");

	if($admin_password != $admin_password2)
		die("Das Passwort wurde nicht korrekt wiederholt");

	$connection = mysql_connect($db_server, $db_user, $db_password) or die(mysql_error());
	mysql_select_db($db_database, $connection) or die(mysql_error());
	$queries = explode(";",$create);
	foreach($queries as $query){
		if($query != ""){
			mysql_query($query, $connection) or die(mysql_error());
		}
	}
	
	$sql = "INSERT INTO " . $db_prefix . "pages (page_lang, page_access, page_name, page_title, page_parent_id, page_creator, page_type, page_date, page_edit_comment)
		VALUES('de', 'public', 'home', 'Hauptseite', 0, 1, 'text', " . mktime() . ", 'Installed Home-page')";
	mysql_query($sql, $connection) or die(mysql_error());
	$lastid =  mysql_insert_id();
	$sql = "INSERT INTO " . $db_prefix . "pages_text (page_id, text_page_text,text_page_html)
		VALUES ($lastid, 'Willkommen auf der Hauptseite', 'Willkommen auf der Hauptseite')";
	mysql_query($sql, $connection) or die(mysql_error());
	$sql = "INSERT INTO " . $db_prefix . "menu (menu_link, menu_text, menu_new, menu_orderid, menu_menuid)
		VALUES ('l:home', 'Home', 'no', 0, 1);";

	mysql_close($connection);
	$config_data = "<?php\n";
	$config_data .= '$d_server = \'' . $db_server.'\';' . "\r\n";
	$config_data .= '$d_user   = \'' . $db_user . '\';' . "\r\n";
	$config_data .= '$d_pw     = \'' . $db_password . '\';' . " \r\n";
	$config_data .= '$d_base   = \'' . $db_database . '\';' . "\r\n";
	$config_data .= '$d_pre = \'' . $db_prefix . '\';' . " \r\n\r\n";
	$config_data .= 'define(\'COMACMS_INSTALLED\', true);' . "\r\n";
	$config_data .= '?>';

	$fp = @fopen("../config.php", 'w');
	$result = @fputs($fp, $config_data, strlen($config_data));
	@fclose($fp);
?>
</body>
</html>