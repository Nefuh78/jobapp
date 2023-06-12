<?php
use nefuh\framework\config;

ob_start();
ini_set('display_errors', true);
ini_set('error_reporting', E_ALL & ~E_DEPRECATED);
define('DIR_SEP', DIRECTORY_SEPARATOR);

// Core
require_once getcwd().DIR_SEP.'vendor'.DIR_SEP.'autoload.php';

spl_autoload_register(function(string $className): void {
    global $log;
    $className = ltrim($className, '\\');
    $fileName  = '';
    $namespace = '';
    if ( $lastNsPos = strrpos($className, '\\') ) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName  = str_replace('\\', DIR_SEP, $namespace) . DIR_SEP;
    }
    $fileName .= str_replace('_', DIR_SEP, $className) . '.php';
    $log = getcwd().DIR_SEP.'vendor'.DIR_SEP.$fileName.'<br>';
    require_once getcwd().DIR_SEP.'vendor'.DIR_SEP.$fileName;   
});

// Load config.ini and parse into 
if (file_exists(__DIR__.DIR_SEP.'config.local.ini'))
    $tmp = parse_ini_file(__DIR__.DIR_SEP.'config.local.ini', true, INI_SCANNER_TYPED);
else
    $tmp = parse_ini_file(__DIR__.DIR_SEP.'config.ini', true, INI_SCANNER_TYPED);

define('LOG_TYPE', config::get_var('LOG_TYPE', 'GLOBAL'));

