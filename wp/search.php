<?php
require 'functions.php';
check_site_param();

$site = $_GET['site'];
$search_term = isset($_GET['s']) ? trim($_GET['s']) : '';
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

$page_title = $search_term ? "搜索：{$search_term}" : "文章搜索";
$topbar = generate_topbar($page_title);

$base_url = "search.php?site=" . urlencode($site) . "&s=" . urlencode($search_term);

echo <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>{$page_title}</title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/wp.css">
</head>
<body>
    $topbar
    <div class="container">
        <h2 class="search-title">在「{$site}」中搜索</h2>
        <form action="search.php" method="get">
            <input type="hidden" name="site" value="{$site}">
            <div class="input-group">
                <input type="text" name="s" value="{$search_term}" placeholder="输入搜索关键词" required>
                <button type="submit" class="btn">搜索</button>
            </div>
        </form>
HTML;

if ($search_term) {
    $api_url = (strpos($site, 'http') === 0 ? $site : "http://$site")
             . "/wp-json/wp/v2/posts?search=" . urlencode($search_term)
             . "&page={$current_page}&per_page=4";
    
    $response = @file_get_contents($api_url);
    $posts = $response ? json_decode($response, true) : null;
    
    if (!$posts || isset($posts['code'])) {
        echo <<<HTML
        <div class="search-results">
            <p>第{$current_page}页</p>
            <p class="no-results">未找到相关文章</p>
            <a href="javascript:history.back()" class="btn">« 返回上一页</a>
        </div>
HTML;
    } else {
        echo <<<HTML
        <p>第{$current_page}页</p>
        <div class="search-results">
        HTML;
        foreach ($posts as $post) {
            $title = strip_tags($post['title']['rendered']);
            $excerpt = strip_tags($post['excerpt']['rendered']);
            
            echo <<<ITEM
            <div class="search-item">
                <h3 class="post-title">{$title}</h3>
                <p class="post-excerpt">{$excerpt}</p>
                <a href="article.php?site={$site}&id={$post['id']}" class="btn">阅读全文</a>
            </div>
ITEM;
        }
        
        echo '<div class="pagination">';
        if ($current_page > 1) {
            echo '<a href="'.$base_url.'&page='.($current_page-1).'" class="btn">« 上一页</a> ';
        }
        echo '<a href="'.$base_url.'&page='.($current_page+1).'" class="btn">» 下一页</a>';
        echo '</div></div>';
    }
}

echo <<<HTML
    </div>
</body>
</html>
HTML;
?>