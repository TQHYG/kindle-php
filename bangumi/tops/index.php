<?php
require_once __DIR__ . '/../functions.php';

$current_year = date('Y');
$years = [$current_year, $current_year - 1, $current_year - 2];
$topbar = generate_topbar('Bangumi排行榜');

echo <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>排行榜 - Bangumi</title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/bangumi.css">
    <script>
    function updateType(type) {
        var links = document.getElementsByClassName('dynamic-type');
        for (var i = 0; i < links.length; i++) {
            links[i].href = links[i].href.replace(/type=[0-9]+/, 'type=' + type);
        }
    }
    </script>
</head>
<body>
    $topbar
    <div class="content">
        <h2>选择类型</h2>
        <select id="typeSelect" class="main-btn" onchange="updateType(this.value)">
            <option value="2">动画</option>
            <option value="1">书籍</option>
            <option value="3">音乐</option>
            <option value="4">游戏</option>
            <option value="6">三次元</option>
        </select>

        <h2>选择排行榜</h2>
        <div class="button-container">
            <a href="toplist.php?type=2" class="main-btn dynamic-type">总排行榜</a>
HTML;

foreach ($years as $year) {
    echo <<<HTML
            <a href="toplist.php?type=2&year=$year" 
               class="main-btn dynamic-type">{$year}年度排行</a>
HTML;
}

echo <<<HTML
            <a href="advanced.php" class="main-btn" style="margin-top:40px;">更多</a>
        </div>
    </div>
</body>
</html>
HTML;