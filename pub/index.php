<?php
date_default_timezone_set('Europe/Amsterdam');
define('API_ROOT', realpath(dirname(__DIR__)));
define('API_CONF', API_ROOT . '/etc/api.ini');
define('LIB_ROOT', API_ROOT . '/lib');
define('USR_ROOT', API_ROOT . '/usr');
set_include_path(LIB_ROOT . ':' . USR_ROOT);

spl_autoload_extensions('.class.php');
spl_autoload_register();

$api = new \api\api();

try {
    $data = $api->dispatch($_REQUEST, $_SERVER);
}
catch (\Exception $exception) {
    header('HTTP/1.0 ' . $exception->getCode() . ' ' . $exception->getMessage());
    $data = [ $exception->getCode() => $exception->getMessage() ];
}

die(json_encode($data, JSON_HEX_QUOT | JSON_PRETTY_PRINT));
