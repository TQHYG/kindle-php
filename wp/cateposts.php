<?php
require 'functions.php';

if (!isset($_GET['site']) || !isset($_GET['category'])) {
    header("Location: index.php");
    exit;
}

$site = $_GET['site'];
$category_id = intval($_GET['category']);
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

$category_api = (strpos($site, 'http') === 0 ? $site : "http://$site") 
              . "/wp-json/wp/v2/categories/{$category_id}";

$response = wp_curl_request($category_api);
$category_data = json_decode($response, true);

$posts_api = (strpos($site, 'http') === 0 ? $site : "http://$site")
           . "/wp-json/wp/v2/posts?categories={$category_id}"
           . "&page={$current_page}&per_page=4&orderby=date&order=desc";

$response = wp_curl_request($posts_api);
$posts = json_decode($response, true);

$category_name = $category_data && !isset($category_data['code']) 
               ? strip_tags($category_data['name']) 
               : '未知分类';
$topbar = generate_topbar($category_name);

echo <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>{$category_name}文章列表</title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/wp.css">
</head>
<body>
    $topbar
    <div class="container">
        <h2 class="category-title">{$category_name}</h2>
        <p>第{$current_page}页</p>
        <div class="article-list">
HTML;

if (!$posts || isset($posts['code'])) {
    echo '<p class="no-data">该分类下暂无文章</p><a href="javascript:history.back()" class="btn">« 返回上一页</a>';
} else {
    foreach ($posts as $post) {
        $title = strip_tags($post['title']['rendered']);
        $excerpt = strip_tags($post['excerpt']['rendered']);
        
        echo <<<ITEM
        <div class="article-item">
            <div class="article-content">
                <h3>{$title}</h3>
                <p>{$excerpt}</p>
                <a href="article.php?site={$site}&id={$post['id']}" class="btn">阅读全文</a>
            </div>
        </div>
ITEM;
    }
    
    $base_url = "cateposts.php?site=".urlencode($site)."&category={$category_id}";
    echo '<div class="pagination">';
    if ($current_page > 1) {
        echo '<a href="'.$base_url.'&page='.($current_page-1).'" class="btn">« 上一页</a> ';
    }
    echo '<a href="'.$base_url.'&page='.($current_page+1).'" class="btn">» 下一页</a>';
    echo '</div>';
}

echo <<<HTML
        </div>
    </div>
</body>
</html>
HTML;
?>