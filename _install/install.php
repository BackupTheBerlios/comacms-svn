<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"> 
<html>
<head>
	<title>Installation</title>

</head>

<body>
<?

$create = "DROP TABLE IF EXISTS ".$db_prefix."vars;
CREATE TABLE ".$db_prefix."vars (
  name varchar(255) NOT NULL default '',
  value varchar(255) NOT NULL default '',
  PRIMARY KEY  (name)
);
DROP TABLE IF EXISTS ".$db_prefix."menue;
CREATE TABLE ".$db_prefix."menue (
  id int(10) unsigned NOT NULL auto_increment,
  link varchar(255) NOT NULL default '',
  text varchar(30) NOT NULL default '',
  new enum('yes','no') NOT NULL default 'no',
  orderid int(10) unsigned default NULL,
  PRIMARY KEY  (id)
);
DROP TABLE IF EXISTS ".$db_prefix."sitedata;
CREATE TABLE ".$db_prefix."sitedata (
  id int(10) NOT NULL auto_increment,
  name varchar(20) NOT NULL default '',
  title varchar(100) NOT NULL default '',
  text text NOT NULL,
  lang varchar(10) NOT NULL default '',
  html text,
  PRIMARY KEY  (id)
);
DROP TABLE IF EXISTS ".$db_prefix."users;
CREATE TABLE ".$db_prefix."users (
  id int(10) NOT NULL auto_increment,
  name varchar(30) NOT NULL default '',
  showname varchar(40) NOT NULL default '',
  password varchar(60) NOT NULL default '',
  registerdate varchar(20) default '0',
  admin enum('y','n') default 'n',
  icq varchar(12) default '0',
  PRIMARY KEY  (id)
);
DROP TABLE IF EXISTS ".$db_prefix."online;
CREATE TABLE ".$db_prefix."online (
  ip varchar(16) default '0.0.0.0',
  lastaction varchar(20) default '0',
  page varchar(30) default NULL,
  lang varchar(5) default NULL
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
INSERT INTO cms_users (name, showname, password, registerdate, admin, icq)
VALUES ('".$admin_name."', '".$admin_showname."', '".md5($admin_passsword)."', '".mktime()."', 'y', '');
INSERT INTO ".$db_prefix."sitedata (name, title, text, lang, html)
VALUES ('home', 'Hauptseite', 'das ist die Homeseite', 'de', 'das ist die Homeseite');
INSERT INTO ".$db_prefix."vars (name, value) VALUES ('style', 'clear');
INSERT INTO ".$db_prefix."vars (name, value) VALUES ('default_site', 'home');
INSERT INTO cms_menue (link, text, new, orderid) VALUES ('l:home', 'Home', 'y', 0)";
if($admin_name == "" || $admin_showname == "" || $admin_password == "")
{
die("Die Angaben zum Adminaccount sind unvollständig..");
}
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
//define('PHPBB_INSTALLED', true);
$config_data = "<?php\n";
$config_data .= "\$d_server = \"".$db_server."\";\n";
$config_data .= "\$d_user   = \"".$db_user."\";\n";
$config_data .= "\$d_pw     = \"".$db_password."\";\n";
$config_data .= "\$d_base   = \"".$db_database."\";\n";
$config_data .= "\$d_pre    = \"".$db_prefix."\";\n";
$config_data .= "\n\ndefine(\"CMS_INSTALLED\", true);\n";
$config_data .= "?".">";

if (!($fp = @fopen("../config.php", 'w')))
{
}
$result = @fputs($fp, $config_data, strlen($config_data));
@fclose($fp);

?>


</body>
</html>