#!/bin/bash 
../../tools/PhpDocumentor/PhpDocumentor-1.3.1/phpdoc -t . -d ../../ComaCMS/ -o HTML:Smarty:PHP -i styles/,data/,config.php,debug/,lang_*.php,admin/index.php -ti "ComaCMS 0.3"