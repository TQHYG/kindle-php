<?php
require 'functions.php';

$topbar = generate_topbar('Kindle Wordpress');
echo <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Kindle Wordpress</title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/wp.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    $topbar
    <div class="container">
        <h1 class="title">Kindle to Wordpress</h1>
        <p class="desc">输入Wordpress站点地址开始阅读</p>
        <form method="get" action="site.php" class="input-group">
            <input type="text" name="site" placeholder="example.com" required>
            <button type="submit" class="btn">连接</button>
        </form>


        <h2 class="quick-links-title">Or 你可以快速连接到以下站点</h2>
        <div class="quick-links">
            <a href="site.php?site=tqhyg.net" class="quick-btn">天远日记</a>
            <a href="site.php?site=mysqil.com" class="quick-btn">有希日记</a>
        </div>
    </div>
</body>
</html>
HTML;
?>