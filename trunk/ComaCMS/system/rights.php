<?php
/**
 * @package ComaCMS
 * @subpackage Authentication
 * @copyright (C) 2005-2009 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : rights.php
 # created              : 2009-05-23
 # copyright            : (C) 2005-2009 The ComaCMS-Team
 # email                : comacms@williblau.de
 #----------------------------------------------------------------------
 # This program is free software; you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation; either version 2 of the License, or
 # (at your option) any later version.
 #----------------------------------------------------------------------

	global $rights_Translation;
	global $rights_SqlConnection;
	// Access to the translation class via $rights_Translation->GetTranslation($translation_string);
	// Access to the mysql database of the system via $rights_SqlConnection->SqlQuery($sql);
	// Rights::SetRight('this is the group of rights in wich it should be displayed', 'the name of the right', 'this is the string displayed to the user', 'this is the string shown as an explanation for the right', is this right dynamic?, here can be pasted an array containing dynamic content with the keys 'name' defined as ident, 'display' and 'description');
	
	Rights::SetRight($translation->GetTranslation('pages'), 'view_page_private', $translation->GetTranslation('view_privat_pages'), $translation->GetTranslation('this_right_allows_to_view_all_privat_pages'), true);
?>
