<?php

function generate_topbar($title) {
    return <<<HTML
    <div class="top-bar">
        <a href="javascript:history.back()" class="nav-btn">« 返回</a>
        <a href="/" class="nav-btn"># 主页</a>
        <span class="divider">|</span>
        <div class="title-wrapper">
            <h1 class="page-title">{$title}</h1>
        </div>
    </div>
HTML;
}

$remaining = 0;
if (isset($_GET['time'])) {
    $duration = intval($_GET['time']);
    if ($duration > 0) {
        $remaining = $duration * 60;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Kindle泡面钟</title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/timer.css">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="pragma" content="no-cache">
</head>
<body>
    <?php echo generate_topbar("Kindle泡面钟"); ?>
    
    <?php if ($remaining > 0): ?>
        <h1 class="title">正在泡面</h1>
        <div id="countdown"><?= gmdate("i:s", $remaining) ?></div>
        <a href="timer.php" class="reset-button">重置</a>
    <?php else: ?>
        <h1 class="title">快速开始</h1>
        <div class="button-container">
            <div class="button-row">
                <a href="?time=1" class="big-button">1分钟</a>
                <a href="?time=3" class="big-button">3分钟</a>
                <a href="?time=5" class="big-button">5分钟</a>
                <a href="?time=10" class="big-button">10分钟</a>
            </div>

            <h1 class="title">自定义时长</h1>
            <form class="custom-form" method="GET">
                <input type="text" class="time-input" name="time" placeholder="分钟数">
                <button type="submit" class="submit-btn">开始</button>
            </form>
        </div>
    <?php endif; ?>

    <script>
        <?php if ($remaining > 0): ?>
            var remaining = <?= $remaining ?>;
            var countdownElement = document.getElementById('countdown');
            
            function updateDisplay() {
                var minutes = Math.floor(remaining / 60);
                var seconds = remaining % 60;
                countdownElement.textContent = 
                    (minutes < 10 ? '0' + minutes : minutes) + ':' + 
                    (seconds < 10 ? '0' + seconds : seconds);
            }

            var interval = setInterval(function() {
                remaining--;
                
                if (remaining >= 0) {
                    updateDisplay();
                }

                if (remaining <= 0) {
                    clearInterval(interval);
                    var flashCount = 0;
                    var flashInterval = setInterval(function() {
                        document.body.style.backgroundColor = 
                            document.body.style.backgroundColor === 'red' ? 'white' : 'red';
                        if (++flashCount >= 20) {
                            clearInterval(flashInterval);
                            document.body.style.backgroundColor = 'white';
                        }
                    }, 500);
                }
            }, 1000);
        <?php endif; ?>
    </script>
</body>
</html>