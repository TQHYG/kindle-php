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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text = $_POST['text'] ?? '';
    $source_lang = $_POST['source_lang'] ?? 'en';
    $target_lang = $_POST['target_lang'] ?? 'zh';

    if (!empty($text)) {
        $clean_text = preg_replace('/\n{3,}/', "\n\n", $text);
        
        $url = "https://api.mymemory.translated.net/get?q=".urlencode($clean_text)."&langpair={$source_lang}|{$target_lang}";
        $response = @file_get_contents($url);
        
        if ($response !== false) {
            $data = json_decode($response, true);
            $translation_result = $data['responseData']['translatedText'] ?? '翻译失败';
        } else {
            $error = "API请求失败，请稍后重试";
        }
    } else {
        $error = "请输入要翻译的内容";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kindle翻译</title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/translate.css"> 
</head>
<body>
    <?= generate_topbar("Kindle翻译器") ?>
    
    <div class="container">
        <form class="translation-form" method="post">
        <div class="lang-container">
            <div class="lang-selector">
                <select name="source_lang">
                    <option value="zh">中文</option>
                    <option value="en">英语</option>
                    <option value="ja">日语</option>
                </select>
            </div>
            
            <div class="lang-arrow">→</div>
            
            <div class="lang-selector">
                <select name="target_lang">
                    <option value="en">英语</option>
                    <option value="zh">中文</option>
                    <option value="es">西班牙语</option>
                    <option value="fr">法语</option>
                    <option value="ja">日语</option>
                </select>
            </div>
        </div>
            
            <textarea 
                class="text-input"
                name="text"
                rows="5"
                placeholder="输入要翻译的文字..."
            ><?= htmlspecialchars($_POST['text'] ?? '') ?></textarea>
            
            <div class="button-group">
                <button 
                    type="submit" 
                    class="translate-btn"
                    name="action" 
                    value="translate"
                >翻译</button>
            </div>
        </form>
        
        <?php if (isset($translation_result)): ?>
            <div class="translation-result">
                <?= nl2br(htmlspecialchars($translation_result)) ?>
            </div>
        <?php elseif (isset($error)): ?>
            <div class="error-msg">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>