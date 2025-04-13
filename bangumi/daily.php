<?php
require_once 'functions.php';

date_default_timezone_set('Asia/Shanghai');
$currentDate = new DateTime();
$selectedDay = isset($_GET['day']) ? (int)$_GET['day'] : (int)$currentDate->format('N');

try {
    $apiData = cached_fetch('https://api.bgm.tv/calendar');

    if (!is_array($apiData)) {
        throw new Exception('API返回数据格式异常');
    }
    
    $weekdaysData = array_fill(1, 7, ['ja' => '月耀日', 'cn' => '星期一', 'date' => '--']);
    foreach ($apiData as $dayData) {
        if (!isset($dayData['weekday']['id'])) continue;

        $id = $dayData['weekday']['id'];
        $date = isset($dayData['items'][0]['air_date']) ? 
            date('m-d', strtotime($dayData['items'][0]['air_date'])) : '--';
        
        $weekdaysData[$id] = [
            'ja' => $dayData['weekday']['ja'],
            'cn' => $dayData['weekday']['cn'],
            'date' => $date
        ];
    }

    $bangumiList = [];
    foreach ($apiData as $dayData) {
        if (!is_array($dayData) || 
            !isset($dayData['weekday']['id']) || 
            !is_array($dayData['items'])) {
            continue;
        }

        if ($dayData['weekday']['id'] == $selectedDay && !empty($dayData['items'])) {
            foreach ($dayData['items'] as $item) {
                $bangumiList[] = [
                    'id' => $item['id'],
                    'image' => $item['images']['common'],
                    'title_cn' => $item['name_cn'] ?: $item['name'],
                    'title_jp' => $item['name'],
                    'score' => round($item['rating']['score'], 1),
                    'rank' => $item['rank'] ?? 'N/A',
                    'air_date' => $item['air_date']
                ];
            }
            break;
        }
    }
} catch (Exception $e) {
    error_log('Bangumi Error: ' . $e->getMessage());
    show_error('系统暂时不可用，请稍后重试');
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>每日放送</title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/bangumi.css">
</head>
<body>
    <?= generate_topbar("每日放送") ?>
    
    <div class="week-nav">
        <?php for ($day=1; $day<=7; $day++): ?>
            <a href="?day=<?= $day ?>" 
               class="<?= ($day == $selectedDay) ? 'selected-day' : 'weekday-btn' ?>">
                <span class="cn-week"><?= $weekdaysData[$day]['cn'] ?? '星期一' ?></span><br>
                <span class="ja-week"><?= $weekdaysData[$day]['ja'] ?? '月耀日' ?></span><br>
                <span class="week-date"><?= $weekdaysData[$day]['date'] ?? '--' ?></span>
            </a>
        <?php endfor; ?>
    </div>

    <div class="bangumi-list">
        <?php if (!empty($bangumiList)): ?>
            <?php foreach ($bangumiList as $item): ?>
                <a href="article.php?id=<?= $item['id'] ?>" class="bangumi-item">
                    <img src="<?= htmlspecialchars($item['image'] ?? "/bangumi/placeholder_related.jpg") ?>" 
                        alt="封面" 
                        class="bangumi-cover">
                    <div class="bangumi-info">
                        <h3 class="title-cn"><?= htmlspecialchars($item['title_cn']) ?></h3>
                        <p class="title-jp"><?= htmlspecialchars($item['title_jp']) ?></p>
                        <div class="meta-line">
                            <span class="score">评分：<?= $item['score'] ?></span>
                            <span class="rank">#<?= $item['rank'] ?></span>
                        </div>
                        <p class="air-date"><?= $item['air_date'] ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-data">今日没有番剧数据</p>
        <?php endif; ?>
    </div>
</body>
</html>