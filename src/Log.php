<?php

namespace Laravel5Helpers;

use Exception;
use function date;
use function fclose;
use function fopen;
use function fwrite;
use function get_class;
use const PHP_EOL;

class Log
{
    public static function logError($class, Exception $exception)
    {
        $source = get_class($class);

        $string = date('Y-m-d H:i:s') . ': ' . $source . ': ' . $exception->getMessage() . PHP_EOL;
        $string .= $exception->getTrace() . PHP_EOL;

        $file = @fopen('laravel_helpers_errors_' . date('Y_m_d') . '.txt', 'a');
        @fwrite($file, $string);
        @fclose($file);
    }
}
