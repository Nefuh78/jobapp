<?php
namespace nefuh\framework;
/**
 * Framework config class
 * 
 * Class for configuration functions
 *
 * @author Joerg Hufen
 * @copyright Joerg Hufen, 2022
 * @package framework
 * @subpackage configuration
 * @version 2.0
 */

class config {

    private static function read_config():array  {
        $cfg = [];
        $cfg_file = BASE_DIR.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'config.ini';
        $tmp = parse_ini_file($cfg_file, true, INI_SCANNER_TYPED);
        if (isset($tmp) && !empty($tmp)) $cfg = $tmp;
        return $cfg;
    }

    static function get_var(string $name, string $section = 'GLOBAL', string $type = 'string') {
        $cfg = self::read_config();
        if (isset($name) && !empty($name)) $name = strtoupper($name);
        if (isset($cfg[$section][$name]) && !empty($cfg[$section][$name])) {
            switch (strtolower($type)) {
                default:
                    if ($name == 'DEBUG') return (bool) $cfg[$section][$name];
                    else return $cfg[$section][$name];    
                    break;

                case 'bool':
                    if ($cfg[$section][$name] == 'true' || $cfg[$section][$name] == true) return true;
                    else return false;
                    break;
                
                case 'int':
                case 'integer':
                    return intval($cfg[$section][$name]);
                    break;
                
                case 'float':
                    return floatval($cfg[$section][$name]);
                    break;
            }            
        }
        else {
            if (strtolower($type) == 'bool') return false;
            else return '';
        }
    }

    static function set_var(string $name, $value, string $section = 'GLOBAL'):void {
        $cfg = self::read_config();
        $cfg[$section][$name] = $value;
        $content_to_write = '';
        foreach ($cfg as $section_name => $section_values) {
            $content_to_write .= '['.$section_name.']'."\n";
            foreach ($section_values as $key => $val) {
                if (is_string($val))
                    $content_to_write .= "\t".$key.' = \''.$val.'\''."\n";
                elseif (is_integer($val))
                    $content_to_write .= "\t".$key.' = '.$val."\n";
                elseif (is_bool($val))
                    $content_to_write .= "\t".$key.' = '.$val."\n";
                else
                    $content_to_write .= "\t".$key.' = \''.$val.'\''."\n";
            }
            $content_to_write .= "\n";
        }
        file_put_contents(__DIR__.DIRECTORY_SEPARATOR.'config.ini', $content_to_write);
        unset($content_to_write);
    }
}