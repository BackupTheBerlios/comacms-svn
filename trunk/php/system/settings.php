<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: settings.php					#
 # created		: 2005-08-01					#
 # copyright		: (C) 2005 The ComaCMS-Team			#
 # email		: comacms@williblau.de				#
 #----------------------------------------------------------------------#
 # This program is free software; you can redistribute it and/or modify	#
 # it under the terms of the GNU General Public License as published by	#
 # the Free Software Foundation; either version 2 of the License, or	#
 # (at your option) any later version.					#
 #----------------------------------------------------------------------#
	
	global $admin_lang;
	
	Preferences::SetSetting("default_page", "Startseite", "Auf diese Seite wird jeder Besucher geleitet, der keine Seite angegeben hat.", "Main", '1', 'page_select');
	Preferences::SetSetting("pagename", "Seitenname", "Hier wird der Name der Seite definiert.", "Main", "ComaCMS");
	Preferences::SetSetting("thumbnailfolder", "Verzeichnis für erstellte Bilder", "In dem hier angegebenen Verzeichnis werden die automatisch erstellten Bilder gespeichert. (Für diesen Ordner sind Schreibrechte notwendig)", "Main", "data/thumbnails/");
	// News:
	Preferences::SetSetting("news_title", "Überschrift", "Eine kleine Überschrift für den News-Block", 'News', 'News', 'string0');
	Preferences::SetSetting('news_display_author', 'Autor anzeigen', 'Soll der Autor in den News-Einträgen erscheinen?', 'News', '1', 'bool');
	Preferences::SetSetting("news_date_format", "Datums-Fotmat für News", "Dies ist das Format, in dem das Datum der News angezeigt wird.", "News", "d.m.Y", 'string0');
	Preferences::SetSetting("news_time_format", "Uhrzeit-Fotmat für News", "Dies ist das Format, in dem die Uhrzeit der News angezeigt wird.", "News", "H:i:s", 'string0');
	Preferences::SetSetting("news_display_count", "Anzahl angezeigter News", "Hier kann man angeben wieviele News angezeigt werden sollen.", "News", '6', 'integer');
	// Articles:
	Preferences::SetSetting("articles_title", "Überschrift", "Eine kleine Überschrift für den Artikel-Block", $admin_lang['articles'], $admin_lang['articles'], 'string0');
	Preferences::SetSetting('articles_display_author', 'Autor anzeigen', 'Soll der Autor in den Artikel-Vorschau erscheinen?', $admin_lang['articles'], '1', 'bool');
	Preferences::SetSetting("articles_date_format", "Datums-Fotmat für Artikel", "Dies ist das Format, in dem das Datum des Artikels angezeigt wird.", $admin_lang['articles'], "d.m.Y", 'string0');
	Preferences::SetSetting("articles_time_format", "Uhrzeit-Fotmat für Artikel", "Dies ist das Format, in dem die Uhrzeit des Artikels angezeigt wird.", $admin_lang['articles'], "H:i:s", 'string0');
	Preferences::SetSetting("articles_display_count", "Anzahl angezeigter Artikel", "Hier kann man angeben wieviele Artikelvoransichten angezeigt werden sollen.", $admin_lang['articles'], '6', 'integer');
?>