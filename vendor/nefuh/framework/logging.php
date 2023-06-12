<?php
namespace nefuh\framework;
use nefuh\framework\main;
use nefuh\framework\config;
use DateTime;
use DateTimeZone;
use DateInterval;
use Nette\Database\Connection;

/**
 * Framework Logger Class
 * 
 * Class for logging functions
 *
 * @author Joerg Hufen
 * @copyright Joerg Hufen, 2022
 * @package framework
 * @subpackage logging
 * @version 2.0
 */

class logging {

    private static function connect() {
        global $database;
        return $database;
    }

    /**
     * Function to check if needed logging table exists, if not try to create the table
     */
    private static function check_log_db() { 
        $sql = "SHOW TABLES";
        $tables = self::connect()->fetchAll($sql);
        $installed = true; // Set to false after debug
        if (isset($tables) && !empty($tables) && is_array($tables))
            foreach ($tables as $table)
                if ($table['Tables_in_'.config::get_var('DATABASE', 'LOG_DB')] == config::get_var('LOG_TABLE', 'LOG_DB')) $installed = true;
        if ($installed == false) {
            $res = self::connect()->query("CREATE TABLE `".config::get_var('LOG_TABLE', 'LOG_DB')."` (
                    `log_id` int(10) NOT NULL AUTO_INCREMENT,
                    `log_timestamp` datetime NOT NULL,
                    `log_text` varchar(255) NOT NULL,
                    `log_section` varchar(255) NOT NULL,
                    `log_function` varchar(255) NOT NULL,
                    `log_type` VARCHAR(255) NOT NULL DEFAULT 'info',
                    PRIMARY KEY (`log_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        }
    }

    /**
     * write_log
     * ==================
     * 
     * Write a textline to a log file.
     *
     * @param string $log_entry The text that will be written into the logfile with datestamp.
     * @param string $section Section
     * @param string $log_type Like info or error
     * @param string $log_function PHP-function where the logging entry is from
     * @return void
     */
    public static function write_log(string $log_entry, string $section = 'Standard', string $log_type = 'info', string $log_function = ''):void {
        self::check_log_db();     
        $timestamp = new DateTime();
        $timestamp->setTimezone(new DateTimeZone('Europe/Berlin'));
    	self::connect()->query('INSERT INTO '.config::get_var('LOG_TABLE', 'LOG_DB').' ?', [
            'log_timestamp' => $timestamp,
            'log_text' => $log_entry,
            'log_section' => $section,
            'log_function' => $log_function,
            'log_type' => $log_type
        ]);
    }

    public static function log_requests():void {
        self::check_log_db();
        $timestamp = new DateTime();
        $timestamp->setTimezone(new DateTimeZone('Europe/Berlin'));
        $log_entries = '';
        /*
        $data = $_SERVER;
        if (is_array($data) && !empty($data)) { 
            $log_entries .= 'SERVER: '."\n";
            foreach ($data as $key => $value)
                $log_entries .= $key. ' => '.$value."\n";
        }
        $log_entries = trim($log_entries)."\n";
        */
        $data = $_REQUEST;
        if (is_array($data) && !empty($data)) { 
            $log_entries .= 'REQUEST: '."\n";
            foreach ($data as $key => $value)
                $log_entries .= $key. ' => '.$value."\n";
        }
        $log_entries = trim($log_entries)."\n";
        /*
        $data = $_POST;
        if (is_array($data) && !empty($data)) { 
            $log_entries .= 'POST: '."\n";
            foreach ($data as $key => $value)
                $log_entries .= $key. ' => '.$value."\n";
        }
        $log_entries = trim($log_entries)."\n";
        $data = getallheaders();
        if (is_array($data) && !empty($data)) { 
            $log_entries .= 'HEADERS: '."\n";
            foreach ($data as $key => $value)
                $log_entries .= $key. ' => '.$value."\n";
        }
        $log_entries = trim($log_entries)."\n";
        $body = file_get_contents('php://input');
        $log_entries .= '------ BODY ---------'."\n";
        $log_entries .= $body."\n";
        */
        if (!empty($log_entries) && !preg_match('/action => dashboard/', $log_entries)) {
            self::connect()->query('INSERT INTO '.config::get_var('LOG_TABLE', 'LOG_DB').' ?', [
                'log_timestamp' => $timestamp, 
                'log_text' => $log_entries,
                'log_section' => 'REQUESTS',
                'log_function' => 'log_requests',
                'log_type' => 'info'
            ]);
        }   
    }

    public static function get_log_dates() {
        $log_db = self::connect();
        self::check_log_db();     
        $log_data = [];
        $res = $log_db->fetchAll('SELECT DATE(log_timestamp) AS log_timestamp FROM '.config::get_var('LOG_TABLE', 'LOG_DB').' GROUP BY DATE(log_timestamp) ORDER BY log_timestamp DESC');
        if (isset($res) && !empty($res) && (is_object($res) || is_array($res)))
            foreach ($res as $entry)
                $log_data[$entry['log_timestamp']->format('Y-m-d')] = $entry['log_timestamp']->format('d.m.Y');
        return json_encode($log_data);    
    }

    public static function read_log() {
        self::check_log_db();     
        $log_data = [];        
        $startdate = main::get_var('logdate', 'string', 'REQUEST') ? new DateTime(main::get_var('logdate', 'string', 'REQUEST')) : new DateTime(date('Y-m-d'));
        $enddate = main::get_var('logdate', 'string', 'REQUEST') ? new DateTime(main::get_var('logdate', 'string', 'REQUEST')) : new DateTime(date('Y-m-d'));
        $enddate->add(new DateInterval('P1D'));
        $res = self::connect()->fetchAll('SELECT log_id,log_timestamp,log_text,log_section,log_function,log_type FROM '.config::get_var('LOG_TABLE', 'LOG_DB').' WHERE', ['log_timestamp >=' => $startdate, 'log_timestamp <' => $enddate], 'ORDER BY log_timestamp DESC, log_type ASC');
        if (isset($res) && !empty($res) && (is_object($res) || is_array($res))) {
            foreach ($res as $entry) {
                if ($entry['log_section'] == 'REQUESTS')
                    $log_data[$entry['log_timestamp']->format('Y-m-d')][strtoupper($entry['log_type'])][] = [ 'datum' => $entry['log_timestamp']->format('H:i:s'), 'nachricht' => nl2br($entry['log_text']), 'type' => $entry['log_type'], 'section' => $entry['log_section'], 'function' => $entry['log_function']];
                else
                    $log_data[$entry['log_timestamp']->format('Y-m-d')][strtoupper($entry['log_type'])][] = [ 'datum' => $entry['log_timestamp']->format('H:i:s'), 'nachricht' => $entry['log_text'], 'type' => $entry['log_type'], 'section' => $entry['log_section'], 'function' => $entry['log_function']];
                ksort($log_data[$entry['log_timestamp']->format('Y-m-d')]);
            }
            ksort($log_data);
        }
        return json_encode($log_data);    
    }
    
    public static function clear_log() {
        self::connect()->query('TRUNCATE '.config::get_var('LOG_TABLE', 'LOG_DB').'');
    }
}