<?php
session_start();
require 'library.php';

// セッション情報がなければログイン画面に戻す
if (isset($_SESSION['id']) && isset($_SESSION['name'])) {
    $id = $_SESSION['id'];
    $name = $_SESSION['name'];
} else {
    header('Location: login.php');
    exit();
}

// DBへの接続
$db = dbconnect();

// 投稿するボタンが押された時にメッセージ投稿をする
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);


    // 投稿内容と投稿者のidをdbに登録する
    $stmt = $db->prepare('insert into posts (message, member_id) values(?,?)');
    if (!$stmt) {
        die($db->error);
    }
    $stmt->bind_param('si', $message, $id);
    $success = $stmt->execute();
    if (!$success) {
        die($db->error);
    }

    // 同じフォームを何度も送信しないように、自分自身を呼び出してPOSTの内容を消す
    header('Location: index.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>ひとこと掲示板</title>

    <link rel="stylesheet" href="style.css" />
</head>

<body>
    <div id="wrap">
        <div id="head">
            <h1>ひとこと掲示板
            </h1>
        </div>
        <div id="content">
            <div style="text-align: right"><a href="logout.php">ログアウト</a></div>
            <div style="text-align: right"><a href="index_followee.php">フォロー中の投稿</a></div>
            <div style="text-align: right"><a href="profile_setting.php">登録情報設定</a></div>
            <form action="" method="post">
                <dl>
                    <dt><?php echo h($name); ?>さん、メッセージをどうぞ</dt>
                    <dd>
                        <textarea name="message" cols="50" rows="5"></textarea>
                    </dd>
                </dl>
                <div>
                    <p>
                        <!-- ⌘+enter または ctrl+enter が押された時にフォーム投稿をする -->
                        <script type="text/javascript">
                            // キーが押されたら関数enterを実行する
                            document.onkeydown = enter;

                            function enter() {
                                // metaKeyをkeyCode == 91としても同じと思ったがなぜか動かなかった
                                if ((event.metaKey && event.keyCode == 13) || (event.ctrlKey && event.keyCode == 13)) {
                                    document.getElementById('submit').click();
                                    return false
                                }
                            }

                            // // これは正しく動いてくれない。理由は不明だが ⌘ と 91 が対応していないと思われる
                            // function enter() {
                            //     if ((event.keyCode == 91 && event.keyCode == 13) || (event.ctrlKey && event.keyCode == 13)) {
                            //         document.getElementById('submit').click();
                            //         return false
                            //     }
                            // }
                        </script>
                        <input type="submit" id="submit" value="投稿する" />
                    </p>
                </div>
            </form>

            <?php

            $stmt = $db->prepare('select p.id, p.member_id, p.message, p.created, p.reply_count, m.name, m.picture
                                from posts p, members m
                                where m.id=p.member_id
                                order by id desc');
            if (!$stmt) {
                die($db->error);
            }
            $success = $stmt->execute();
            if (!$success) {
                die($db->error);
            }

            $stmt->bind_result($id, $member_id, $message, $created, $reply_count, $name, $picture);

            ?>

            <?php
            while ($stmt->fetch()) :
            ?>

                <div class="msg">
                    <?php if ($picture) { ?>
                        <img src="member_picture/<?php echo h($picture); ?>" width="48" height="48" alt="" />
                    <?php } else { ?>
                        <img src="member_picture/no_image_square.jpg" width="48" height="48" alt="" />
                    <?php } ?>
                    <p><?php echo h($message); ?>
                        <span class="name">
                            (<a href="profile.php?id=<?php echo h($member_id); ?>"><?php echo h($name); ?></a>)
                        </span>
                    </p>
                    <p class=" day"><a href="view.php?id=<?php echo h($id); ?>"><?php echo h($created); ?></a>
                        <?php if ($_SESSION['id'] === $member_id) : ?>
                            [<a href="delete.php?id=<?php echo h($id); ?>" style="color: #F33;">削除</a>]
                        <?php endif; ?>
                        <?php if ($_SESSION['id'] !== $member_id) : ?>
                            [<a href="reply.php?id=<?php echo h($id); ?>" style="color: #39F;">返信</a>]
                        <?php endif; ?>
                        [<a style="color: #45F;">返信数:<?php echo h($reply_count); ?></a>]
                    </p>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

</body>

</html>
