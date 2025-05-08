<?php
require 'functions.php';
check_site_param();

$site = $_GET['site'];
$page = max(1, intval($_GET['page'] ?? 1));
$protocol = detect_protocol($site); 
$api_url = "$protocol://$site/wp-json/wp/v2/posts?page=$page&per_page=4";

$response = wp_curl_request($api_url);

$posts = json_decode($response, true);

$topbar = generate_topbar('最新文章');
echo <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>最新文章</title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/wp.css">
</head>
<body>
    $topbar
    <div class="container">
        <p>第{$page}页</p>
        <div class="article-list">
HTML;

foreach ($posts as $post) {
    echo <<<ITEM
    <div class="article-item">
        <div class="article-content">
            <h3>{$post['title']['rendered']}</h3>
            <p>{$post['excerpt']['rendered']}</p>
            <a href="article.php?site=$site&id={$post['id']}" class="btn">阅读全文</a>
        </div>
    </div>
ITEM;
}

echo <<<HTML
        </div>
        <div class="pagination">
HTML;
if ($page > 1) {
    echo '<a href="?site='.$site.'&page='.($page-1).'" class="btn">« 上一页</a> ';
}
echo '<a href="?site='.$site.'&page='.($page+1).'" class="btn">» 下一页</a>';
echo <<<HTML
        </div>
    </div>
</body>
</html>
HTML;
?>