<?php
require_once __DIR__ . '/../functions.php';

$current_year = date('Y');
$topbar = generate_topbar('更多筛选');

echo <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>更多筛选 - Bangumi</title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/bangumi.css">
</head>
<body>
    $topbar
    <div class="content">
        <form action="toplist.php" method="GET">
            <h2>选择类型</h2>
            <div class="form-group">
                <select name="type" class="main-btn" >
                    <option value="2" selected>动画</option>
                    <option value="1">书籍</option>
                    <option value="3">音乐</option>
                    <option value="4">游戏</option>
                    <option value="6">三次元</option>
                </select>
            </div>

            <h2>输入年份</h2>
            <div class="form-group">
                <input type="number" name="year" 
                       placeholder="输入年份（可选）"
                       min="1990" max="{$current_year}"
                       class="main-btn"
                       style="text-align:center;
                              -moz-appearance: textfield;
                              &::-webkit-inner-spin-button { display: none; }">
            </div>

            <h2>选择月份</h2>
            <div class="form-group">
                <select name="month" class="main-btn">
                    <option value="">全年</option>
HTML;

// 生成月份选项
for ($i = 1; $i <= 12; $i++) {
    $month_name = sprintf("%02d月", $i);
    echo "<option value=\"$i\">$month_name</option>";
}

echo <<<HTML
                </select>
            </div>

            <!-- 提交按钮 -->
            <div class="form-group">
                <button type="submit" 
                        class="main-btn"
                        style="background:#f0f0f0;
                               font-weight:bold;">
                    提交筛选条件
                </button>
            </div>
        </form>
    </div>
</body>
</html>
HTML;