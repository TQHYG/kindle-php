<?php
define('CACHE_DIR', 'pic/');
if (!file_exists(CACHE_DIR)) mkdir(CACHE_DIR, 0755, true);

// 定义百科站点配置
$wiki_sites = [
    'zh' => [
        'name' => '中文维基',
        'api' => 'https://zh.wikipedia.org/w/api.php',
        'domain' => 'zh.wikipedia.org'
    ],
    'en' => [
        'name' => '英文维基',
        'api' => 'https://en.wikipedia.org/w/api.php',
        'domain' => 'en.wikipedia.org'
    ],
    'moegirl' => [
        'name' => '萌娘百科',
        'api' => 'https://moegirl.uk/api.php',
        'domain' => 'moegirl.uk'
    ],
    'hmoegirl' => [
        'name' => 'H萌百科',
        'api' => 'https://hmoegirl.com/api.php',
        'domain' => 'hmoegirl.com'
    ]
];

function process_image($img_url) {
    $local_name = CACHE_DIR . md5($img_url) . '.jpg';
    if (!file_exists($local_name)) {
        $img_data = @file_get_contents($img_url);
        if ($img_data) {
            $src_img = imagecreatefromstring($img_data);
            $width = imagesx($src_img);
            $height = imagesy($src_img);
            $max_size = 600;
            
            if($width === false || $height === false) return $img_url;
            
            $ratio = $width / $height;
            $new_width = min($width, $max_size);
            $new_height = $new_width / $ratio;
            
            $gray_img = imagecreatetruecolor($new_width, $new_height);
            imagecopyresampled($gray_img, $src_img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            imagefilter($gray_img, IMG_FILTER_GRAYSCALE);
            imagefilter($gray_img, IMG_FILTER_CONTRAST, -15); 
            
            if (!imagejpeg($gray_img, $local_name, 70)) {
                unlink($local_name);
                return $img_url;
            }
            imagedestroy($src_img);
            imagedestroy($gray_img);
        }
    }
    return $local_name;
}

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

function get_by_curl($url) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3, 
        CURLOPT_CONNECTTIMEOUT => 3, 
        CURLOPT_TIMEOUT => 5, 
        CURLOPT_SSL_VERIFYPEER => false, 
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36', 
        CURLOPT_ENCODING => 'gzip, deflate'
    ]);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $response;
}

function get_random_page($site) {
    global $wiki_sites;
    
    if (!isset($wiki_sites[$site])) {
        $site = 'zh'; // 默认使用中文维基
    }
    
    $api_url = $wiki_sites[$site]['api'] . "?action=query&format=json&list=random&rnnamespace=0&rnlimit=1";
    $response = json_decode(get_by_curl($api_url), true);
    
    if (isset($response['query']['random'][0]['id'])) {
        return $response['query']['random'][0]['id'];
    }
    return null;
}

function show_search_form() {
    global $wiki_sites;
    
    $options = '';
    foreach ($wiki_sites as $key => $site) {
        $selected = $key === 'zh' ? ' selected' : '';
        $options .= "<option value=\"{$key}\"{$selected}>{$site['name']}</option>";
    }
    
    $topbar = generate_topbar("百科搜索");
    return <<<HTML
    <!DOCTYPE html>
    <html>
    <head>
        <title>Kindle百科搜索</title>
        <meta name="viewport" content="width=device-width">
        <link rel="stylesheet" href="/css/main.css">
        <link rel="stylesheet" href="/css/wiki.css">
    </head>
    <body>
        {$topbar}
        <div class="container">
            <h2 class="title">百科查询</h2>
            <form action="wiki.php" method="get" class="search-form">
                <select name="site" class="btn">
                    {$options}
                </select>
                <input type="text" id="search-input" name="q" placeholder="输入查询关键词" required>
                <div class="form-buttons">
                    <button type="submit" class="btn">搜索</button>
                    <button type="button" onclick="randomPage()" class="btn random-btn">随机页面</button>
                </div>
            </form>
        </div>
        
        <script>
        function randomPage() {
            const site = document.querySelector('select[name="site"]').value;
            window.location.href = 'wiki.php?action=random&site=' + site;
        }
        </script>
    </body>
    </html>
HTML;
}

