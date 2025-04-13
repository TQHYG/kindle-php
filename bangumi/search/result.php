<?php
require_once __DIR__ . '/../functions.php';

try {
    // 验证基础参数
    if (empty($_GET['keyword']) || empty($_GET['type'])) {
        throw new Exception('缺少必要参数');
    }
    
    $keyword = trim($_GET['keyword']);
    $type = intval($_GET['type']);
    $current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $start = ($current_page - 1) * 4;

    // 构建API请求
    $api_url = sprintf(
        "https://api.bgm.tv/search/subject/%s?type=%d&responseGroup=large&max_results=4&start=%d",
        urlencode($keyword),
        $type,
        $start
    );

    $response = cached_fetch($api_url);
    $total = $response['results'] ?? 0;
    $items = $response['list'] ?? [];

    // 生成结果列表
    $items_html = '';
    foreach ($items as $item) {
        $items_html .= sprintf('
            <a href="/bangumi/article.php?id=%d" class="bangumi-item">
                <img src="%s" alt="封面" class="bangumi-cover">
                <div class="bangumi-info">
                    <h3 class="title-cn">%s</h3>
                    %s
                    <div class="meta-line">
                        <span class="score">评分：%.1f</span>
                        <span class="rank">#%d</span>
                    </div>
                    <p class="summary">%s</p>
                    <p class="air-date">%s</p>
                </div>
            </a>',
            $item['id'],
            htmlspecialchars($item['images']['small'] ?? "/bangumi/placeholder_related.jpg"),
            htmlspecialchars($item['name_cn'] ?? $item['name']),
            (!empty($item['name_cn']) && $item['name_cn'] != $item['name']) ? 
                '<p class="title-jp">'.htmlspecialchars($item['name']).'</p>' : '',
            $item['rating']['score'] ?? 0,
            $item['rank'] ?? 0,
            htmlspecialchars(trim($item['summary'])),
            $item['air_date'] ?? '未定档'
        );
    }

    // 分页处理
    $total_pages = ceil($total / 4);
    $query_params = http_build_query([
        'keyword' => $keyword,
        'type' => $type
    ]);
    
    $pagination = '';
    if ($total_pages > 1) {
        if ($current_page > 1) {
            $pagination .= sprintf('<a href="?%s&page=%d" class="nav-btn">« 上一页</a>',
                $query_params, $current_page - 1);
        }
        
        if ($current_page < $total_pages) {
            $pagination .= sprintf('<a href="?%s&page=%d" class="nav-btn">下一页 »</a>',
                $query_params, $current_page + 1);
        }
    }

    $pageshower = sprintf('<p">第 %d 页/共 %d 页</p>',
            $current_page, $total_pages);

    $topbar = generate_topbar('搜索结果');
    echo <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>搜索结果 - Bangumi</title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/bangumi.css">
</head>
<body>
    $topbar
    <div class="container">
        $pageshower
        <div class="bangumi-list">$items_html</div>
        <div class="pagination">$pagination</div>
    </div>
</body>
</html>
HTML;

} catch (Exception $e) {
    show_error($e->getMessage());
}