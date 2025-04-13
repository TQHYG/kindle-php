<?php
set_time_limit(40);

header('Content-Type: text/html; charset=utf-8');

define('CACHE_DIR', __DIR__ . '/cache');
define('CACHE_EXPIRE', 3600); 

function generate_topbar($title) {
    return <<<HTML
    <div class="top-bar">
        <a href="javascript:history.back()" class="nav-btn">« 返回</a>
        <a href="/" class="nav-btn"># 主页</a>
        <span class="divider">|</span>
        <div class="title-wrapper">
            <h1 class="page-title">{$title}</h1>
        </div>
    </div>
HTML;
}

function show_error($message) {
    $topbar = generate_topbar('请求错误');
    
    echo <<<HTML
    <head>
        <title>请求错误</title>
        <link rel="stylesheet" href="/css/main.css">
        <link rel="stylesheet" href="/css/bangumi.css">
    </head>
    <body>
        $topbar
        <div class="container">
            <h2 class="error-title">请求错误</h2>
            <div class="error-details">
                <p>错误信息：{$message}</p>
            </div>
            <div class="error-actions">
                <button onclick="history.back()" class="btn">返回上一页</button>
            </div>
        </div>
    </body>
    HTML;
    exit;
}

function safe_fetch($url) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2TLS,
        CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2 | CURL_SSLVERSION_TLSv1_3,
        CURLOPT_HTTPHEADER => [
            'User-Agent: tqhyg/bangumi-for-kindle (https://kindle.tqhyg.net)',
            'Accept: application/json'
        ],
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_CERTINFO => true,
        CURLOPT_CAINFO => '/etc/ssl/certs/ca-certificates.crt'
    ]);
    
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        throw new Exception('cURL Error: ' . curl_error($ch));
    }
    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode !== 200) {
        throw new Exception("HTTP Code: {$httpCode}");
    }
    
    curl_close($ch);
    return $response;
}

function cache_gc() {
    static $last_gc = 0;
    
    if (time() - $last_gc < 3600) return;
    
    $files = glob(CACHE_DIR . '/*.json');
    foreach ($files as $file) {
        if (filemtime($file) < time() - CACHE_EXPIRE * 2) {
            unlink($file);
        }
    }
    $last_gc = time();
}

function get_cache($key) {
    $cache_file = CACHE_DIR . '/' . md5($key) . '.json';
    
    if (!file_exists($cache_file)) return false;
    
    $fp = fopen($cache_file, 'r');
    flock($fp, LOCK_SH); // 共享锁
    
    $data = null;
    if ((time() - filemtime($cache_file)) < CACHE_EXPIRE) {
        $content = stream_get_contents($fp);
        $data = json_decode($content, true);
    }
    
    flock($fp, LOCK_UN);
    fclose($fp);
    
    return $data !== null ? $data : false;
}

function set_cache($key, $data) {
    cache_gc();

    $cache_file = CACHE_DIR . '/' . md5($key) . '.json';
    $temp_file = tempnam(CACHE_DIR, 'temp');
    
    $fp = fopen($temp_file, 'w');
    flock($fp, LOCK_EX); // 排他锁
    
    fwrite($fp, json_encode($data));
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    
    rename($temp_file, $cache_file);
}

function cached_fetch($url) {
    $cache_key = 'bgm_' . md5($url);
    
    if ($cached = get_cache($cache_key)) {
        return $cached;
    }
    
    $raw_data = safe_fetch($url);

    $decoded_data = json_decode($raw_data, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON解析失败: ' . json_last_error_msg());
    }
    
    set_cache($cache_key, $decoded_data);
    
    return $decoded_data;
}

?>