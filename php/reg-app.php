<?php
declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';

const REG_APP_UPSTREAM = 'https://runsign.vercel.app';

$path = (string) ($_GET['u'] ?? '');
$path = ltrim($path, '/');

if (!preg_match('/^[A-Za-z0-9_.\-\/]*$/', $path) || str_contains($path, '..')) {
    http_response_code(400);
    exit('Chemin invalide.');
}

if ($path !== '' && str_ends_with($path, '/')) {
    http_response_code(400);
    exit('Chemin invalide.');
}

$cacheDir = MIM_DATA . '/upstream';
$cacheFile = $cacheDir . '/' . preg_replace('/[^A-Za-z0-9_.-]/', '_', $path === '' ? 'index' : $path);

$body = null;
$contentType = '';

if (is_file($cacheFile) && time() - filemtime($cacheFile) < 3600) {
    $cached = json_decode((string) file_get_contents($cacheFile), true);
    if (is_array($cached) && isset($cached['type'], $cached['body'])) {
        $body = (string) $cached['body'];
        $contentType = (string) $cached['type'];
    }
}

if ($body === null) {
    $ch = curl_init(REG_APP_UPSTREAM . '/' . $path);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_USERAGENT => 'MIM-RegApp-Proxy',
    ]);

    $caBundle = MIM_ROOT . '/cacert.pem';
    if (is_file($caBundle)) {
        curl_setopt($ch, CURLOPT_CAINFO, $caBundle);
    }
    $body = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $contentType = (string) curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    unset($ch);

    if ($body === false || $status < 200 || $status >= 300) {
        http_response_code(502);
        exit('Application d’inscription momentanément indisponible.');
    }

    $isHtml = str_starts_with($contentType, 'text/html');

    if ($isHtml) {
        $rewritten = preg_replace('/(=)("|\')\/(?!\/)/', '$1$2reg-app.php?u=/', $body);
        $body = $rewritten ?? $body;

        if ($path === '') {
            // The SPA routes on location.pathname: /reg-app.php would land on its catch-all.
            // Present "/" to the router and map its later navigations back through this proxy.
            $shim = '<script>'
                . 'history.replaceState(null,"","/");'
                . '(function(){var p=history.pushState.bind(history),r=history.replaceState.bind(history);'
                . 'function w(u){if(typeof u==="string"&&u.charAt(0)==="/"&&u.indexOf("/reg-app.php")!==0){return "/reg-app.php?u="+encodeURIComponent(u);}return u;}'
                . 'history.pushState=function(a,b,u){p(a,b,w(u));};'
                . 'history.replaceState=function(a,b,u){r(a,b,w(u));};'
                . '})();</script>';
            $body = str_replace('<script type="module"', $shim . '<script type="module"', $body);
        }
    } elseif (str_contains($contentType, 'javascript')) {
        $body = str_replace('"/logo.png"', '"reg-app.php?u=/logo.png"', $body);
    }

    if (is_dir($cacheDir) || @mkdir($cacheDir, 0750, true)) {
        @file_put_contents(
            $cacheFile,
            json_encode(['type' => $contentType, 'body' => $body], JSON_UNESCAPED_SLASHES),
            LOCK_EX
        );
    }
}

header('Content-Type: ' . ($contentType !== '' ? $contentType : 'application/octet-stream'));
header('X-Robots-Tag: noindex');
echo $body;
