<?php

namespace Core\Helper;

use Core\Debug\Debug;
use Throwable;

/**
 * class ExceptionHandler
 * custom exception handler
 */
class ExceptionHandler
{
    /**
     * @param Throwable $exception
     * @return void
     */
    public function handleException(Throwable $exception): void
    {
        echo 'Error in ' . $exception->getFile() . ' on line ' . $exception->getLine() . '<br>';
        echo '"' . $exception->getMessage() . '"';
        echo '<br>Exception code: ' . $exception->getCode() . '<br>';
        Debug::d($exception->getTrace()[0]);
    }
}