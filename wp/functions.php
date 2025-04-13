<?php
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

function detect_protocol($domain) {
    $test_urls = [
        'https' => "https://{$domain}/wp-json/",
        'http' => "http://{$domain}/wp-json/"
    ];
    
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false
        ],
        'http' => [
            'follow_location' => 0,
            'timeout' => 10
        ]
    ]);
    
    foreach ($test_urls as $protocol => $test_url) {
        $headers = @get_headers($test_url, 1, $context);
        if ($headers !== false && isset($headers[0])) {
            $status_code = substr($headers[0], 9, 3);
            if ($status_code >= 200 && $status_code < 300) {
                return $protocol;
            } elseif (isset($headers['Location'])) {
                $location = is_array($headers['Location']) ? end($headers['Location']) : $headers['Location'];
                if (stripos($location, 'https://') === 0) return 'https';
                if (stripos($location, 'http://') === 0) return 'http';
            }
        }
    }
    return false;
}

function check_site_param() {
    if (!isset($_GET['site']) || empty(trim($_GET['site']))) {
        header("Location: index.php");
        exit;
    }
}

function sanitize_site_domain($input) {
    $raw = $input;
    if (!preg_match('/^https?:\/\//i', $raw)) {
        $raw = 'http://' . $raw;
    }

    $parsed = parse_url($raw);
    if (!isset($parsed['host'])) {
        return $input; 
    }

    $domain = $parsed['host'];
    
    $domain = preg_replace('/:\d+$/', '', $domain);
    
    $domain = preg_replace('/[^a-zA-Z0-9.\-]/', '', $domain);
    
    return strtolower($domain);
}

function fetch_wp_data($url) {
    if (!preg_match('/^https?:\/\//i', $url)) {
        $protocol = detect_protocol($url);
        if (!$protocol) {
            return ['error' => '无法检测到有效的协议（HTTP/HTTPS），请确认网站是否是WordPres站点。'];
        }
        $url = "{$protocol}://{$url}";
    }

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return ['error' => '无效的URL格式。'];
    }

    $endpoints = [
        'site' => '/wp-json/',
        'posts' => '/wp-json/wp/v2/posts',
        'categories' => '/wp-json/wp/v2/categories'
    ];
    
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false
        ],
        'http' => [
            'timeout' => 10
        ]
    ]);

    $data = [];
    foreach ($endpoints as $key => $ep) {
        $full_url = rtrim($url, '/') . $ep;
        $response = @file_get_contents($full_url, false, $context);
        if ($response === false) {
            $error = error_get_last();
            return ['error' => "无法访问 {$full_url}，请确认网站是否是WordPres站点。     " . $error['message']];
        }

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => "{$full_url} 返回了无效的JSON数据。"];
        }

        $data[$key] = $decoded;
    }

    if (empty($data['site']['name']) || empty($data['site']['description'])) {
        return ['error' => '该网站不是有效的WordPress站点或未启用REST API。'];
    }
    return $data;
}
?>