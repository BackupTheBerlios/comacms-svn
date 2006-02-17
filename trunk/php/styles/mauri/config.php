<?php
/*
 * ComaLate-Template-Config
 */
 $config['longname'] = 'Mauritius Style';
 $config['css-files'][]['all'] = 'style.css';
 $config['css-files'][]['print'] = 'printerstyle.css';
 $config['css-files'][]['iefix']['lt IE 7'] = 'iefix.css';
 $config['css-files'][]['operafix'] = 'operafix.css'; 
 $config['template'] = 'mainpage.php';
 $config['withoutDefault'] = false;
 $config['conditional-css']['inlinemenu']['all'] = '#text{margin-left: 355px;}';
 $config['conditional-css']['inlinemenu']['print'] = '#text{margin-left: 205px;}';
 $config['conditional-css']['notathome']['iefix']['lt IE 7'] = '#title {margin-bottom: 33px;} #position{border-bottom:none;}';
  
?>