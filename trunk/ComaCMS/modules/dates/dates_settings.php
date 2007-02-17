<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2007 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : dates_settings.php
 # created              : 2007-02-17
 # copyright            : (C) 2005-2007 The ComaCMS-Team	
 # email                : comacms@williblau.de
 #----------------------------------------------------------------------
 # This program is free software; you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation; either version 2 of the License, or
 # (at your option) any later version.
 #----------------------------------------------------------------------
	
	global $translation;
	
	$category = $translation->GetTranslation('dates');
	Preferences::SetSetting('dates_day_format', 'Datumsformat f&uuml;r Termine', 'Dies ist das Format, in dem das Datum des Artikels angezeigt wird.', $category, 'd.m.Y', 'string0');
	Preferences::SetSetting('dates_time_format', 'Datumsformat f&uuml;r Termine', 'Dies ist das Format, in dem die Uhrzeit des Artikels angezeigt wird.', $category, 'H:i', 'string0');
	
?>