function get_search_results($keyword, $site = 'zh') {
    global $wiki_sites;
    
    if (!isset($wiki_sites[$site])) {
        $site = 'zh'; // 默认使用中文维基
    }
    
    $api_url = $wiki_sites[$site]['api'] . "?action=query&format=json&list=search&srsearch=".urlencode($keyword);
    $response = json_decode(get_by_curl($api_url), true);
    return $response['query']['search'] ?? [];
}

function show_search_results($keyword, $results, $site) {
    global $wiki_sites;
    
    $site_name = $wiki_sites[$site]['name'] ?? '百科';
    $topbar = generate_topbar("{$site_name}搜索 - {$keyword}");
    $html = <<<HTML
    <!DOCTYPE html>
    <html>
    <head>
        <title>Kindle百科搜索</title>
        <meta name="viewport" content="width=device-width">
        <link rel="stylesheet" href="/css/main.css">
        <link rel="stylesheet" href="/css/wiki.css">
    </head>
    <body>
        {$topbar}
        <div class="container">
            <h3 class="search-title">找到{$GLOBALS['result_count']}条相关结果（{$site_name}）</h3>
            <ul class="result-list">
HTML;
    
    foreach ($results as $item) {
        $pageid = $item['pageid'];
        $html .= <<<ITEM
        <li class="result-item">
            <a href="wiki.php?pageid={$pageid}&site={$site}" class="result-link">
                <span class="result-title">{$item['title']}</span>
                <span class="result-snippet">{$item['snippet']}</span>
            </a>
        </li>
ITEM;
    }
    
    $html .= <<<HTML
            </ul>
        </div>
    </body>
    </html>
HTML;
    return $html;
}

function show_article_detail($pageid, $site = 'zh') {
    global $wiki_sites;
    
    if (!isset($wiki_sites[$site])) {
        $site = 'zh'; // 默认使用中文维基
    }
    
    $api_url = $wiki_sites[$site]['api'] . "?action=query&format=json&prop=extracts&pageids={$pageid}";
    $response = json_decode(get_by_curl($api_url), true);
    $page = current($response['query']['pages']);

    $title = $page['title'] ?? '词条详情';
    $topbar = generate_topbar($title);
    
    $content = preg_replace_callback('/<img[^>]+src="([^"]+)"/i', function($m) {
        $local_img = process_image($m[1]);
        return '<img src="' . $local_img . '" class="wiki-image">';
    }, $page['extract']);

    return <<<HTML
    <!DOCTYPE html>
    <html>
    <head>
        <title>Kindle百科 - {$title}</title>
        <meta name="viewport" content="width=device-width">
        <link rel="stylesheet" href="/css/main.css">
        <link rel="stylesheet" href="/css/wiki.css">
    </head>
    <body>
        {$topbar}
        <div class="article-content">{$content}</div>
    </body>
    </html>
HTML;
}

header("Content-Type:text/html; charset=utf-8");

if(isset($_GET['pageid'])) {
    $site = isset($_GET['site']) ? $_GET['site'] : 'zh';
    echo show_article_detail(intval($_GET['pageid']), $site);
} elseif(isset($_GET['action']) && $_GET['action'] === 'random') {
    $site = isset($_GET['site']) ? $_GET['site'] : 'zh';
    $random_pageid = get_random_page($site);
    if ($random_pageid) {
        echo show_article_detail($random_pageid, $site);
    } else {
        // 如果获取随机页面失败，显示错误信息
        $topbar = generate_topbar("随机页面");
        echo <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <title>Kindle百科 - 随机页面</title>
            <meta name="viewport" content="width=device-width">
            <link rel="stylesheet" href="/css/main.css">
            <link rel="stylesheet" href="/css/wiki.css">
        </head>
        <body>
            {$topbar}
            <div class="container">
                <p class="error-message">无法获取随机页面，请重试。</p>
            </div>
        </body>
        </html>
HTML;
    }
} else {
    $keyword = trim($_GET['q'] ?? '');
    $site = isset($_GET['site']) ? $_GET['site'] : 'zh';
    
    if(empty($keyword)) {
        echo show_search_form();
    } else {
        $results = get_search_results($keyword, $site);
        $GLOBALS['result_count'] = count($results);
        echo show_search_results(htmlspecialchars($keyword), $results, $site);
    }
}
?>