<?php
/**
 * Created by PhpStorm.
 * User: sovkutsan
 * Date: 2/19/18
 * Time: 9:59 AM
 */

namespace AppBundle\Utils;

/**
 * Just for my favorite debug methods
 * Class CustomMethods
 * @package AppBundle\Utils
 */
class CustomMethods
{
    /**
     * Same as print_r or var_dump, but with beautiful black-green design. Just like old-school
     * @param $var
     * @param bool $return
     * @param bool $special
     * @return mixed|string
     */
    public static function print_arr($var, $return = false, $special = true) {
        $type = gettype($var);

        $out = print_r($var, true);
        if ($special) {
            $out = htmlspecialchars($out);
        }
        $out = str_replace(' ', '&nbsp;', $out);
        if ($type == 'boolean') {
            $content = $var ? 'true' : 'false';
        } else {
            $content = nl2br($out);
        }
        $count = '';
        if ($type == 'array') {
            $count = ' (' . count($var) . ' items)';
        }

        $out = '<div style="
       border:2px inset #666;
       background:black;
       font-family:monospace;
       font-size:12px;
       color:#6F6;
       text-align:left;
       margin:20px;
       padding:16px">
         <span style="color: #F66">(' . $type . ')</span>' . $count . ' ' . $content . '</div><br /><br />';

        if (!$return)
            echo $out;
        else
            return $out;
    }

    /**
     * Same as print_arr, but with die
     * @param $var
     * @param bool $return
     * @param bool $special
     */
    public static function print_die($var, $return = false, $special = true) {
        CustomMethods::print_arr($var, $return, $special);
        $info = debug_backtrace();
        CustomMethods::print_arr("File: {$info[0]['file']} Line: {$info[0]['line']}");
        die;
    }
}