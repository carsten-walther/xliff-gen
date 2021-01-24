<?php

$basePath = __DIR__;

// for infinite time of execution
ini_set('max_execution_time', '0');

error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 'On');
ini_set('error_log', $basePath . '/php_error.log');

/**
 * run: php -S localhost:8000
 */

require $basePath . '/vendor/autoload.php';

// for Windows: replacing backslashes (\) by slashes (/)
if (strpos($basePath, '\\') !== false) {
    $basePath = str_replace('\\', '/', $basePath);
}

/**
 * Returns $baseUrl to this framework, also if it's resided in a subdirectory relative to the domain-root.
 * The port is respected but omitted in $baseUrl if standard for http (80) or https (443).
 *
 * @return string $baseUrl
 */
function getBaseUrl() : string
{
    $port = ':' . $_SERVER['SERVER_PORT'];
    if (($_SERVER['REQUEST_SCHEME'] === 'http' && $_SERVER['SERVER_PORT'] === '80') || ($_SERVER['REQUEST_SCHEME'] === 'https' && $_SERVER['SERVER_PORT'] === '443')) {
        $port = '';
    }
    $path = $_SERVER['SCRIPT_NAME'];
    if (strrpos($path, 'index.php') === strlen($path) - 9) {
        $path = substr($path, 0, -9);
    }
    return $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . $port . $path;
}

$baseUrl = getBaseUrl();
$controller = new \CarstenWalther\XliffGen\Controller\XliffGenController($basePath . '/app/', $baseUrl, [
    'type' => 'csv',
    'data' => $basePath . '/app/Resources/Public/Data'
]);
