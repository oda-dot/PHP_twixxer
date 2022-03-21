<?php
session_start();
require 'library.php';

if (isset($_GET['action']) && $_GET['action'] === 'rewrite' && isset($_SESSION['sess_form'])) {
	$form = $_SESSION['sess_form'];
} else {
	$form = ['name' => '', 'email' => '', 'password' => ''];
}

//$error = ['name' => '', 'email' => ''];//下のif文が動かない時は宣言する必要がないので、使わない。使うときは下のifが動いている時=isset()で確認した時に定義されているときだけとする。
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	// ニックネーム欄のチェック
	$form['name'] = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
	// if ($form['name'] === '') {
	// 	$error['name'] = 'blank';
	// }

	// メールアドレス欄のチェック
	$form['email'] = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
	// if ($form['email'] === '') {
	// 	$error['email'] = 'blank';
	// } else {
	$db = dbconnect();
	$stmt = $db->prepare('select count(*) from members where email = ?');
	if (!$stmt) {
		die($db->error);
	}
	$stmt->bind_param('s', $form['email']);
	$success = $stmt->execute();
	if (!$success) {
		die($db->error);
	}
	$stmt->bind_result($cnt);
	$stmt->fetch();
	if ($cnt > 0) {
		$error['email'] = 'duplicate';
	}
	// }

	// パスワード欄のチェック
	$form['password'] = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
	// if ($form['password'] === '') {
	// 	$error['password'] = 'blank';
	/*} else*/
	if (strlen($form['password']) < 4 && $form['password'] !== '') {
		$error['password'] = 'length';
	}

	// 画像ファイルのチェック
	if ($_FILES['image']['name'] !== '' && $_FILES['image']['error'] === 0) {
		$image_type = mime_content_type($_FILES['image']['tmp_name']);
		if ($image_type !== 'image/jpeg' && $image_type !== 'image/png') {
			$error['image'] = 'type';
		}
	}


	// エラーがなければ、セッションにフォームの値を保存してcheck.phpへ移動
	if (empty($error)) {

		$_SESSION['sess_form'] = $form;

		if ($_FILES['image']['name'] !== '') {
			//画像のファイル名編集
			date_default_timezone_set('Asia/Tokyo');
			$filename = date("YmdHis_") . $_FILES['image']['name'];
			//画像のアップロード
			if (!move_uploaded_file($_FILES['image']['tmp_name'], './member_picture/' . $filename)) {
				die('ファイルのアップロードに失敗しました。');
			}
			//セッションに画像のファイル名を格納
			$_SESSION['sess_form']['image'] = $filename;
		}/* else {
			$_SESSION['sess_form']['image'] = '';
			//何のために必要かわからないからとりまコメントアウトしてる
			//DBに登録するときに、null状態では登録できないため、''を代入している？
		}*/
		header('Location: profile_setting_check.php');
		exit();
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="style.css" />
	<title>登録情報の変更</title>
</head>

<body>
	<div id="wrap">
		<div id="head">
			<h1>登録情報の変更</h1>
		</div>
		<div id="content">
			<p style="text-align: right"><a href="index.php"><img src="images/ie_mark_ikkai.png" width="35" height="35" alt="" /></a></p>

			<div id="lead">

				<div id="content">
					<p>変更したい情報のみご記入ください。</p>
					<!-- 下のaction属性が空になっているのは自分自身を呼び出すため -->
					<form action="" method="post" enctype="multipart/form-data">
						<dl>
							<dt>ニックネーム</dt>
							<dd>
								<input type="text" name="name" size="35" maxlength="255" value="<?php echo h($form['name']); ?>" />

							</dd>
							<dt>メールアドレス</dt>
							<dd>
								<input type="text" name="email" size="35" maxlength="255" value="<?php echo h($form['email']); ?>" />
							<dt>パスワード</dt>
							<dd>
								<input type="password" name="password" size="10" maxlength="20" value="<?php echo h($form['password']); ?>" />
								<?php if (isset($error['password']) && $error['password'] === 'length') : ?>
									<p class="error">* パスワードは4文字以上で入力してください</p>
								<?php endif; ?>
							</dd>
							<dt>プロフィール画像</dt>
							<dd>
								<input type="file" name="image" size="35" value="" />
								<?php if (isset($error['image']) && $error['image'] === 'type') : ?>
									<p class="error">* 「.png」または「.jpg」の画像を指定してください</p>
								<?php endif; ?>
								<p class="error">* 恐れ入りますが、画像を改めて指定してください</p>
							</dd>
						</dl>
						<div><input type="submit" value="入力内容を確認する" /></div>
					</form>
				</div>
</body>

</html>
