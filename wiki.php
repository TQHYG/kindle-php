<?php
define('CACHE_DIR', 'pic/');
if (!file_exists(CACHE_DIR)) mkdir(CACHE_DIR, 0755, true);

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

function show_search_form() {
    $topbar = generate_topbar("维基搜索");
    return <<<HTML
    <!DOCTYPE html>
    <html>
    <head>
        <title>Kindle维基百科</title>
        <meta name="viewport" content="width=device-width">
        <link rel="stylesheet" href="/css/main.css">
        <link rel="stylesheet" href="/css/wiki.css">
    </head>
    <body>
        {$topbar}
        <div class="container">
            <h2 class="title">维基百科查询</h2>
            <form action="wiki.php" method="get" class="search-form">
                <input type="text" id="search-input" name="q" placeholder="输入查询关键词" required>
                <div class="search-btns">
                    <button type="submit" name="lang" value="zh" class="btn">中文搜索</button>
                    <button type="submit" name="lang" value="en" class="btn">英文搜索</button>
                </div>
            </form>
        </div>
    </body>
    </html>
HTML;
}


function get_search_results($keyword, $lang = 'zh') {
    $domain = ($lang === 'en') ? 'en' : 'zh';
    $api_url = "https://{$domain}.wikipedia.org/w/api.php?action=query&format=json&list=search&srsearch=".urlencode($keyword);
    $response = json_decode(get_by_curl($api_url), true);
    return $response['query']['search'] ?? [];
}

function show_search_results($keyword, $results, $lang) {
    $lang_display = ($lang === 'en') ? '英文' : '中文';
    $topbar = generate_topbar("{$lang_display}搜索 - {$keyword}");
    $html = <<<HTML
    <!DOCTYPE html>
    <html>
    <head>
        <title>Kindle维基百科</title>
        <meta name="viewport" content="width=device-width">
        <link rel="stylesheet" href="/css/main.css">
        <link rel="stylesheet" href="/css/wiki.css">
    </head>
    <body>
        {$topbar}
        <div class="container">
            <h3 class="search-title">找到{$GLOBALS['result_count']}条相关结果（{$lang_display}）</h3>
            <ul class="result-list">
HTML;
    
    foreach ($results as $item) {
        $pageid = $item['pageid'];
        $html .= <<<ITEM
        <li class="result-item">
            <a href="wiki.php?pageid={$pageid}&lang={$lang}" class="result-link">
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


function show_article_detail($pageid, $lang = 'zh') {
    $domain = ($lang === 'en') ? 'en' : 'zh';
    $api_url = "https://{$domain}.wikipedia.org/w/api.php?action=query&format=json&prop=extracts&pageids={$pageid}";
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
        <title>Kindle维基百科</title>
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
    $lang = isset($_GET['lang']) ? $_GET['lang'] : 'zh';
    echo show_article_detail(intval($_GET['pageid']), $lang);
} else {
    $keyword = trim($_GET['q'] ?? '');
    $lang = isset($_GET['lang']) ? $_GET['lang'] : 'zh';
    
    if(empty($keyword)) {
        echo show_search_form();
    } else {
        $results = get_search_results($keyword, $lang);
        $GLOBALS['result_count'] = count($results);
        echo show_search_results(htmlspecialchars($keyword), $results, $lang);
    }
}
?>