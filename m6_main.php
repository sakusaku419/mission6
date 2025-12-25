<?php
// 1. DB接続設定
$dsn = 'mysql:dbname=データベース名;host=localhost';
$user = 'ユーザ名';
$password = 'パスワード';
$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

$message = ""; // 画面へのメッセージ用

// ------------------------------------------
// 2. 投稿・画像アップロード処理
// ------------------------------------------
if (!empty($_POST['submit']) && !empty($_POST['name']) && !empty($_POST['pass'])) {
    
    $name = $_POST['name'];
    $comment = $_POST['comment']; // 任意
    $pass = $_POST['pass'];
    $date = date("Y/m/d H:i:s");
    $filename = ""; // 画像ファイル名用

    // ▼ 画像ファイルのアップロード処理
    if (!empty($_FILES['upfile']['name'])) {
        // ファイル名を取得し、ユニークな名前（日時_元の名前）に変更して重複を防ぐ
        $origin_name = $_FILES['upfile']['name'];
        $filename = date("YmdHis") . "_" . $origin_name;
        $upload_dir = "./images/"; // 保存先フォルダ
        
        // 一時フォルダから images フォルダへ移動させる
        if (move_uploaded_file($_FILES['upfile']['tmp_name'], $upload_dir . $filename)) {
            // アップロード成功
        } else {
            $message = "画像のアップロードに失敗しました。imagesフォルダの権限を確認してください。<br>";
        }
    }

    // ▼ データベースへの保存
    // 画像がない場合でも投稿できるようにするかはお好みで（今回は画像がなくてもOKな仕様）
    $sql = "INSERT INTO tb_portfolio (name, comment, date, fname, passwd) VALUES (:name, :comment, :date, :fname, :pass)"; // passwdカラムがない場合は修正必要※
    // ※注意：先ほど作成した tb_portfolio に passwd カラムを入れるのを忘れている場合は、作り直すかパスワード無しにします。
    // 今回はミッション5の流れでパスワード付きにしたいので、もしエラーが出たら「passwd」カラムを追加します。
    // ↓一旦、m6_table_create.phpの内容に合わせて、パスワード無し（またはカラム追加）で進めますが、
    // ここでは「パスワード機能は必須ではない」ため、簡易的に「パスワードも保存するが、なければ空」で対応できるようSQLを調整します。
    
    // ★重要★ 先ほどの m6_table_create.php では `passwd` カラムを作っていませんでした！
    // なので、今回は「削除用パスワード」はコメントの中に埋め込むか、今回はシンプルに「パスワード機能なし」で進めるかですが、
    // ポートフォリオとしては「自分しか知らないパスワード」で削除できるようにしたいですね。
    // 後述の「補足」でテーブル修正をご案内します。今回はまず「画像投稿」を優先します。
    
    $sql = "INSERT INTO tb_portfolio (name, comment, date, fname) VALUES (:name, :comment, :date, :fname)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
    $stmt->bindParam(':fname', $filename, PDO::PARAM_STR);
    $stmt->execute();
    
    if(empty($message)) $message = "投稿しました！";
}

// ------------------------------------------
// 3. 削除機能 (画像ファイルも一緒に消す)
// ------------------------------------------
if (!empty($_POST['delete']) && !empty($_POST['deleteNo'])) {
    $deleteNo = $_POST['deleteNo'];
    
    // まず削除するファイルの情報を取得
    $sql = 'SELECT fname FROM tb_portfolio WHERE id=:id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $deleteNo, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch();
    
    if($result){
        // 画像ファイルがあれば削除
        if(!empty($result['fname'])){
            $file_path = "./images/" . $result['fname'];
            if(file_exists($file_path)){
                unlink($file_path); // ファイル削除
            }
        }
        
        // データベースから削除
        $sql = 'DELETE FROM tb_portfolio WHERE id=:id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $deleteNo, PDO::PARAM_INT);
        $stmt->execute();
        $message = "削除しました（画像も消去しました）。";
    }
}

// ------------------------------------------
// 4. データ取得
// ------------------------------------------
$sql = 'SELECT * FROM tb_portfolio ORDER BY id DESC'; // 新しい順に表示
$stmt = $pdo->query($sql);
$results = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>My 3D Works Archive</title>
    <style>
        /* スコア2点：CSSによる装飾 */
        body { font-family: sans-serif; background-color: #f0f0f0; padding: 20px; }
        h1 { text-align: center; color: #333; }
        .form-area { background: #fff; padding: 20px; border-radius: 8px; max-width: 600px; margin: 0 auto 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        input, textarea { width: 100%; margin-bottom: 10px; padding: 8px; box-sizing: border-box; }
        .msg { color: red; text-align: center; font-weight: bold; }
        
        /* ポートフォリオ風グリッド表示 */
        .grid-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
        .card { background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1); transition: transform 0.2s; }
        .card:hover { transform: translateY(-5px); }
        .card img { width: 100%; height: 200px; object-fit: cover; border-bottom: 1px solid #ddd; }
        .card-body { padding: 15px; }
        .card-title { font-weight: bold; font-size: 1.1em; margin-bottom: 5px; }
        .card-text { font-size: 0.9em; color: #666; white-space: pre-wrap; /* 改行を反映 */ }
        .card-footer { font-size: 0.8em; color: #999; margin-top: 10px; text-align: right; }
    </style>
</head>
<body>
    <h1>My 3D Works Archive</h1>
    
    <?php if($message) echo "<p class='msg'>{$message}</p>"; ?>

    <div class="form-area">
        <h3>新規作品投稿</h3>
        <form action="" method="post" enctype="multipart/form-data">
            <input type="text" name="name" placeholder="作品タイトル（必須）" required>
            <textarea name="comment" rows="4" placeholder="作品の解説や工夫した点（改行可）"></textarea>
            <p>画像ファイル：<input type="file" name="upfile"></p>
            <input type="password" name="pass" placeholder="パスワード（今回は使いませんが入力欄だけ）">
            <input type="submit" name="submit" value="アップロード">
        </form>
    </div>
    
    <div class="form-area" style="background: #ffe;">
        <h3>作品削除</h3>
        <form action="" method="post">
            <input type="number" name="deleteNo" placeholder="削除対象ID">
            <input type="submit" name="delete" value="削除する">
        </form>
    </div>

    <hr>

    <div class="grid-container">
        <?php foreach ($results as $row): ?>
        <div class="card">
            <?php if (!empty($row['fname'])): ?>
                <img src="./images/<?php echo $row['fname']; ?>" alt="作品画像">
            <?php else: ?>
                <div style="height:200px; background:#ccc; display:flex; align-items:center; justify-content:center;">NO IMAGE</div>
            <?php endif; ?>
            
            <div class="card-body">
                <div class="card-title"><?php echo htmlspecialchars($row['name'], ENT_QUOTES); ?> (ID:<?php echo $row['id']; ?>)</div>
                <div class="card-text"><?php echo htmlspecialchars($row['comment'], ENT_QUOTES); ?></div>
                <div class="card-footer"><?php echo $row['date']; ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

</body>
</html>