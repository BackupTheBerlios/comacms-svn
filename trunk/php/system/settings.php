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

	setSetting("default_page", "Standart Startseite", "Auf diese Seite wird jeder besucher geleitet, der keine Seite angegeben hat.", "home");
	setSetting("pagename", "Seitenname", "Hier wird der Name der Seite definiert.", "ComaCMS");
	setSetting("thumbnailfolder", "Verzeichnis für erstellte Bilder", "In dem hier angegebenen Verzeichnis werden die automatisch erstellten Bilder gespeichert. (Für diesen Ordner sind Schreibrechte notwendig)", "data/thumbnails/");
	setSetting("news_date_format", "Datums-Fotmat für News", "Dies ist das Format, in dem das Datum der News angezeigt wird.", "d.m.Y");
	setSetting("news_time_format", "Uhrzeit-Fotmat für News", "Dies ist das Format, in dem die Uhrzeit der News angezeigt wird.", "H:i:s");
	setSetting("news_display_count", "Anzahl angezeigter News", "Hier kann man angeben wieviele News angezeigt werden sollen.", "6");
?>