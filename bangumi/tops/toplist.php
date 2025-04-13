<?php
require_once __DIR__ . '/../functions.php';

try {
    $type = isset($_GET['type']) ? intval($_GET['type']) : 2;
    $year = isset($_GET['year']) ? intval($_GET['year']) : null;
    $month = isset($_GET['month']) ? intval($_GET['month']) : null;
    $current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($current_page - 1) * 4;

    $api_url = "https://api.bgm.tv/v0/subjects?type={$type}&sort=rank";
    if ($year) $api_url .= "&year={$year}";
    if ($month) $api_url .= "&month={$month}";
    $api_url .= "&limit=4&offset={$offset}";

    $response = cached_fetch($api_url);
    if (!isset($response['data'])) throw new Exception('无效的API响应');

    $total = $response['total'] ?? 0;
    $total_pages = ceil($total / 4);
    $prev_page = max(1, $current_page - 1);
    $next_page = min($total_pages, $current_page + 1);

    $query_params = http_build_query([
        'type' => $type,
        'year' => $year,
        'month' => $month
    ]);
    $pagination = '';

    if ($current_page > 1) {
        $pagination .= sprintf('<a href="?%s&page=%d" class="nav-btn">« 上一页</a>',
                        $query_params, 
                        $prev_page);
    }
    if ($current_page < $total_pages) {
        $pagination .= sprintf('<a href="?%s&page=%d" class="nav-btn">下一页 »</a>',
                        $query_params,
                        $next_page);
    }

    $topbar = generate_topbar('排行榜详情');
    $items_html = '';

    foreach ($response['data'] as $item) {
        $summary = mb_substr(trim($item['summary']), 0, 40, 'UTF-8');
        if (mb_strlen($item['summary']) > 40) $summary .= '...';
        
        $tags_html = '';
        if (!empty($item['meta_tags'])) {
            $tags = array_slice($item['meta_tags'], 0, 5);
            foreach ($tags as $tag) {
                $tags_html .= sprintf('<span class="tag">%s</span>', htmlspecialchars($tag));
            }
        }

        $items_html .= sprintf('
            <a href="/bangumi/article.php?id=%d" class="bangumi-item">
                <img src="%s" alt="封面" class="bangumi-topcover">
                <div class="bangumi-info">
                    <h3 class="title-cn">%s</h3>
                    %s
                    <div class="meta-line">
                        <span class="score">评分：%.1f</span>
                        <span class="rank">#%d</span>
                    </div>
                    <p class="summary">%s</p>
                    %s
                    <p class="air-date">%s</p>
                </div>
            </a>',
            $item['id'],
            htmlspecialchars($item['images']['small'] ?? "/bangumi/placeholder_related.jpg"),
            htmlspecialchars($item['name_cn'] ?? $item['name']),
            (!empty($item['name_cn']) && $item['name_cn'] != $item['name']) ? 
                '<p class="title-jp">'.htmlspecialchars($item['name']).'</p>' : '',
            $item['rating']['score'] ?? 0,
            $item['rating']['rank'] ?? 0,
            htmlspecialchars($summary),
            $tags_html ? '<div class="tag-cloud">'.$tags_html.'</div>' : '',
            $item['date'] ?? '未定档'
        );
    }

    echo <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>排行榜 - Bangumi</title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/bangumi.css">
</head>
<body>
    $topbar
    <div class="container">
        <p>第{$current_page}页</p>
        <div class="bangumi-list">$items_html</div>
        <div class="pagination">$pagination</div>
    </div>
</body>
</html>
HTML;

} catch (Exception $e) {
    show_error($e->getMessage());
}