<?php

namespace Core\Debug;

/**
 * Class Debug
 *
 * d - output on screen
 * f - output in file
 */
class Debug
{
    /**
     * @param mixed $var
     * @return void
     */
    public static function d($var): void
    {
        if (is_array($var) || is_object($var)) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        } else {
            echo $var . '<br>';
        }
    }

    /**
     * @param mixed $var
     * @return void
     */
    public static function f($var): void
    {
        ob_start();
        echo date('d-m-Y H:i:s') . PHP_EOL;

        if (is_array($var) || is_object($var)) {
            var_dump($var);
        } else {
            echo $var . '<br>';
        }

        echo "=========================================================";

        $output = ob_get_clean();
        file_put_contents('./debug_output.txt', $output . PHP_EOL, FILE_APPEND);
    }
}
