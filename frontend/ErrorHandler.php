<?php

set_error_handler('exceptions_error_handler');

/**
 * @param $severity
 * @param $message
 * @param $filename
 * @param $lineno
 * @throws ErrorException
 */
function exceptions_error_handler($severity, $message, $filename, $lineno)
{
    if (error_reporting() == 0) {
        return;
    }
    if (error_reporting() & $severity) {
        echo $message . '<br>Severity ' . $severity . '<br>' . $filename . ' at line ' . $lineno . '<br>';
    }
}
