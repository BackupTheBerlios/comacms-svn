<?php
/**
 * @package ComaCMS
 * @subpackage News
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : news_settings.php
 # created              : 2006-02-18
 # copyright            : (C) 2005-2006 The ComaCMS-Team
 # email                : comacms@williblau.de
 #----------------------------------------------------------------------
 # This program is free software; you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation; either version 2 of the License, or
 # (at your option) any later version.
 #----------------------------------------------------------------------
 
	Preferences::SetSetting('news_title', '&Uuml;berschrift', 'Eine kleine &Uuml;berschrift f&uuml;r den News-Block', 'News', 'News', 'string0');
	Preferences::SetSetting('news_display_author', 'Autor anzeigen', 'Soll der Autor in den News-Eintr&auml;gen erscheinen?', 'News', '1', 'bool');
	Preferences::SetSetting('news_date_format', 'Datums-Fotmat f&uuml;r News', 'Dies ist das Format, in dem das Datum der News angezeigt wird.', 'News', 'd.m.Y', 'string0');
	Preferences::SetSetting('news_time_format', 'Uhrzeit-Fotmat f&uuml;r News', 'Dies ist das Format, in dem die Uhrzeit der News angezeigt wird.', 'News', 'H:i:s', 'string0');
	Preferences::SetSetting('news_display_count', 'Anzahl angezeigter News', 'Hier kann man angeben wieviele News angezeigt werden sollen.', 'News', '6', 'integer');
?>