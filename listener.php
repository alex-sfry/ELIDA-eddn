<?php

use App\Helpers\ExceptionHandler;
const ROOT = __DIR__;

require_once ROOT . '/../bootstrap3.php';
require_once(ROOT . '/vendor/autoload.php');
require_once(ROOT . '/components/Autoload.php');
require_once(ROOT . '/controllers/EddnController.php');

$ex_handler = new ExceptionHandler();
set_exception_handler(array($ex_handler, 'handleException'));