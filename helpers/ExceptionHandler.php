<?php

namespace App\Helpers;

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
        echo $exception->getMessage();
    }
}