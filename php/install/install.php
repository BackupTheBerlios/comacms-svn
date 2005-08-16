<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"> 
<html>
<head>
	<title>Installation</title>

</head>

<body>
<?

$create = "DROP TABLE IF EXISTS " . $db_prefix. "config;
CREATE TABLE " . $db_prefix . "vars (
  config_name varchar(255) NOT NULL default '',
  config_value varchar(255) NOT NULL default '',
  PRIMARY KEY  (name)
);
DROP TABLE IF EXISTS ".$db_prefix."menue;
CREATE TABLE ".$db_prefix."menue (
  id int(10) unsigned NOT NULL auto_increment,
  link varchar(255) NOT NULL default '',
  text varchar(30) NOT NULL default '',
  new enum('yes','no') NOT NULL default 'no',
  orderid int(10) unsigned default NULL,
  menue_id int(10) NOT NULL default '1',
  PRIMARY KEY  (id)
);

DROP TABLE IF EXISTS " . $db_prefix . "pages_content;
CREATE TABLE `" . $db_prefix . "pages_content` (
  `page_id` int(10) NOT NULL auto_increment,
  `page_parent_id` int(10) NOT NULL default '0',
  `page_type` varchar(15) NOT NULL default '',
  `page_visible` enum('public','private','hidden','deleted') NOT NULL default 'public',
  `page_name` varchar(20) NOT NULL default '',
  `page_title` varchar(100) NOT NULL default '',
  `page_text` text NOT NULL,
  `page_lang` varchar(10) NOT NULL default '',
  `page_html` text,
  `page_creator` int(10) NOT NULL default '0',
  `page_created` varchar(20) NOT NULL default '0',
  PRIMARY KEY  (`page_id`)
);
DROP TABLE IF EXISTS ".$db_prefix."users;
CREATE TABLE `" . $db_prefix . "users` (
  `user_id` int(10) NOT NULL auto_increment,
  `user_name` varchar(30) NOT NULL default '',
  `user_showname` varchar(40) NOT NULL default '',
  `user_password` varchar(100) NOT NULL default '',
  `user_registerdate` varchar(20) default '0',
  `user_admin` enum('y','n') default 'n',
  `user_icq` varchar(12) default '0',
  `user_email` varchar(200) NOT NULL default '',
  PRIMARY KEY  (`user_id`)
);
DROP TABLE IF EXISTS ".$db_prefix."online;
REATE TABLE ".$db_prefix."online (
  online_id varchar(50) NOT NULL default '',
  ip varchar(16) default '0.0.0.0',
  lastaction varchar(20) default '0',
  page varchar(30) default NULL,
  lang varchar(5) default NULL,
  userid int(10) NOT NULL default '0',
  PRIMARY KEY  (online_id)
);
DROP TABLE IF EXISTS ".$db_prefix."guestbook;
CREATE TABLE ".$db_prefix."guestbook (
  id int(10) NOT NULL auto_increment,
  name varchar(20) default '0',
  mail varchar(50) default NULL,
  icq varchar(13) default NULL,
  homepage varchar(100) default NULL,
  message text,
  date varchar(20) default '0',
  ip varchar(16) default '0.0.0.0',
  host varchar(30) default '0',
  PRIMARY KEY  (id)
);
DROP TABLE IF EXISTS ".$db_prefix."news;
CREATE TABLE ".$db_prefix."news (
  id int(10) unsigned NOT NULL auto_increment,
  userid int(10) unsigned NOT NULL default '0',
  date int(20) NOT NULL default '0',
  title varchar(60) NOT NULL default '',
  text text NOT NULL,
  PRIMARY KEY  (id)
);
DROP TABLE IF EXISTS ".$db_prefix."sitedata_history;
CREATE TABLE ".$db_prefix."sitedata_history (
id INT( 10 ) NOT NULL AUTO_INCREMENT ,
type VARCHAR( 15 ) NOT NULL ,
name VARCHAR( 20 ) NOT NULL ,
title VARCHAR( 100 ) NOT NULL ,
text TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
lang VARCHAR( 5 ) NOT NULL ,
creator INT( 10 ) NOT NULL ,
PRIMARY KEY ( id )
);
DROP TABLE IF EXISTS ".$db_prefix."files;
CREATE TABLE " . $db_prefix . "files (
file_id INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
file_name VARCHAR( 255 ) NOT NULL ,
file_path VARCHAR( 255 ) NOT NULL ,
file_downloads INT( 10 ) DEFAULT '0' NOT NULL ,
file_size INT( 20 ) DEFAULT '0' NOT NULL ,
file_md5 VARCHAR( 150 ) NOT NULL ,
file_type VARCHAR( 100 ) NOT NULL ,
file_date INT( 25 ) DEFAULT '0' NOT NULL,
PRIMARY KEY ( file_id )
);
INSERT INTO ".$db_prefix."users (name, showname, password, registerdate, admin, icq)
VALUES ('".$admin_name."', '".$admin_showname."', '".md5($admin_password)."', '".mktime()."', 'y', '');
INSERT INTO ".$db_prefix."sitedata (name, title, text, lang, html, type)
VALUES ('home', 'Hauptseite', 'das ist die Homeseite', 'de', 'Das ist die Homeseite', 'text');
INSERT INTO ".$db_prefix."vars (name, value) VALUES ('style', 'clear');
INSERT INTO ".$db_prefix."vars (name, value) VALUES ('default_page', 'home');
INSERT INTO ".$db_prefix."menue (link, text, new, orderid) VALUES ('l:home', 'Home', 'no', 0)";
if($admin_name == "" || $admin_showname == "" || $admin_password == "")
	die("Die Angaben zum Adminaccount sind unvollständig..");

if($admin_password != $admin_password2)
	die("Das passwort wurde nicht korrekt wiederholt");

$connection = mysql_connect($db_server, $db_user, $db_password) or die(mysql_error());
mysql_select_db($db_database, $connection) or die(mysql_error());
$queries = explode(";",$create);
foreach($queries as $query){
	if($query != ""){
		mysql_query($query, $connection) or die(mysql_error());
	}
}
mysql_close($connection);
$config_data = "<?php\n";
$config_data .= '$d_server = \'' . $db_server.'\';' . "\n";
$config_data .= '$d_user   = \'' . $db_user . '\';' . "\n";
$config_data .= '$d_pw     = \'' . $db_password . '\';' . "\n";
$config_data .= '$d_base   = \'' . $db_database . '\';' . "\n";
$config_data .= '$d_pre = \'' . $de_prefix . '\';' . "\n\n";
$config_data .= 'define(\'COMACMS_INSTALLED\', true);' . "\n";
$config_data .= '?>';

$fp = @fopen("../config.php", 'w');
$result = @fputs($fp, $config_data, strlen($config_data));
@fclose($fp);
?>
</body>
</html>