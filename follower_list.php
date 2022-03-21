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
?>



<?php
// DBへの接続
$db = dbconnect();

$stmt = $db->prepare('SELECT m.id, m.name, m.picture
                                    FROM members m
                                    INNER JOIN  relation r ON r.followee_id=? AND r.follower_id = m.id
                                    ORDER BY r.id DESC');
if (!$stmt) {
	die($db->error);
}
$stmt->bind_param('i', $_GET['id']);
$success = $stmt->execute();
if (!$success) {
	die($db->error);
}
$stmt->bind_result($follower_id, $follower_name, $follower_picture);

//クエリのバッファリング（この後queryメソッドを使いたいから）
$buffer = $stmt->store_result();
if (!$success) {
	die($db->error);
}

// 誰のページから来たのかを判断
$profile_usr = $db->query("SELECT m.name FROM members m
				where m.id = '" . $_GET['id'] . "'");
if (!$profile_usr) {
	die($db->error);
}
foreach ($profile_usr as $row) {
	$profile_usr = $row['name'];
}



?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="style.css" />
	<title><?php echo h($profile_usr) ?>さんのフォロワー</title>
</head>

<body>
	<div id="wrap">
		<div id="head">
			<h1><?php echo h($profile_usr) ?>さんのフォロワー</h1>
		</div>
		<div id="content">
			<p style="text-align: right"><a href="index.php"><img src="images/ie_mark_ikkai.png" width="35" height="35" alt="" /></a></p>

			<div id="lead">
				<?php while ($stmt->fetch()) { ?>
					<div>
						<?php if ($follower_picture) { ?>
							<img src="member_picture/<?php echo h($follower_picture); ?>" width="60" height="60" alt="" />
						<?php } else { ?>
							<img src="member_picture/no_image_square.jpg" width="60" height="60" alt="" />
						<?php } ?>
						<span class="name">
							<a href="profile.php?id=<?php echo h($follower_id); ?>"><?php echo h($follower_name); ?></a>
						</span>
					</div>
					<!-- 空行を開けるための空のpタグ -->
					<p></p>
					<!-- 線を引く -->
					<p class="gradientline"></p>
				<?php } ?>

			</div>
		</div>
	</div>
</body>

</html>
