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

//subitボタンが押された時にDBヘ接続する
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$db = dbconnect();


	if ($_SESSION['sess_form']['password'] === '') {
		$result = $db->query("SELECT m.password FROM members m
				where m.id = '" . $_SESSION['id'] . "'");
		if (!$result) {
			die($db->error);
		}
		foreach ($result as $row) {
			$result_pw = $row['password'];
		}
		$password = $result_pw;
	} else {
		// パスワードのハッシュ化
		$password = password_hash($_SESSION['sess_form']['password'], PASSWORD_DEFAULT);
	}


	$stmt = $db->prepare(
		'UPDATE members m
		SET m.name=?, m.email=?, m.password=?, m.picture=?
		WHERE m.id=?'
	);
	if (!$stmt) {
		die($db->error);
	}
	if ($_SESSION['sess_form']['name'] === '') {
		$_SESSION['sess_form']['name'] = $_SESSION['name'];
	}
	if ($_SESSION['sess_form']['email'] === '') {
		$_SESSION['sess_form']['email'] = $_SESSION['email'];
	}
	if (is_null($_SESSION['sess_form']['image'])) {
		$_SESSION['sess_form']['image'] = $_SESSION['image'];
	}
	$stmt->bind_param('ssssi', $_SESSION['sess_form']['name'], $_SESSION['sess_form']['email'], $password, $_SESSION['sess_form']['image'], $_SESSION['id']);
	$success = $stmt->execute();
	if (!$success) {
		die($db->error);
	}


	// 重複登録防止のため、セッションを削除
	unset($_SESSION['form']);
	unset($_SESSION['sess_form']);

	// thanks.phpへの遷移
	header('Location: profile_setting_done.php');
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="style.css" />
	<title>登録情報の変更</title>
</head>

</head>

<body>
	<div id="wrap">
		<div id="head">
			<h1>登録情報の変更</h1>
		</div>

		<div id="content">
			<p>内容を確認して、「登録する」ボタンをクリックしてください</p>
			<form action="" method="post">
				<dl>
					<dt>お名前</dt>
					<dd><?php if (isset($_SESSION['sess_form']['name']) && $_SESSION['sess_form']['name'] !== '') {
							echo $_SESSION['sess_form']['name'];
						} else {
							echo $_SESSION['name'];
						} ?></dd>
					<dt>メールアドレス</dt>
					<dd><?php if (isset($_SESSION['sess_form']['email']) && $_SESSION['sess_form']['email'] !== '') {
							echo $_SESSION['sess_form']['email'];
						} else {
							echo $_SESSION['email'];
						} ?></dd>
					<dt>パスワード</dt>
					<dd>
						【表示されません】
					</dd>
					<dt>プロフィール画像</dt>
					<dd>
						<?php if (isset($_SESSION['sess_form']['image']) && $_SESSION['sess_form']['image'] !== '') { ?>
							<img src="./member_picture/<?php echo $_SESSION['sess_form']['image'] ?>" width="100" alt="" />
						<?php } elseif (isset($_SESSION['image']) && $_SESSION['image'] !== '') { ?>
							<img src="./member_picture/<?php echo $_SESSION['image'] ?>" width="100" alt="" />
						<?php } else { ?>
							<img src="./member_picture/no_image_square.jpg" width="100" alt="" />
						<?php } ?>
					</dd>
				</dl>
				<div><a href="profile_setting.php?action=rewrite">&laquo;&nbsp;書き直す</a> | <input type="submit" value="登録する" /></div>
			</form>
		</div>

	</div>
</body>

</html>
