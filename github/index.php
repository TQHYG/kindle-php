<?php
require_once 'functions.php';

$title = '仓库搜索';
$search_query = $_GET['q'] ?? '';

if (!empty($search_query)) {
    $query = urlencode($search_query);
    $results = fetch_github_api("https://api.github.com/search/repositories?q=$query&sort=stars");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/github.css">
</head>
<body>
    <?php echo generate_topbar($title); ?>
    <div class="content">
    <h1 class="title">在Github上搜索</h1>
        <form class="search-form" action="index.php" method="GET">
            <input type="text" name="q" class="search-input" 
                   placeholder="输入搜索关键词..." value="<?php echo htmlspecialchars($search_query); ?>">
            <input type="submit" class="search-btn" value="搜索">
        </form>

        <?php if(!empty($search_query)): ?>
            <h3>搜索结果：</h3>
            <?php foreach($results['items'] as $item): ?>
                <div class="repo-info">
                    <p><strong>
                        <a href="repo.php?owner=<?php echo urlencode($item['owner']['login']); ?>&repo=<?php echo urlencode($item['name']); ?>">
                            <?php echo htmlspecialchars($item['full_name']); ?>
                        </a>
                    </strong></p>
                    <p><?php echo htmlspecialchars($item['description'] ?? '无描述'); ?></p>
                    <p>★ <?php echo number_format($item['stargazers_count']); ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>