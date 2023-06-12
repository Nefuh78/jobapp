<?php
use Tracy\Debugger;
use nefuh\framework\config;
use nefuh\framework\main;
use nefuh\jtl\jtl;

/**
 * Customer Account Portal - API
 * 
 * @author Joerg Hufen
 * @copyright Joerg Hufen, 2022
 * @package framework
 * @subpackage logging
 * @version 2.0
 */

ini_set('display_errors', true);
ini_set('error_reporting', E_ALL);
DEFINE('BASE_DIR', $_SERVER['DOCUMENT_ROOT']);
require_once(__DIR__.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'includes.php');

Debugger::setSessionStorage(new Tracy\NativeSession);
Debugger::dispatch();
Debugger::$strictMode = E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED;
if ( (main::get_var('debug', 'bool', 'REQUEST') === true || config::get_var('DEBUG', 'GLOBAL') === true) && main::get_var('action', 'string', 'REQUEST') != 'get_logs')
    Debugger::enable(Debugger::DEVELOPMENT);
else
    Debugger::enable(Debugger::PRODUCTION);

$smarty = new Smarty(); // Initialize template engine
$smarty->setTemplateDir(BASE_DIR.DIRECTORY_SEPARATOR.'template'.DIRECTORY_SEPARATOR);
$smarty->setCompileDir(BASE_DIR.DIRECTORY_SEPARATOR.'template_cache/'.DIRECTORY_SEPARATOR);
$smarty->setConfigDir(BASE_DIR.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR);
$smarty->setCacheDir(BASE_DIR.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR);
$smarty->caching = false;
$smarty->cache_lifetime = 86400;
$smarty->compile_check = true;

// Local mysql database
try {
    $localdb = new Nette\Database\Connection(config::get_var('DRIVER', 'LOCAL_DB').':host='.config::get_var('HOST', 'LOCAL_DB').';dbname='.config::get_var('DATABASE', 'LOCAL_DB'), config::get_var('USERNAME', 'LOCAL_DB'), config::get_var('PASSWORD', 'LOCAL_DB'));
    Nette\Bridges\DatabaseTracy\ConnectionPanel::initialize($localdb, true, 'Lokale Datenbank');
}
catch (Exception $e) {
    $error_message = $e->getMessage();
    $smarty->assign('error_title', 'No connection to local database');
    $smarty->assign('error_message', 'No connection to database on server '.config::get_var('HOST', 'LOCAL_DB').'<p>Please check the server!</p><p><span class="fw-bold">Error:</span>&nbsp;'.$error_message.'</p>');
    $smarty->display('error.tpl');
    die();
}
try {
    $wawidb = new Nette\Database\Connection(config::get_var('DRIVER', 'DATABASE_JTL').':Server='.config::get_var('MSSQL_DB_HOST', 'DATABASE_JTL').config::get_var('MSSQL_DB_INSTANCE', 'DATABASE_JTL').';Database='.config::get_var('MSSQL_DB_DATABASE', 'DATABASE_JTL').';Encrypt='.config::get_var('MSSQL_DB_ENCRYPT', 'DATABASE_JTL'), config::get_var('MSSQL_DB_USERNAME', 'DATABASE_JTL'), config::get_var('MSSQL_DB_PASSWORD', 'DATABASE_JTL'));
    Nette\Bridges\DatabaseTracy\ConnectionPanel::initialize($wawidb, true, 'Wawi Datenbank');
}
catch (Exception $e) {
    $error_message = $e->getMessage();
    $smarty->assign('error_title', 'No connection to local database');
    $smarty->assign('error_message', 'No connection to database on server '.config::get_var('MSSQL_DB_HOST', 'DATABASE_JTL').config::get_var('MSSQL_DB_INSTANCE', 'DATABASE_JTL').'<p>Please check the server!</p><p><span class="fw-bold">Error:</span>&nbsp;'.$error_message.'</p>');
    $smarty->display('error.tpl');
    die();
}

$action = main::get_var('action', 'string', 'REQUEST');
$first_date = main::get_var('start', 'string', 'REQUEST');
$end_date = main::get_var('end', 'string', 'REQUEST');

if (!isset($first_date) || empty($first_date)) $first_date = jtl::get_first_order_date();
if (!isset($end_date) || empty($end_date)) $end_date = jtl::get_last_order_date();

$first_date = new DateTime($first_date.' 00:00:00');
$end_date = new DateTime($end_date.' 23:59:59');

switch($action) {    
    default:
    case 'dashboard':
        $smarty->assign('start_date', jtl::get_first_order_date());
        $smarty->assign('end_date', jtl::get_last_order_date());
        $smarty->assign('current_start_date', $first_date->format('Y-m-d'));
        $smarty->assign('current_end_date', $end_date->format('Y-m-d'));
        $smarty->assign('content', '');
        $smarty->display('index.tpl');
    break;

    case 'get_top_10_most_buyed_products':
        $data = json_encode(jtl::get_top_products(10, 'DESC', $first_date, $end_date), JSON_NUMERIC_CHECK);
        echo $data;
        die();
    break;

    case 'get_top_10_worst_buyed_products':
        $data = json_encode(jtl::get_top_products(10, 'ASC', $first_date, $end_date), JSON_NUMERIC_CHECK);
        echo $data;
        die();
    break;
    
    case 'get_revenues_by_years':
        $data = json_encode(jtl::get_revenues_by_years($first_date, $end_date), JSON_NUMERIC_CHECK);
        echo $data;
        die();
    break;
        
    case 'get_revenues_by_month':
        $data = json_encode(jtl::get_revenues_by_month($first_date, $end_date), JSON_NUMERIC_CHECK);
        echo $data;
        die();
    break;

    case 'get_missing_descriptions':
        $data = json_encode(jtl::get_missing_descriptions(), JSON_NUMERIC_CHECK);
        echo $data;
        die();
    break;

    case 'get_article_details':
        $kArtikel = main::get_var('kArtikel', 'integer', 'REQUEST');
        if (isset($kArtikel) && !empty($kArtikel) && $kArtikel > 0) {
            if(!$smarty->isCached('articledetails.tpl',$kArtikel)) {
                $data = jtl::get_article_details($kArtikel);
                $article_image = BASE_DIR.DIRECTORY_SEPARATOR.'template'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.$data['Artikelnummer'].'-1.jpg';
                if (file_exists($article_image)) $smarty->assign('show_article_image', true);
                else $smarty->assign('show_article_image', false);
                $smarty->assign('data', $data);
            }
            $html_code = $smarty->fetch('articledetails.tpl', $kArtikel);
            echo $html_code;
            die();
        }
        else 
            echo json_encode(['status' => 'fail', 'message' => 'Keine Artikel-ID übergeben.']);
        die();
    break;

    case 'get_article_details_json':
        $kArtikel = main::get_var('kArtikel', 'integer', 'REQUEST');
        if (isset($kArtikel) && !empty($kArtikel) && $kArtikel > 0) {
            dumpe(jtl::get_article_details($kArtikel));
        }
        die();
    break;

    case 'get_order_stats':
        $data = json_encode(jtl::get_order_stats($first_date, $end_date), JSON_NUMERIC_CHECK);
        echo $data;
        die();
    break;

    case 'get_defect_articles':
        $data = json_encode(jtl::get_defect_articles(), JSON_NUMERIC_CHECK);
        echo $data;
        die();
    break;

}