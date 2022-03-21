<?php
session_start();
require('library.php');

$error = [];
$email = '';
$password = '';


//submitボタンが押された時の挙動
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

    // 入力がされていない時にエラー情報を記録
    if ($email === '' || $password === '') {
        $error['login'] = 'blank';
    } else {
        // DBとログイン情報のチェック
        $db = dbconnect();
        $stmt = $db->prepare('select id, name, password, email, picture from members where email=? limit 1'); // limit 1　は、あまり意味はないが誤って大量のデータが漏出することを防ぐためのもの
        if (!$stmt) {
            die($db->error);
        }

        $stmt->bind_param('s', $email); // DBに登録されているハッシュ化されたPWを受け取っても無意味なのでここではemailだけ
        $success = $stmt->execute();
        if (!$success) {
            die($db->error);
        }

        $stmt->bind_result($id, $name, $hash, $email, $picture); // $変数$passwordは入力されたPWを格納する変数としているので$hashとした
        $stmt->fetch();

        if (password_verify($password, $hash)) {
            // ログイン成功

            // セッションidを再生成してセキュリティを高める
            session_regenerate_id();

            $_SESSION['id'] = $id;
            $_SESSION['name'] = $name;
            $_SESSION['email'] = $email;
            $_SESSION['image'] = $picture;

            header('Location: index.php');
            exit();
        } else { //パスワードが一致していなかった場合のエラー記録
            $error['login'] = 'failed';
        }
    }
}

?>



<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" type="text/css" href="style.css" />
    <title>ログインする</title>
</head>

<body>
    <div id="wrap">
        <div id="head">
            <h1>ログインする</h1>
        </div>
        <div id="content">
            <div id="lead">
                <p>メールアドレスとパスワードを記入してログインしてください。</p>
                <p>パスワードは全て[9999]</p>
                <p>入会手続きがまだの方はこちらからどうぞ。</p>
                <p>&raquo;<a href="join/">入会手続きをする</a></p>
            </div>
            <form action="" method="post">
                <dl>
                    <dt>メールアドレス</dt>
                    <dd>
                        <input type="text" name="email" size="35" maxlength="255" value="<?php echo h($email); ?>" />
                        <?php if (isset($error['login']) && $error['login'] === 'blank') : ?>
                            <p class="error">* メールアドレスとパスワードをご記入ください</p>
                        <?php endif; ?>
                        <?php if (isset($error['login']) && $error['login'] === 'failed') : ?>
                            <p class="error">* ログインに失敗しました。正しくご記入ください。</p>
                        <?php endif; ?>
                    </dd>
                    <dt>パスワード</dt>
                    <dd>
                        <input type="password" name="password" size="35" maxlength="255" value="<?php echo h($password); ?>" />
                    </dd>
                </dl>
                <div>
                    <input type="submit" value="ログインする" />
                </div>
            </form>
        </div>
    </div>
</body>

</html>
