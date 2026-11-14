<?php
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
$file=__DIR__.urldecode($path);
if($path!=='/'&&is_file($file)&&(str_starts_with(realpath($file),realpath(__DIR__).DIRECTORY_SEPARATOR)||(str_starts_with($path,'/assets/')&&str_starts_with(realpath($file),realpath(__DIR__.'/assets').DIRECTORY_SEPARATOR)))&&!str_ends_with($file,'.php'))return false;
require __DIR__.'/index.php';
