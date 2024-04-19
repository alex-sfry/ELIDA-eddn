<?php

use Core\Helper\ExceptionHandler;

ini_set('display_errors', 1);
error_reporting(E_ALL);

const ROOT = __DIR__;

require_once ROOT . '/../bootstrap3.php';
require_once(ROOT . '/vendor/autoload.php');
require_once(ROOT . '/controllers/EddnController.php');

$ex_handler = new ExceptionHandler();
set_exception_handler(array($ex_handler, 'handleException'));
