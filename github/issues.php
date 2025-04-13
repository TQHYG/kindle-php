<?php
require_once 'functions.php';

$owner = $_GET['owner'] ?? '';
$repo = $_GET['repo'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($title); ?></title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/github.css">
</head>
<body>
    <?php echo generate_topbar($title); ?>
    <div class="content">
        <?php if (!empty($issues)): ?>
            <?php foreach($issues as $issue): ?>
                <div class="repo-info">
                    <p><strong>#<?php echo $issue['number']; ?> <?php echo htmlspecialchars($issue['title']); ?></strong></p>
                    <p>状态：<?php echo $issue['state'] == 'open' ? '🟢 开放' : '🔴 关闭'; ?></p>
                    <p>作者：<?php echo htmlspecialchars($issue['user']['login']); ?></p>
                    <p><a href="<?php echo $issue['html_url']; ?>">查看详情</a></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="repo-info">
                <p>📭 当前仓库还没有任何Issue</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>