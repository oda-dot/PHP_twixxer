<?php
session_start();
require 'library.php';

// DBへの接続
$db = dbconnect();

// セッション情報がなければログイン画面に戻す
if (isset($_SESSION['id']) && isset($_SESSION['name'])) {
	$reply_user_id = $_SESSION['id'];
	$reply_user_name = $_SESSION['name'];
} else {
	header('Location: login.php');
	exit();
}

// パラメータを受け取って、正しいidでなければindex.phpに戻す
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$id) {
	header('Location: index.php?id=h($id)');
}

// フォームに入力がされて返信するボタンが押された時
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$reply_message = filter_input(INPUT_POST, 'reply_message', FILTER_SANITIZE_STRING);

	if ($reply_message === '') {
		$error['reply_message'] = 'blank';
	} else {

		// 投稿内容と投稿者のidをdbに登録する
		$stmt = $db->prepare(
			'insert into reply (reply_message, reply_user_id, replied_post_id) values(?,?,?)'
		);
		if (!$stmt) {
			die($db->error);
		}
		$stmt->bind_param('sii', $reply_message, $reply_user_id, $id);
		$success = $stmt->execute();
		//クエリのバッファリング（この後queryメソッドを使いたいから）
		$buffer = $stmt->store_result();
		if (!$success) {
			die($db->error);
		}

		// リプライ数カウンターをインクリメント
		$cnt_rslt = $db->query("update posts
				set reply_count = reply_count+1
				where posts.id = '" . $id . "'");
		if (!$cnt_rslt) {
			die($db->error);
		}
	}
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
			<h1>ひとこと掲示板</h1>
		</div>
		<div id="content">
			<p>&laquo;<a href="index.php">一覧にもどる</a></p>
			<?php

			$stmt = $db->prepare('select p.id, p.member_id, p.message, p.created, m.name, m.picture
                                from posts p, members m
                                where p.id=? and m.id=p.member_id
                                order by id desc');
			if (!$stmt) {
				die($db->error);
			}
			$stmt->bind_param('i', $id);
			$success = $stmt->execute();

			// クエリの実行結果をバッファリング　（後で再度prepareを使えるようにするため）
			$buffer = $stmt->store_result();

			if (!$success) {
				die($db->error);
			}
			$stmt->bind_result(
				$replied_user_id,
				$member_id,
				$message,
				$created,
				$name,
				$picture
			);
			if ($stmt->fetch()) {
			?>
				<div class="msg">
					<?php if ($picture) : ?>
						<img src="member_picture/<?php echo h($picture); ?>" width="48" height="48" alt="" />
					<?php endif; ?>
					<p><?php echo h($message); ?><span class="name">（<?php echo h($name); ?>）</span></p>
					<p class="day"><a href="view.php?id=<?php echo h($replied_user_id); ?>"><?php echo h($created); ?></a></p>

					<!-- 返信投稿フォーム -->
					<form action="" method="post">
						<dl>
							<dt><?php echo h($reply_user_name); ?>さん、返信メッセージをどうぞ</dt>

							<?php if (isset($error['reply_message']) && $error['reply_message'] === 'blank') : ?>
								<p class="error">* メッセージが未入力です</p>
							<?php endif; ?>

							<dd>
								<textarea name="reply_message" cols="40" rows="2"></textarea>
							</dd>
						</dl>
						<div>
							<p>
								<input type="submit" value="返信する" />
							</p>
						</div>
					</form>
				<?php } else { //
				?>
					<p>その投稿は削除されたか、URLが間違えています</p>
				<?php exit();
			} ?>

				<div>
					<p>【 リプライ一覧 】</p>
				</div>
				<?php
				// リプライ一覧を表示させるためのDBデータ取得
				$stmt2 = $db->prepare("select r.id, r.reply_user_id, r.reply_message, r.created, m.name, m.picture
						from reply r, members m
						where r.replied_post_id = '" . $id . "'and m.id=r.reply_user_id
						order by r.created desc");
				if (!$stmt2) {
					die($db->error);
				}

				$success = $stmt2->execute();
				if (!$success) {
					die($db->error);
				}

				$stmt2->bind_result($r_id, $r_user_id, $r_message, $r_created, $r_name, $r_picture);
				while ($stmt2->fetch()) {
				?>
					<div>
						<?php if ($r_picture) { ?>
							<img src="member_picture/<?php echo h($r_picture); ?>" width="20" height="20" alt="" />
						<?php } ?>
						<p><?php echo h($r_message); ?><span class="name"> from:<?php echo h($r_name); ?></span></p>
						<p class="day"><?php echo h($r_created); ?></p>
					</div>
				<?php } ?>
				</div>
		</div>
		<div>
		</div>
	</div>
</body>

</html>
