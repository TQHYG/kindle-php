<?php
require_once __DIR__ . '/../functions.php';

$type_options = [
    2 => '动画',
    1 => '书籍',
    3 => '音乐',
    4 => '游戏',
    6 => '三次元'
];

$topbar = generate_topbar('搜索Bangumi');
$keyword = htmlspecialchars($_GET['keyword'] ?? '');
$selected_type = $_GET['type'] ?? 2;

echo <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>搜索 - Bangumi</title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/bangumi.css">
</head>
<body>
    $topbar
    <div class="content">
        <h1 class="title">在Bangumi上搜索</h1>
        <form method="GET" action="result.php" class="search-form">
            <input type="text" name="keyword" 
                   placeholder="输入搜索关键词" 
                   class="main-btn"
                   style="width:80%; margin-bottom:15px;"
                   value="$keyword">
            
            <h1 class="title">搜索类型</h1>
            <select name="type" class="main-btn">
HTML;

foreach ($type_options as $value => $label) {
    $selected = $value == $selected_type ? 'selected' : '';
    echo "<option value='$value' $selected>$label</option>";
}

echo <<<HTML
            </select>
            
            <button type="submit" 
                    class="main-btn"
                    style="width:80%; margin-top:20px;
                           background:#f0f0f0;">
                搜索
            </button>
        </form>
    </div>
</body>
</html>
HTML;