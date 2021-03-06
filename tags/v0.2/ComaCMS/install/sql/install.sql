DROP TABLE IF EXISTS {DB_PREFIX}articles;
		CREATE TABLE {DB_PREFIX}articles (
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
		DROP TABLE IF EXISTS {DB_PREFIX}config;
		CREATE TABLE {DB_PREFIX}config (
			config_name varchar(255) NOT NULL default '',
			config_value varchar(255) NOT NULL default '',
			PRIMARY KEY  (config_name)
		);
		DROP TABLE IF EXISTS {DB_PREFIX}dates;
		CREATE TABLE {DB_PREFIX}dates (
			date_id int(10) unsigned NOT NULL auto_increment,
			date_date int(20) unsigned default NULL,
			date_topic varchar(150) NOT NULL default '',
			date_location varchar(60) NOT NULL default '',
			date_creator int(10) unsigned default NULL,
			PRIMARY KEY  (date_id)
		);
		DROP TABLE IF EXISTS {DB_PREFIX}files;
		CREATE TABLE {DB_PREFIX}files (
			file_id int(10) unsigned NOT NULL auto_increment,
			file_name varchar(255) NOT NULL default '',
			file_path varchar(255) NOT NULL default '',
			file_downloads int(10) NOT NULL default '0',
			file_size int(20) NOT NULL default '0',
			file_md5 varchar(150) NOT NULL default '',
			file_type varchar(100) NOT NULL default '',
			file_date int(25) NOT NULL default '0',
			file_creator INT( 10 ) DEFAULT '0' NOT NULL,
			PRIMARY KEY  (file_id)
		);
		DROP TABLE IF EXISTS {DB_PREFIX}guestbook;
		CREATE TABLE {DB_PREFIX}guestbook (
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
		DROP TABLE IF EXISTS {DB_PREFIX}inlinemenu;
		CREATE TABLE {DB_PREFIX}inlinemenu (
			page_id int(10) unsigned NOT NULL default '0',
			inlinemenu_image varchar(150) NOT NULL default '',
			inlinemenu_image_thumb varchar(255) NOT NULL default '',
			inlinemenu_html text NOT NULL,
			inlinemenu_image_title varchar(100) NOT NULL default '',
			PRIMARY KEY  (page_id)
		);
		DROP TABLE IF EXISTS {DB_PREFIX}inlinemenu_entries;
		CREATE TABLE {DB_PREFIX}inlinemenu_entries (
			inlineentry_id int(10) unsigned NOT NULL auto_increment,
			inlineentry_sortid int(10) NOT NULL default '0',
			inlineentry_page_id int(10) NOT NULL default '0',
			inlineentry_type enum('link','download','text','intern') NOT NULL default 'link',
			inlineentry_text text NOT NULL,
			inlineentry_link varchar(255) NOT NULL default '',
  			PRIMARY KEY  (inlineentry_id)
		);
		DROP TABLE IF EXISTS {DB_PREFIX}menu;
		CREATE TABLE {DB_PREFIX}menu (
			menu_id int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
			menu_name VARCHAR( 255 ) NOT NULL DEFAULT '',
			menu_title VARCHAR( 255 ) NOT NULL DEFAULT '',
			PRIMARY KEY  (menu_id)
		);
		DROP TABLE IF EXISTS {DB_PREFIX}menu_entries;
		CREATE TABLE {DB_PREFIX}menu_entries (
			menu_entries_id INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
			menu_entries_link VARCHAR( 255 ) NOT NULL DEFAULT '',
			menu_entries_title VARCHAR( 30 ) NOT NULL DEFAULT '',
			menu_entries_new ENUM( 'yes', 'no' ) NOT NULL DEFAULT 'no',
			menu_entries_css_id VARCHAR( 50 ) NOT NULL DEFAULT '',
			menu_entries_orderid INT( 10 ) UNSIGNED NULL,
			menu_entries_menuid INT( 10 ) NOT NULL DEFAULT '1',
			menu_entries_page_id INT( 10 ) UNSIGNED NULL
		);
		DROP TABLE IF EXISTS {DB_PREFIX}news;
		CREATE TABLE {DB_PREFIX}news (
			id int(10) unsigned NOT NULL auto_increment,
			userid int(10) unsigned NOT NULL default '0',
			date int(20) NOT NULL default '0',
			title varchar(60) NOT NULL default '',
			text text NOT NULL,
			PRIMARY KEY  (id)
		);
		DROP TABLE IF EXISTS {DB_PREFIX}online;
		CREATE TABLE {DB_PREFIX}online (
			online_id varchar(100) NOT NULL default '',
			online_ip varchar(16) default '0.0.0.0',
			online_lastaction varchar(20) default '0',
			online_page varchar(30) default NULL,
			online_lang varchar(5) default NULL,
			online_userid int(10) NOT NULL default '0',
			online_host varchar(110) NOT NULL default '',
			online_loggedon ENUM( 'yes', 'no' ) DEFAULT 'no' NOT NULL,
			PRIMARY KEY  (online_id)
		);
		DROP TABLE IF EXISTS {DB_PREFIX}pages;
		CREATE TABLE {DB_PREFIX}pages (
			page_id int(10) unsigned NOT NULL auto_increment,
			page_name varchar(250) NOT NULL default '',
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
		DROP TABLE IF EXISTS {DB_PREFIX}pages_text;
		CREATE TABLE {DB_PREFIX}pages_text (
			page_id int(10) NOT NULL default '0',
			text_page_text text NOT NULL,
			text_page_html text NOT NULL,
			PRIMARY KEY  (page_id)
		);
		DROP TABLE IF EXISTS {DB_PREFIX}pages_history;
		CREATE TABLE {DB_PREFIX}pages_history (
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
		DROP TABLE IF EXISTS {DB_PREFIX}pages_text_history;
		CREATE TABLE {DB_PREFIX}pages_text_history (
			id int(10) NOT NULL auto_increment,
			page_id int(10) NOT NULL default '0',
			text_page_text text NOT NULL,
			PRIMARY KEY  (id)
		);
		DROP TABLE IF EXISTS {DB_PREFIX}sitedata_history;
		CREATE TABLE {DB_PREFIX}sitedata_history (
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
		DROP TABLE IF EXISTS {DB_PREFIX}users;
		CREATE TABLE {DB_PREFIX}users (
			user_id int(10) NOT NULL auto_increment,
			user_name varchar(30) NOT NULL default '',
			user_showname varchar(40) NOT NULL default '',
			user_password varchar(100) NOT NULL default '',
			user_registerdate varchar(20) default '0',
			user_admin enum('y','n') default 'n',
			user_icq varchar(12) default '0',
			user_email varchar(200) NOT NULL default '',
			user_activated bool NULL default '1',
			user_activationcode varchar(32) NULL,
			PRIMARY KEY  (user_id)
		);
		DROP TABLE IF EXISTS {DB_PREFIX}pages_gallery;
		CREATE TABLE {DB_PREFIX}pages_gallery (
			page_id INT( 10 ) NOT NULL ,
			gallery_id INT( 10 ) NOT NULL AUTO_INCREMENT ,
			PRIMARY KEY ( gallery_id ) ,
			INDEX ( page_id )
		);
		DROP TABLE IF EXISTS {DB_PREFIX}gallery;
		CREATE TABLE {DB_PREFIX}gallery (
			gallery_id INT( 10 ) NOT NULL ,
			gallery_file_id MEDIUMINT( 10 ) NOT NULL ,
			gallery_orderid INT( 10 ) DEFAULT '0' NOT NULL ,
			gallery_image_thumbnail VARCHAR( 255 ) NOT NULL ,
			gallery_image VARCHAR( 255 ) NOT NULL,
			gallery_description TEXT NOT NULL
		);
		DROP TABLE IF EXISTS {DB_PREFIX}smilies;
		CREATE TABLE {DB_PREFIX}smilies (
			smilie_id int(10) NOT NULL auto_increment,
			smilie_path varchar(250) NOT NULL default '',
			smilie_text varchar(100) NOT NULL default '',
			smilie_title varchar(250) NOT NULL default '',
			PRIMARY KEY  (smilie_id),
			UNIQUE KEY smilie_text (smilie_text)
		);
		DROP TABLE IF EXISTS {DB_PREFIX}auth;
        	CREATE TABLE {DB_PREFIX}auth (
			auth_group_id INT( 20 ) ,
			auth_user_id INT( 20 ) ,
			auth_page_id INT( 20 ) DEFAULT '0' NOT NULL ,
			auth_view TINYINT( 1 ) DEFAULT '1' NOT NULL ,
			auth_edit TINYINT( 1 ) DEFAULT '0' NOT NULL ,
			auth_delete TINYINT( 1 ) DEFAULT '0' NOT NULL ,
			auth_new_sub TINYINT( 1 ) DEFAULT '0' NOT NULL
		);
		DROP TABLE IF EXISTS {DB_PREFIX}groups;
		CREATE TABLE {DB_PREFIX}groups (
			group_id int(20) NOT NULL auto_increment,
			group_name varchar(40) NOT NULL default '',
			group_description text NOT NULL,
			group_manager int(20) NOT NULL default '0',
			PRIMARY KEY  (group_id),
			UNIQUE KEY group_name (group_name)
		);
		DROP TABLE IF EXISTS {DB_PREFIX}group_users;
		CREATE TABLE {DB_PREFIX}group_users (
			group_id INT( 20 ) NOT NULL ,
			user_id INT( 20 ) NOT NULL
		);
		
		INSERT INTO {DB_PREFIX}config (config_name, config_value)
		VALUES ('style', 'clear');
		INSERT INTO {DB_PREFIX}config (config_name, config_value)
		VALUES ('default_page', '1');
		INSERT INTO {DB_PREFIX}config (config_name, config_value)
		VALUES ('pagename', 'ComaCMS');
		INSERT INTO {DB_PREFIX}config (config_name, config_value)
		VALUES ('news_date_format', 'd.m.Y');
		INSERT INTO {DB_PREFIX}config (config_name, config_value)
		VALUES ('news_time_format', 'H:i:s');
		INSERT INTO {DB_PREFIX}config (config_name, config_value)
		VALUES ('news_display_count', '6');
		INSERT INTO {DB_PREFIX}menu (menu_name, menu_title)
		VALUES ('DEFAULT', 'DEFAULT');
		INSERT INTO {DB_PREFIX}menu_entries (menu_entries_link, menu_entries_title, menu_entries_new, menu_entries_orderid, menu_entries_menuid, menu_entries_page_id)
		VALUES ('l:home', 'Home', 'no', 0, 1, 1);