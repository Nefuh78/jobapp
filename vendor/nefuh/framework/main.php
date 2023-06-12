<?php

namespace nefuh\framework;
use nefuh\framework\config;
use nefuh\framework\logging;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Framework main class
 * 
 * Class for global framework functions
 * 
 * @author Joerg Hufen
 * @copyright Joerg Hufen, 2022
 * @package framework
 * @subpackage main
 * @version 2.0
 */
class main
{
    /**
     * get_var
     * =======
     * 
     * Function to get variables from the url by give function (REQUEST, POST or GET)
     * and return the value by the given type (like string, integer, ...)
     *
     * @param  string $varname Name of the variable to get from URL
     * @param  string $vartype Type of the variable value to return
     * @param  string $get_type Type of function to use to get the variable from URL
     * @return mixed The value of the variable
     */
    static function get_var(string $varname, string $vartype = 'string', string $get_type = 'REQUEST') {
        $get_type = strtoupper($get_type);
        $vartype = strtolower($vartype);
        switch ($get_type) {
            default:
            case 'REQUEST':
                $var = (isset($_REQUEST[$varname]) && !empty($_REQUEST[$varname])) ? $_REQUEST[$varname] : self::check_arg($varname);
            break;

            case 'GET':
                $var = (isset($_GET[$varname]) && !empty($_GET[$varname])) ? $_GET[$varname] : self::check_arg($varname);
            break;

            case 'POST':
                $var = (isset($_POST[$varname]) && !empty($_POST[$varname])) ? $_POST[$varname] : self::check_arg($varname);
            break;
        }
        switch ($vartype) {
            default:
            case 'string':
                $var = (string) $var;
            break;

            case 'integer':
                $var = intval($var);
            break;

            case 'float':
                $var = floatval($var);
            break;

            case 'bool':
                if ($var == 'true') $var = true;
                else $var = false;
            break;
        }
        return $var;
    }
    
    /**
     * check_arg
     *
     * @param  mixed $var
     * @return void
     */
    private static function check_arg(string $var) {
        $arguments = self::get_arg();
        if (isset($arguments[$var])) return $arguments[$var];
        else null;
    }
    
    /**
     * get_arg
     *
     * @return void
     */
    private static function get_arg() {
        global $argc, $argv;
        $arguments = [];
        if ($argc > 1) {
            foreach ($argv as $id => $tmp) {
                if ($id > 0) {
                    $tmp_data = explode('=', $tmp);
                    if (isset($tmp_data[0]) && isset($tmp_data[1]))
                        $arguments[$tmp_data[0]] = $tmp_data[1];
                }
            }
        }
        return $arguments;
    }
        
    /**
     * read_csv
     *
     * @param  string $file
     * @param  string $separator
     * @param  int $length
     * @return array
     */
    static function read_csv(string $file, string $separator = ';', int $length = 0):array {
        $data = [];
        if (!isset($length) || empty($length)) $length = filesize($file);
        if (is_file($file) && is_readable($file)) {
            if (($handle = fopen($file, "r")) !== FALSE) {
                while (($tmp = fgetcsv($handle, $length, $separator)) !== FALSE)
                    $data[] = $tmp;
                fclose($handle);
            }   
            else logging::write_log('Datei '.$file.' konnte nicht geöffnet werden.');
        }
        else logging::write_log('Datei '.$file.' nicht lesbar oder nicht vorhanden!');
        return $data;
    }
    
    /**
     * write_csv
     *
     * @param  string $file
     * @param  array $data
     * @param  string $delimiter
     * @return bool
     */
    public static function write_csv(string $file, array $data, string $delimiter = ';'):bool {
        if ($handle = fopen($file, "w")) {
            foreach ($data as $line)
                fputcsv($handle, $line, $delimiter);
            fclose($handle);
            return true;
        }
        return false;
    }

    /**
     * add_csv
     *
     * @param  string $file
     * @param  array $data
     * @param  string $delimiter
     * @return bool
     */
    public static function add_csv(string $file, array $data, string $delimiter = ';'):bool {
        if ($handle = fopen($file, "a")) {
            foreach ($data as $line)
                fputcsv($handle, $line, $delimiter);
            fclose($handle);
            return true;
        }
        return false;
    }

    static public function reload_page(string $msg = '', string $url = ''):void {
        if (!isset($url) || empty($url)) {
            if (isset($msg) && !empty($msg))
                header("Refresh:0; url=index.php?message=".urlencode($msg));
            else
                header("Refresh:0; url=index.php");
        }
        else {
            if (isset($msg) && !empty($msg))
                header("Refresh:0; url=".$url."?message=".urlencode($msg));
            else
                header("Refresh:0; url=".$url);
        }
        die();
    }

    public static function get_month_name(int $num):string {
        switch ($num) {
            case 1:
                return 'Januar';
            break;

            case 2:
                return 'Februar';
            break;
            
            case 3:
                return 'M&auml;rz';
            break;
            
            case 4:
                return 'April';
            break;
            
            case 5:
                return 'Mai';
            break;
            
            case 6:
                return 'Juni';
            break;
            
            case 7:
                return 'Juli';
            break;
            
            case 8:
                return 'August';
            break;
            
            case 9:
                return 'September';
            break;
            
            case 10:
                return 'Oktober';
            break;
            
            case 11:
                return 'November';
            break;
            
            case 12:
                return 'Dezember';
            break;
            
        }
    }
}