<?php
// DB接続設定
$dsn = 'mysql:dbname=データベース名;host=localhost';
$user = 'ユーザ名';
$password = 'パスワード';
$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

// 新しいテーブルを作成（tb_portfolio）
// ★ポイント: 'fname' (file name) というカラムを追加しています！ここに画像の名前が入ります。
$sql = "CREATE TABLE IF NOT EXISTS tb_portfolio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name CHAR(32),
    comment TEXT,
    date DATETIME,
    fname TEXT
);";
$stmt = $pdo->query($sql);

echo "ポートフォリオ用のテーブルを作成しました！準備完了です。";
?>