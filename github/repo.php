<?php

require_once 'functions.php';

$owner = $_GET['owner'] ?? '';
$repo = $_GET['repo'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo "$owner/$repo"; ?></title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/github.css">
</head>
<body>
    <?php echo generate_topbar("$owner/$repo"); ?>
    <div class="content">
        <?php
        $repo_url = "https://api.github.com/repos/$owner/$repo";
        $readme_url = "https://api.github.com/repos/$owner/$repo/readme";
        
        $context = stream_context_create(['http' => ['header' => 'User-Agent: Kindle-Github-Viewer']]);
        
        $repo_info = json_decode(file_get_contents($repo_url, false, $context), true);
        
        echo '<div class="repo-info">';
        echo '<p><strong>描述：</strong>'.($repo_info['description'] ?? '无').'</p>';
        echo '<p><strong>链接：</strong><a href="'.$repo_info['html_url'].'">访问仓库</a></p>';
        echo '<p><strong>许可证：</strong>'.($repo_info['license']['name'] ?? '无').'</p>';
        echo '<p>Star '.number_format($repo_info['stargazers_count']).'</p>';
        echo '<p>Watching '.number_format($repo_info['subscribers_count']).'</p>';
        echo '<p>Fork '.number_format($repo_info['forks_count']).'</p>';
        echo '</div>';

        $readme = json_decode(file_get_contents($readme_url, false, $context), true);
        $readme_content = base64_decode($readme['content']);
        
        echo '<h3>README.md</h3>';
        echo '<div class="markdown-body">';
        echo parse_markdown($readme_content);
        echo '</div>';
        ?>
        
        <p style="margin-top:20px;">
            <a href="issues.php?owner=<?php echo urlencode($owner)?>&repo=<?php echo urlencode($repo)?>" class="nav-btn">查看 Issues</a>
        </p>
    </div>
</body>
</html>