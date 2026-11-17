<?php

$config = new stdClass();

$config->app_name = 'yuva';
$config->app_version = '3.1';

$is_localhost = (isset($_SERVER['HTTP_HOST']) && (
    strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ||
    strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false ||
    strpos($_SERVER['HTTP_HOST'], '192.168.') !== false ||
    strpos($_SERVER['HTTP_HOST'], '10.') !== false
));

if ($is_localhost) {
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
    $config->site_url = $scheme . $_SERVER['HTTP_HOST'] . '/';
    $config->force_url = false;
    $config->force_https = false;
} else {
    $config->site_url = "https://chat.yuvabharathierp.org/";
    $config->force_url = true;
    $config->force_https = false;
}
$config->developer_mode = false;

$config->samesite_cookies = "Lax";
$config->timezone = 'Asia/Kolkata';
$config->file_seperator = '-gr-';

$db_error_mode = PDO::ERRMODE_SILENT;

if ($config->developer_mode) {
    $db_error_mode = PDO::ERRMODE_EXCEPTION;
}

$config->database = [
    'type' => 'mysql',
    'host' => '38.109.10.140',
    'database' => 'yuvachat1',
    'username' => 'yuvachat1',
    'password' => 'Ireava#001',
    'port' => '3306',
    'prefix' => 'gr_',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_general_ci',
    'logging' => false,
    'error' => $db_error_mode,
];
