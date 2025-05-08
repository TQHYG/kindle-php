<?php
require 'functions.php';

if (!isset($_GET['site']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$site = $_GET['site'];
$post_id = intval($_GET['id']);

$api_url = (strpos($site, 'http') === 0 ? $site : "http://$site") . "/wp-json/wp/v2/posts/{$post_id}";
$response = wp_curl_request($api_url);



$post_data = json_decode($response, true);

$post_title = strip_tags($post_data['title']['rendered']);
$topbar = generate_topbar($post_title);
$post_content = $post_data['content']['rendered'];

$dom = new DOMDocument();
libxml_use_internal_errors(true); 
$dom->loadHTML('<?xml encoding="utf-8" ?>' . $post_content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

foreach ($dom->getElementsByTagName('video') as $video) {
    $video->parentNode->removeChild($video);
}

foreach ($dom->getElementsByTagName('audio') as $audio) {
    $audio->parentNode->removeChild($audio);
}

foreach ($dom->getElementsByTagName('source') as $source) {
    $source->parentNode->removeChild($source);
}

foreach ($dom->getElementsByTagName('pre') as $pre) {
    $pre->setAttribute('style', 'margin:10px 0;');
}

foreach ($dom->getElementsByTagName('code') as $code) {
    $code->setAttribute('style', 'font-family:monospace;');
}

foreach ($dom->getElementsByTagName('img') as $img) {
    if ($img->hasAttribute('data-src')) {
        $real_src = $img->getAttribute('data-src');
        if (filter_var($real_src, FILTER_VALIDATE_URL)) {
            $img->setAttribute('src', $real_src);
            $img->removeAttribute('data-src');
        }
    }

    $img->removeAttribute('decoding');
    $img->removeAttribute('loading');
    
    $classes = explode(' ', $img->getAttribute('class'));
    $classes = array_filter($classes, function($c) { return $c !== 'lazy'; });
    $img->setAttribute('class', implode(' ', $classes));
    
    $style = 'max-width: 100%; height: auto; margin: 10px 0;';
    if (strpos($img->getAttribute('style'), 'display:') === false) {
        $style .= ' display: block;';
    }
    $img->setAttribute('style', $style);
}

$post_content = $dom->saveHTML();

echo <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>{$post_title}</title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/wp.css">
</head>
<body>
    $topbar
    <div class="container">
        <div class="article-content">
            <h1 class="post-title">{$post_title}</h1>
            <div class="post-body">
                {$post_content}
            </div>
            <div class="back-btn">
                <a href="posts.php?site={$site}" class="btn">« 返回列表</a>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
?>