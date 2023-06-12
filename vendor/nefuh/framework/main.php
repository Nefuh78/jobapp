<?php

namespace nefuh\framework;

/**
 * Framework main class
 * 
 * Class for framework functions
 * 
 * @author Joerg Hufen
 * @copyright Joerg Hufen, 2023
 * @package framework
 * @version 1.0
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
     * Check if a parameters is in the arguments
     *
     * @param  string $var Parametername to check
     * @return array Array with parameters and values or empty array
     */
    private static function check_arg(string $var):string {
        $arguments = self::get_arg();
        if (isset($arguments[$var])) return $arguments[$var];
        else return '';
    }
    
    /**
     * Retrieve an array with parameters, submitted by command line
     *
     * @return array Array with parameters and values
     */
    private static function get_arg():array {
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
     * Read data from a submitted csv file and return the data as array.
     *
     * @param  string $file Filename with path of the csv file to read
     * @param  string $separator The field separator (Default semicolon)
     * @param  int $length If is 0 then the size will be determined by filesize command
     * @return array Array with data read from csv file
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
            else dumpe('Datei '.$file.' konnte nicht geöffnet werden.');
        }
        else dumpe('Datei '.$file.' nicht lesbar oder nicht vorhanden!');
        return $data;
    }
    
    /**
     * Write data array to csv file.
     * 
     * Warning: If submitted file exists, all data will be overwritten.
     *
     * @param  string $file Filename with path, where the data should be written to
     * @param  array $data The data as array to write 
     * @param  string $delimiter The delimiter to use, to separate fields in the csv file
     * @return bool true if file was written, otherwoise false
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
     * Add data to an existing csv file.
     *
     * @param  string $file Filename with path, where the data should be written to
     * @param  array $data The data as array to write 
     * @param  string $delimiter The delimiter to use, to separate fields in the csv file
     * @return bool true if file was written, otherwoise false
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

    /**
     * Function to reload the page and additionaly submit a message
     *
     * @param string $msg Optional message to submit on reload
     * @param string $url The URL of the page that should be reloaded.
     * @return void
     */
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

    /**
     * Function to return the month name by number
     * 
     * Currently only german month names
     *
     * @param integer $num Number of the month
     * @return string The name of the month for the submitted number
     */
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