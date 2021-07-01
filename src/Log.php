<?php

namespace Laravel5Helpers;

use function date;
use function fclose;
use function fopen;
use function fwrite;
use function get_class;

class Log
{
    public static function logError($class, $message)
    {
        $source = get_class($class);

        $string = date('Y-m-d H:i:s') . ': ' . $source . ': ' . $message . PHP_EOL;

        $file = @fopen('laravel_helpers_errors_' . date('Y_m_d') . '.txt', 'a');
        @fwrite($file, $string);
        @fclose($file);
    }
}
