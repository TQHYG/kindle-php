<?php require_once 'functions.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Bangumi on Kindle</title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/bangumi.css">
</head>
<body>
    <?= generate_topbar("Bangumi on Kindle") ?>
    
    <div class="content">
        <h1 class="title">Bangumi on Kindle</h1>
        <img src="logo.png" alt="Bangumi Logo" class="site-logo">
        
        <div class="button-container">
            <a href="daily.php" class="main-btn">每日放送</a>
            <a href="tops/" class="main-btn">排行榜</a>
            <a href="search/" class="main-btn">条目搜索</a>
        </div>
    </div>
</body>
</html>