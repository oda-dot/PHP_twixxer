<?php
require 'library.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$fromjs['follower_id'] = $_POST['follower_id'];
	$fromjs['followee_id'] = $_POST['followee_id'];
} else {
	header('Location: index.php');
	exit();
}

if (isset($fromjs)) {

	$follower_id = $fromjs['follower_id'];
	$followee_id = $fromjs['followee_id'];

	// DBへの接続
	$db = dbconnect();

	$stmt = $db->prepare('delete from
							relation
							where follower_id=? and followee_id=? and unique_flag=?
							limit 1
							');
	if (!$stmt) {
		die($db->error);
	}

	// フォローデータの重複登録を避けるために、ユニークな文字列を作成
	$unique_flag = $follower_id . $followee_id;
	$stmt->bind_param('iii', $follower_id, $followee_id, $unique_flag);
	$success = $stmt->execute();
	if (!$success) {
		die($db->error);
	}

	// フォロー数,フォロワー数をデクリメント
	$cnt_rslt = $db->query("update members
				set follower_count = follower_count-1
				where id = '" . $followee_id . "'");
	if (!$cnt_rslt) {
		die($db->error);
	}
	$cnt_rslt = $db->query("update members
				set followee_count = followee_count-1
				where id = '" . $follower_id . "'");
	if (!$cnt_rslt) {
		die($db->error);
	}
}
