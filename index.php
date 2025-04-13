<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Kindle工具站</title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/home.css">
</head>
<body>

    <?php
    $hitokoto = '获取失败';
    try {
        $response = file_get_contents('https://v1.hitokoto.cn/');
        if ($response !== false) {
            $data = json_decode($response, true);
            if (isset($data['hitokoto']) && isset($data['from'])) {
                $hitokoto = htmlspecialchars("{$data['hitokoto']} ——「{$data['from']}」");
            }
        }
    } catch (Exception $e) {
        $hitokoto = "⚠️ 一言加载失败";
    }
    ?>

    <h1 class="title">天穹何以高的Kindle工具站</h1>
    <p style="text-align:center">专为电子墨水屏优化的知识工具</p>
    <p id="hitokoto" style="text-align:center"><?php echo $hitokoto; ?></p>
    
    <div class="hmenu">
        <a href="clock/clock.html" class="hbtn"><span>屏幕时钟</span></a>
        <a href="timer.php" class="hbtn"><span>泡面倒计时</span></a>
        <a href="wiki.php" class="hbtn"><span>维基百科检索</span></a>
        <a href="/bangumi/index.php" class="hbtn"><span>浏览Bangumi</span></a>
        <a href="wp/index.php" class="hbtn"><span>WordPress阅读</span></a>
        <a href="translate.php" class="hbtn"><span>多语言翻译</span></a>
    </div>

</body>
</html>