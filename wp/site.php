<?php
require 'functions.php';
check_site_param();

$original_site = $_GET['site'];
$clean_site = sanitize_site_domain($original_site);
if ($clean_site !== $original_site) {
    $redirect_url = "site.php?site=" . urlencode($clean_site);
    header("Location: " . $redirect_url);
    exit;
}
$site_url = $clean_site;

function show_error($message) {
    global $site_url;
    $topbar = generate_topbar('连接失败');
    $retry_url = "site.php?site=" . urlencode($site_url);
    
    echo <<<HTML
    <head>
        <title>站点信息</title>
        <link rel="stylesheet" href="/css/main.css">
        <link rel="stylesheet" href="/css/wp.css">
    </head>
    <body>
        $topbar
        <div class="container">
            <h2 class="error-title">站点连接失败</h2>
            <div class="error-details">
                <p>错误信息：{$message}</p>
                <p>尝试地址：{$site_url}</p>
            </div>
            <div class="error-actions">
                <a href="{$retry_url}" class="btn">强制重试</a>
                <button onclick="history.back()" class="btn">返回修改</button>
                <a href="/" class="btn">返回主页</a>
            </div>
        </div>
    </body>
    HTML;
    exit;
}

$data = fetch_wp_data($site_url);

if (isset($data['error'])) {
    show_error($data['error']);
    exit;
}

$topbar = generate_topbar('站点信息');
echo <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>站点信息</title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/wp.css">
</head>
<body>
    $topbar
    <div class="container">
        <div class="site-info">
            <h2>{$data['site']['name']}</h2>
            <p>{$data['site']['description']}</p>
        </div>
        
        <a href="posts.php?site={$site_url}" class="btn">最新文章</a>
        <a href="search.php?site={$site_url}" class="btn">搜索文章</a>
        <a href="categories.php?site={$site_url}" class="btn">分类目录</a>
    </div>
</body>
</html>
HTML;
?>