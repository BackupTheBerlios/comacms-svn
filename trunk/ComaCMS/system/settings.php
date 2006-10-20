<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : settings.php
 # created              : 2005-08-01
 # copyright            : (C) 2005-2006 The ComaCMS-Team
 # email                : comacms@williblau.de
 #----------------------------------------------------------------------
 # This program is free software; you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation; either version 2 of the License, or
 # (at your option) any later version.
 #----------------------------------------------------------------------
	
	global $admin_lang;
	
	Preferences::SetSetting("default_page", "Startseite", "Auf diese Seite wird jeder Besucher geleitet, der keine Seite angegeben hat.", "Main", '1', 'page_select');
	Preferences::SetSetting("pagename", "Seitenname", "Hier wird der Name der Seite definiert.", "Main", "ComaCMS");
	Preferences::SetSetting('keywords', 'Keywords', 'Insert here keywords relating to the content of your page. (Separated with commas)', 'Main', 'ComaCms,Content Management System,Open Source');
	Preferences::SetSetting("thumbnailfolder", "Verzeichnis f&uuml;r erstellte Bilder", "In dem hier angegebenen Verzeichnis werden die automatisch erstellten Bilder gespeichert. (F&uuml;r diesen Ordner sind Schreibrechte notwendig)", "Main", "data/thumbnails/");
	//Preferences::SetSetting('advanced_menueditor', 'Use advanced Menueditor', 'Do you want to use an advanced Menuedior?', 'Menueditor', 0, 'bool');
	Preferences::SetSetting("show_inlinemenu_entries", "Zusatzmen&uuml; anzeigen", "Sollen Zusatzmen&uuml;einträge im Menüeditor angezeigt werden?", "Menueditor", "0", "bool");
	
?>