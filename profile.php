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

$stmt = $db->prepare('SELECT m.id, m.name, m.picture, m.follower_count, m.followee_count
                            FROM members m
							WHERE m.id=? ');
if (!$stmt) {
	die($db->error);
}
// パラメータがストリング型なので整数化する（なくても問題なく動く）
$int_id = intval($_GET['id']);
$stmt->bind_param('i', $int_id);
$success = $stmt->execute();
if (!$success) {
	die($db->error);
}
$stmt->bind_result($member_id, $name, $picture, $follower_count, $followee_count);
$stmt->fetch();

?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<!-- jQueryの読み込み -->
	<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
	<!-- jsファイルの呼び出し -->
	<script type="text/javascript" src="follow_ajax.js"></script>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="style.css" />
	<title><?php echo h($name) ?>さんのページ</title>
</head>

<body>
	<div id="wrap">
		<div id="head">
			<h1><?php echo h($name) ?>さんのページ</h1>
		</div>
		<div id="content">
			<p style="text-align: right"><a href="index.php"><img src="images/ie_mark_ikkai.png" width="35" height="35" alt="" /></a></p>

			<div id="lead">

			</div>
			<div>
				<?php if ($picture) { ?>
					<img src="member_picture/<?php echo h($picture); ?>" width="150" height="150" alt="" />
				<?php } else { ?>
					<img src="member_picture/no_image_square.jpg" width="150" height="150" alt="" />
				<?php } ?>


				<script type="text/javascript">
					// phpのセッション変数の値をjsに渡すためのjsonエンコード
					var php_LoginUsrId =
						<?php echo json_encode($_SESSION['id']); ?>;
					var php_ProfileUsrId =
						<?php echo json_encode($_GET['id']); ?>;
				</script>

				<!-- フォローチェックをして、ボタンの出しわけ -->
				<form action="#" method="post">

					<!-- hiddenタイプで画面表示はさせずにid情報を渡すことができる。 -->
					<!-- <input type="hidden" class="follow_user_id" value="<?= $_SESSION['id'] ?>"> -->
					<!-- <input type="hidden" name="followed_user_id" value="<?= $_GET['id'] ?>"> -->

					<?php if (check_follow($_SESSION['id'], $_GET['id'])) { ?>
						<button class="unfollow_btn" type="button" name="unfollow">
							フォロー解除する
						</button>
					<?php } else { ?>
						<button class="follow_btn" type="button" name="follow">
							フォローする
						</button>
					<?php } ?>
				</form>
				フォロワー:<a href='follower_list.php?id=<?php echo h($_GET['id']); ?>'><?php echo h($follower_count); ?></a>
				<!-- 空白文字 -->
				&nbsp;&nbsp;&nbsp;
				　フォロー:<a href='followee_list.php?id=<?php echo h($_GET['id']); ?>'><?php echo h($followee_count); ?></a>
			</div>
			<!-- 空行を開けるための空のpタグ -->
			<p></p>
			<!-- 線を引く -->
			<p style=" border-bottom : solid "></p>
		</div>
	</div>
</body>

</html>
