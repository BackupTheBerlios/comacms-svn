<?php
/**
 * @package ComaCMS
 * @subpackage Sitemap
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : articles_settings.php
 # created              : 2006-12-11
 # copyright            : (C) 2005-2006 The ComaCMS-Team
 # email                : comacms@williblau.de
 #----------------------------------------------------------------------
 # This program is free software; you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation; either version 2 of the License, or
 # (at your option) any later version.
 #----------------------------------------------------------------------
 
 	global $admin_lang;
 	//Preferences::SetSetting('einstellungsname', 'angezeigte Option', 'info zu der Optionin <span class="info">info</span>', 'Einstellungsgruppe', 'Defaultwert', 'Typ');
 	Preferences::SetSetting('sitemap_show_language', 'Sprache anzeigen', 'Soll die Sprache einer Seite in der Sitemap angezeigt werden?', 'Sitemap Modul', '1', 'bool');
?>
