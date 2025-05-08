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
        $hitokoto = '⚠️ 一言加载失败';
        $apiUrl = 'https://international.v1.hitokoto.cn/';

        // 初始化 cURL 会话
        $ch = curl_init($apiUrl);

        // 设置 cURL 选项
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2); 
        curl_setopt($ch, CURLOPT_FAILONERROR, true); 

        try {
            $response = curl_exec($ch);
            if ($response === false) {
                throw new Exception('Curl error: ' . curl_error($ch));
            }

            $data = json_decode($response, true);
            if (isset($data['hitokoto']) && isset($data['from'])) {
                $hitokoto = htmlspecialchars("{$data['hitokoto']} ——「{$data['from']}」");
            }
        } catch (Exception $e) {
            $hitokoto = "⚠️ 一言加载失败";
        } finally {
            curl_close($ch); 
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
        <a href="/github/index.php" class="hbtn"><span>浏览Github</span></a>
    </div>

</body>
</html>