<?php
// 画像保存用フォルダの名前
$dir = "images";

echo "<h1>環境チェックと修復</h1>";

// 1. フォルダがあるか確認
if (!file_exists($dir)) {
    echo "フォルダ '{$dir}' が見つかりません。<br>";
    echo "→ 作成を試みます... ";
    
    // フォルダ作成（権限777で）
    if (mkdir($dir, 0777)) {
        echo "<b>成功しました！</b><br>";
    } else {
        echo "<b>失敗しました。</b><br>※手動でFTPソフト等を使い、'mission6'フォルダの中に 'images' フォルダを作ってください。<br>";
    }
} else {
    echo "フォルダ '{$dir}' は存在します。<br>";
}

echo "<hr>";

// 2. 書き込み権限があるか確認
if (is_writable($dir)) {
    echo "書き込み権限：<b>OKです！</b><br>";
    echo "→ <a href='m6_main.php'>m6_main.php に戻って投稿を試してください</a>";
} else {
    echo "書き込み権限：<b>ありません（エラー原因）</b><br>";
    echo "→ 権限の修正（777化）を試みます... ";
    
    if (chmod($dir, 0777)) {
        echo "<b>成功しました！</b><br>";
        echo "→ <a href='m6_main.php'>m6_main.php に戻って投稿を試してください</a>";
    } else {
        echo "<b>失敗しました。</b><br>※手動でFTPソフト等を使い、'images' フォルダの属性（パーミッション）を <b>777</b> に変更してください。<br>";
    }
}
?>