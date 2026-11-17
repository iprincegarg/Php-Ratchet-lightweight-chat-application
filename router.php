<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Normalize PHP_SELF and SCRIPT_NAME for built-in web server
$_SERVER['PHP_SELF'] = '/index.php';
$_SERVER['SCRIPT_NAME'] = '/index.php';

// Serve local static file if it exists and is not a directory
$localPath = __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, $uri);
if ($uri !== '/' && file_exists($localPath) && !is_dir($localPath)) {
    return false;
}

// If it's a request to /assets/ that doesn't exist locally, fetch from live server and cache it locally
if (strpos($uri, '/assets/') === 0) {
    $remoteUrl = 'https://chat.yuvabharathierp.org' . $uri;
    $ch = curl_init($remoteUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
    $content = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    if ($httpCode === 200 && $content !== false) {
        $dir = dirname($localPath);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($localPath, $content);
        if ($contentType) {
            header('Content-Type: ' . $contentType);
        }
        echo $content;
        exit;
    } else {
        http_response_code(404);
        exit;
    }
}

// For static asset extensions that don't exist, don't pass to index.php
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot|map)$/i', $uri)) {
    http_response_code(404);
    exit;
}

// Application routing
require_once __DIR__ . '/index.php';
