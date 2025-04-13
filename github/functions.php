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

function show_error($message) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $topbar = generate_topbar('请求错误');
    
    echo <<<HTML
    <!DOCTYPE html>
    <html>
    <head>
        <title>请求错误</title>
        <link rel="stylesheet" href="/css/main.css">
        <link rel="stylesheet" href="/css/github.css">
    </head>
    <body>
        $topbar
        <div class="content">
            <h2 class="error-title">请求错误</h2>
            <div class="error-details">
                <p>错误信息：{$message}</p>
            </div>
            <div class="error-actions">
                <button onclick="history.back()" class="btn">返回上一页</button>
            </div>
        </div>
    </body>
    </html>
HTML;
    exit;
}

function check_request_rate($max_requests = 5, $period = 60) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    $current_time = time();
    $_SESSION['requests'] = array_filter($_SESSION['requests'] ?? [], function($t) use ($current_time, $period) {
        return $current_time - $t <= $period;
    });
    
    if (count($_SESSION['requests']) >= $max_requests) {
        show_error('操作过于频繁，请等待'.($period/60).'分钟后再试');
    }
    
    $_SESSION['requests'][] = $current_time;
}

function fetch_github_api($url) {
    check_request_rate();
    
    $context = stream_context_create([
        'http' => [
            'header' => 'User-Agent: Kindle-Github-Viewer',
            'ignore_errors' => true
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    if ($response === false) {
        $error = error_get_last();
        show_error('连接GitHub失败: '.$error['message']);
    }

    $status_code = 200;
    foreach ($http_response_header as $header) {
        if (preg_match('/^HTTP\/1\.\d (\d{3})/', $header, $matches)) {
            $status_code = intval($matches[1]);
            break;
        }
    }

    $data = json_decode($response, true);
    if ($status_code >= 400) {
        if ($status_code === 403 && isset($data['message']) && strpos($data['message'], 'API rate limit') !== false) {
            show_error('GitHub API请求次数超限，请稍后再试');
        }
        show_error("API请求失败 ({$status_code}): ".$data['message'] ?? '未知错误');
    }
    
    return $data;
}

function parse_markdown($content) {
    require_once 'Parsedown.php';
    try {
        $parsedown = new Parsedown();
        $parsedown->setSafeMode(false); 
        
        $html = $parsedown->text($content);
        $html = preg_replace('/<img\s+([^>]*)>/i', '<img $1>', $html);
        
        $html = preg_replace_callback('/<img\s+([^>]*)>/i', function($matches) {
            $attrs = $matches[1];
            preg_match_all('/(\w+)=["\']([^"\']*)["\']/', $attrs, $attr_matches);
            $safe_attrs = [];
            foreach ($attr_matches[1] as $i => $name) {
                if (in_array(strtolower($name), ['src', 'alt', 'title', 'width', 'height'])) {
                    $safe_attrs[] = $name . '="' . htmlspecialchars($attr_matches[2][$i]) . '"';
                }
            }
            $safe_attrs[] = 'style="max-width:95%;height:auto;margin:0.5em auto"';
            return '<img ' . implode(' ', $safe_attrs) . '>';
        }, $html);
        
        return $html;
        
    } catch (Exception $e) {
        return '<div class="md-error">Markdown解析失败: '.htmlspecialchars($e->getMessage()).'</div>';
    }
}

?>