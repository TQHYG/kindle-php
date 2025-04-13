<?php
require 'functions.php';
check_site_param();

$site = $_GET['site'];
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

$api_url = (strpos($site, 'http') === 0 ? $site : "http://$site") 
         . "/wp-json/wp/v2/categories?page={$current_page}&per_page=4";
$categories = json_decode(@file_get_contents($api_url), true);

$topbar = generate_topbar('分类目录');
echo <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>分类目录</title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/wp.css">
</head>
<body>
    $topbar
    <div class="container">
        <h2 class="category-title">{$site} 的分类目录</h2>
        <p>第{$current_page}页</p>
        <div class="category-list">
HTML;

if (!$categories || isset($categories['code'])) {
    echo '<p class="no-data">无法获取分类数据</p><a href="javascript:history.back()" class="btn">« 返回上一页</a>';
} else if (empty($categories)) {
    echo '<p class="no-data">该站点暂无分类</p><a href="javascript:history.back()" class="btn">« 返回上一页</a>';
} else {
    foreach ($categories as $cat) {
        $cat_name = strip_tags($cat['name']);
        $cat_count = intval($cat['count']);
        $cat_desc = strip_tags($cat['description']);
        
        echo <<<ITEM
        <div class="category-item">
            <div class="category-info">
                <h3>{$cat_name} <span class="count">({$cat_count}篇)</span></h3>
                <p class="desc">{$cat_desc}</p>
            </div>
            <a href="cateposts.php?site={$site}&category={$cat['id']}" class="btn">查看文章</a>
        </div>
ITEM;
    }
    
    echo '<div class="pagination">';
    if ($current_page > 1) {
        echo '<a href="categories.php?site='.urlencode($site).'&page='.($current_page-1).'" class="btn">« 上一页</a> ';
    }
    echo '<a href="categories.php?site='.urlencode($site).'&page='.($current_page+1).'" class="btn">» 下一页</a>';
    echo '</div>';
}

echo <<<HTML
        </div>
    </div>
</body>
</html>
HTML;
?>