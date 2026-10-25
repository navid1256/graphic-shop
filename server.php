<?php

$publicDirectory = realpath(getcwd());

if ($publicDirectory === false) {
    throw new RuntimeException('Unable to resolve the Laravel public directory.');
}

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');
$requestedFile = realpath($publicDirectory.$uri);
$storageDirectory = realpath($publicDirectory.'/../storage/app/public');

if (
    $uri !== '/'
    && $requestedFile !== false
    && is_file($requestedFile)
    && (
        str_starts_with($requestedFile, $publicDirectory.DIRECTORY_SEPARATOR)
        || (
            str_starts_with($uri, '/storage/')
            && $storageDirectory !== false
            && str_starts_with($requestedFile, $storageDirectory.DIRECTORY_SEPARATOR)
        )
    )
) {
    return false;
}

$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';
unset($_SERVER['PATH_INFO']);

require_once $publicDirectory.'/index.php';
