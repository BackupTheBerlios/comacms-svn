<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: index.php					#
 # created		: 2005-07-11					#
 # copyright		: (C) 2005 The ComaCMS-Team			#
 # email		: comacms@williblau.de				#
 #----------------------------------------------------------------------#
 # This program is free software; you can redistribute it and/or modify	#
 # it under the terms of the GNU General Public License as published by	#
 # the Free Software Foundation; either version 2 of the License, or	#
 # (at your option) any later version.					#
 #									#
 # This program is distributed in the hope that it will be useful,	#
 # but WITHOUT ANY WARRANTY; without even the implied warranty of	#
 # MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the	#
 # GNU General Public License for more details.				#
 #									#
 # You should have received a copy of the GNU General Public License	#
 # along with this program; if not, write to the Free Software		#
 # Foundation, Inc., 59 Temple Place, Suite 330,			#
 # Boston, MA  02111-1307  USA						#
 #----------------------------------------------------------------------#

	/**
	 * Set a global to make sure that common.php is executet in the
	 * only right context
	 */
	define("COMACMS_RUN", true);
	include('common.php');
	$outputpage = new OutputPage($sqlConnection);
	$outputpage->LoadPage($extern_page, $user);
	
	$output->SetReplacement('MENU' , $outputpage->GenerateMenu());
	$output->SetReplacement('MENU2' , $outputpage->GenerateMenu(2));
	$output->SetReplacement('PATH' , $outputpage->Position);
	if($config->Get('default_page', '0') != $outputpage->PageID)
		$output->SetCondition('notathome' , true);
	$output->Title = $outputpage->Title;
	$output->Language = $outputpage->Language;
	if($user->IsAdmin) {
		$content = GetPostOrGet('content');
		if($content != '')
			$outputpage->Text = stripcslashes($content);
	}
	$inlineMenu = InlineMenu::LoadInlineMenu($sqlConnection, $outputpage->PageID);
	if(count($inlineMenu) > 0){
		$output->SetReplacement($inlineMenu);
		$output->SetCondition('inlinemenu', true);
	}
	$modules = array();
	if(preg_match_all("/{(:([A-Za-z0-9_.-]+)(\?(.+?))?)}/s", $outputpage->Text, $moduleMatches)) {
		foreach($moduleMatches[2] as $key => $moduleName) {
			if(file_exists('./modules/' . $moduleName . '/' . $moduleName . '_module.php')) {
				$modules[] = array('moduleName' => $moduleName, 'moduleParameter' => $moduleMatches[4][$key], 'identifer' => $moduleMatches[0][$key]);
			}
		}
	}
	foreach($modules as $module) {
		$moduleName = $module['moduleName'];
		$moduleParameter = $module['moduleParameter'];
		include('./modules/' . $moduleName . '/' . $moduleName . '_module.php');
		if(!isset($$moduleName)) {
			if(class_exists('Module_' . $moduleName)) {
				$newClass = create_function('&$SqlConnection, &$User, &$Lang, &$Config, &$ComaLate, &$ComaLib', 'return new Module_' . $moduleName . '(&$SqlConnection, &$User, &$Lang, &$Config, &$ComaLate, &$ComaLib);');
				$$moduleName = $newClass($sqlConnection, $user, $admin_lang, $config, $output, $lib);
			}
		}
		if(isset($$moduleName))
			$outputpage->Text = str_replace($module['identifer'], $$moduleName->UseModule($module['identifer'], str_replace('&amp;', '&', $moduleParameter)), $outputpage->Text);
	}
	/*if(count($inlineMenu) > 0) {
		$output->SetCondition('inlinemenu', true);
		$output->SetReplacement($inlineMenu);
	}
	if($outputpage->PageID != $config->Get('default_page', '1'))
		$output->SetCondition('notathome', true);
	
	if(strpos($outputpage->Text, '[articles-preview]') !== false) {
		$articlesDisplayCount = $config->Get('articles_display_count', 6);
		if(!is_numeric($articlesDisplayCount))
			$articlesDisplayCount = 6;
		$outputpage->Text = str_replace('[articles-preview]', articlesPreview($articlesDisplayCount), $outputpage->Text);
	}
	if(strpos($outputpage->Text, '[news]') !== false) {
		include('news.php');
		$news_display_count = $config->Get('news_display_count', 6);
		if(!is_numeric($news_display_count))
			$news_display_count = 6;
		$outputpage->Text = str_replace('[news]', getNews($news_display_count), $outputpage->Text);
	}
	if(strpos($outputpage->Text, '[dates]') !== false)
		$outputpage->Text = str_replace('[dates]', nextDates(10), $outputpage->Text);
	*/
/*	if (strpos ($page, "[gbook-")) {
		include("gbook.php");
		$page = str_replace("[gbook-input]", gbook_input(), $page);
		$page = str_replace("[gbook-pages]", gbook_pages(), $page);
		$page = str_replace("[gbook-content]", gbook_content(), $page);
	}
	if (strpos ($page, "[contact]")) {
		include("contact.php");
		$page = str_replace("[contact]", contact_formular(), $page);
	}*/


	$output->SetReplacement('TEXT' , $outputpage->Text);
	$output->GenerateOutput();
	echo $output->GeneratedOutput;
	echo "\r\n<!-- rendered in " . round(getmicrotime(microtime()) - getmicrotime($starttime), 4) . ' seconds with ' . $sqlConnection->QueriesCount .' SQL queries -->';
?>