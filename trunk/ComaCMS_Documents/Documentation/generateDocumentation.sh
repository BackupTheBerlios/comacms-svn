#!/bin/bash 
../../tools/PhpDocumentor/PhpDocumentor-1.4.2/phpdoc -t . -d ../../ComaCMS/ -o HTML:Smarty:PHP -i styles/,data/,/config.php,debug/,lang_*.php,admin/index.php,user/index.php -ti "ComaCMS 0.3"
