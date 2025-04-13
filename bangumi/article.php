<?php
require_once 'functions.php';

$subjectId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($subjectId <= 0) show_error('无效的条目ID');

try {
    $data = cached_fetch("https://api.bgm.tv/v0/subjects/{$subjectId}");
    
    if (!isset($data['id']) || $data['id'] != $subjectId) {
        throw new Exception('无效的API响应');
    }
} catch (Exception $e) {
    show_error("无法获取条目数据: " . $e->getMessage());
}

try {
    $characters = cached_fetch("https://api.bgm.tv/v0/subjects/{$subjectId}/characters");
    $characters = array_filter($characters, function($c) {
        return $c['type'] == 1; 
    });
} catch (Exception $e) {
    $characters = [];
}

try {
    $related = cached_fetch("https://api.bgm.tv/v0/subjects/{$subjectId}/subjects");
} catch (Exception $e) {
    $related = [];
}

$typeNames = [
    1 => '书籍',
    2 => '动画',
    3 => '音乐',
    4 => '游戏',
    6 => '现实'
];


$type = $data['type'] ?? 2; 
$collection = $data['collection'] ?? [];

$verbMap = [
    1 => '读',  // book
    2 => '看',  // anime
    3 => '听',  // music
    4 => '玩',  // game
    6 => '看' // real
];

$verb = $verbMap[$type] ?? '看'; 

$collection = array_merge(
    ['wish' => 0, 'collect' => 0, 'doing' => 0, 'on_hold' => 0, 'dropped' => 0],
    $collection
);

$ratingData = $data['rating']['count'] ?? [];
$totalVotes = array_sum($ratingData);
$maxVotes = max($ratingData ?: [1]); 


$infobox = [];
foreach ($data['infobox'] ?? [] as $item) {
    $value = $item['value'];
    if (filter_var($value, FILTER_VALIDATE_URL)) {
        $value = '<a href="' . htmlspecialchars($value) . '" target="_blank">' . htmlspecialchars($value) . '</a>';
    }
    $infobox[] = [
        'key' => htmlspecialchars($item['key']),
        'value' => $value
    ];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars(($data['name_cn'] ?? '') . " (" . $data['name'] . ")") ?></title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/bangumi.css">
</head>
<body>
    <?= generate_topbar(htmlspecialchars(($data['name_cn'] ?? '') . " (" . $data['name'] . ")")) ?>
    
    <div class="article-container">
        <div class="left-col">
            <img src="<?= htmlspecialchars($data['images']['common'] ?? 'placeholder.jpg') ?>" 
                 alt="封面" 
                 class="main-cover"
                 onclick="this.src='<?= htmlspecialchars($data['images']['large'] ?? '') ?>'">
            
            <div class="basic-info">
                <h2>基本信息</h2>
                <dl>
                    <dt>放送日期</dt>
                    <dd><?= htmlspecialchars($data['date'] ?? '未定') ?></dd>
                    
                    <dt>话数</dt>
                    <dd><?= $data['total_episodes'] ?? '未知' ?></dd>
                    
                    <dt>平台</dt>
                    <dd><?= htmlspecialchars($data['platform'] ?? '未定') ?></dd>
                </dl>
                <?php if (!empty($infobox)): ?>
                    <div class="section">
                        <h2>详细信息</h2>
                        <dl class="infobox">
                            <?php foreach ($infobox as $item): ?>
                            <dt><?= $item['key'] ?></dt>
                            <dd><?= $item['value'] ?></dd>
                            <?php endforeach; ?>
                        </dl>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="right-col">
            <div class="section">
                <h2><?= htmlspecialchars($data['name_cn'] ?? $data['name']) ?></h2>
                <p class="jp-title"><?= htmlspecialchars($data['name']) ?></p>
                
                <?php if (!empty($data['summary'])): ?>
                <div class="summary">
                    <h3>简介</h3>
                    <p><?= nl2br(htmlspecialchars($data['summary'])) ?></p>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="collection-box">
                <h3>收藏统计</h3>
                <table class="stats-table">
                    <tr class="stats-labels">
                        <th scope="col">想<?= $verb ?></th>
                        <th scope="col"><?= $verb ?>过</th>
                        <th scope="col">在<?= $verb ?></th>
                        <th scope="col">搁置</th>
                        <th scope="col">抛弃</th>
                    </tr>
                    <tr class="stats-numbers">
                        <td><?= number_format($collection['wish']) ?></td>
                        <td><?= number_format($collection['collect']) ?></td>
                        <td><?= number_format($collection['doing']) ?></td>
                        <td><?= number_format($collection['on_hold']) ?></td>
                        <td><?= number_format($collection['dro']) ?></td>
                    </tr>
                </table>
            </div>

            <div class="section">
                <h3>评分详情</h3>
                <div class="rating-box">
                    <div class="score-main">
                        综合评分：<?= round($data['rating']['score'] ?? 0, 1) ?>
                        <span class="rank">排名：#<?= $data['rating']['rank'] ?? 'N/A' ?></span>
                    </div>
                    
                    <div class="histogram">
                        <table class="rating-table">
                            <?php for ($i = 10; $i >= 1; $i--): 
                                $count = $ratingData[$i] ?? 0;
                                $percent = $totalVotes ? round(($count / $totalVotes) * 100) : 0;
                            ?>
                            <tr>
                                <td class="star"><?= $i ?>★</td>
                                <td>
                                    <div class="text-bar">
                                        <?= str_repeat('■', max(1, round($percent / 3))) ?>
                                    </div>
                                </td>
                                <td class="count"><?= $count ?></td>
                                <td class="percent">(<?= $percent ?>%)</td>
                            </tr>
                            <?php endfor; ?>
                        </table>
                    </div>
                </div>
            </div>

            
            <?php if (!empty($data['tags'])): ?>
            <div class="section">
                <h3>作品标签</h3>
                <div class="tag-cloud">
                    <?php foreach ($data['tags'] as $tag): ?>
                    <span class="tag"><?= htmlspecialchars($tag['name']) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($characters)): ?>
                <div class="section">
                    <h3>登场角色</h3>
                    <div class="character-grid">
                        <?php foreach ($characters as $c): ?>
                        <div class="character-item">
                            <img src="<?= htmlspecialchars($c['images']['grid'] ?? '') ?>" 
                                alt="<?= htmlspecialchars($c['name']) ?>" 
                                class="character-img"
                                onerror="this.src='/bangumi/placeholder_related.jpg'">
                            <div class="character-name"><?= htmlspecialchars($c['name']) ?></div>
                            <div class="character-role"><?= htmlspecialchars($c['relation']) ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($related)): ?>
                <div class="section">
                    <h3>关联作品</h3>
                    <div class="related-list">
                        <?php foreach ($related as $r): ?>
                        <a href="article.php?id=<?= $r['id'] ?>" class="related-item">
                            <img src="<?= htmlspecialchars($r['images']['small'] ?? '') ?>" 
                                alt="封面" 
                                class="related-cover"
                                onerror="this.src='placeholder_related.jpg'">
                            <div class="related-info">
                                <div class="related-title"><?= htmlspecialchars($r['name']) ?></div>
                                <?php if (!empty($r['name_cn'])): ?>
                                <div class="related-title-cn"><?= htmlspecialchars($r['name_cn']) ?></div>
                                <?php endif; ?>
                                <div class="related-meta">
                                    <span class="related-type"><?= $typeNames[$r['type']] ?? '其他' ?></span>
                                    <span class="related-relation"><?= htmlspecialchars($r['relation']) ?></span>
                                </div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</body>
</html>