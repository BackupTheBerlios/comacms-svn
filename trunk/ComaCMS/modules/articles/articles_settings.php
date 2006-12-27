<?php
/**
 * @package ComaCMS
 * @subpackage Articles
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : articles_settings.php
 # created              : 2006-03-13
 # copyright            : (C) 2005-2006 The ComaCMS-Team
 # email                : comacms@williblau.de
 #----------------------------------------------------------------------
 # This program is free software; you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation; either version 2 of the License, or
 # (at your option) any later version.
 #----------------------------------------------------------------------
 
 	$category = $translation->GetTranslation('articles');
 	Preferences::SetSetting('articles_title', '&Uuml;berschrift', 'Eine kleine &Uuml;berschrift f&uuml;r den Artikel-Block', $category, $category, 'string0');
	Preferences::SetSetting('articles_display_author', 'Autor anzeigen', 'Soll der Autor in den Artikel-Vorschau erscheinen?', $category, '1', 'bool');
	Preferences::SetSetting('articles_date_format', 'Datums-Fotmat f&uuml;r Artikel', 'Dies ist das Format, in dem das Datum des Artikels angezeigt wird.', $category, 'd.m.Y', 'string0');
	Preferences::SetSetting('articles_time_format', 'Uhrzeit-Fotmat f&uuml;r Artikel', 'Dies ist das Format, in dem die Uhrzeit des Artikels angezeigt wird.', $category, 'H:i:s', 'string0');
	Preferences::SetSetting('articles_display_count', 'Anzahl angezeigter Artikel', 'Hier kann man angeben wieviele Artikelvoransichten angezeigt werden sollen.', $category, '6', 'integer');
?>