<?php

function classAutoloader($class_name)
{
    // # List all the class directories in the array.
    $array_paths = array(
        '/models/',
        '/components/'
    );

    $class_name = explode("\\", $class_name) ;

    foreach ($array_paths as $path) {
        if (count($class_name) > 1) {
            $path = ROOT . $path . $class_name[1] . '.php';
        } else {
            $path = ROOT . $path . $class_name[0] . '.php';
        }
        
        if (is_file($path)) {
            include_once $path;
        }
    }

    // require_once (ROOT . '/components/'  . $class_name[1] . '.php');
}

spl_autoload_register('classAutoloader');