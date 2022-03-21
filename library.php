<?php

// htmlspecialcharsを短縮する自作関数hの定義
function h($value)
{
	return htmlspecialchars($value, ENT_QUOTES);
}

// DBへの接続
function dbconnect()
{
	$db = new mysqli('localhost', 'root', 'root', 'min_bbs');
	if (!$db) {
		die($db->error);
	}
	return $db;
}



// フォロー状態のチェック
function check_follow($follow_user, $followee_user)
{
	$db = dbconnect();
	$stmt = $db->prepare('select follower_id,followee_id
                                from relation
								where follower_id=? and followee_id=?');
	if (!$stmt) {
		die($db->error);
	}
	$stmt->bind_param('ii', $follow_user, $followee_user);
	$success = $stmt->execute();
	if (!$success) {
		die($db->error);
	}
	$stmt->bind_result($follower, $followee);
	// 一致するものがあれば、つまり既にフォローがあればTRUEになる
	return $stmt->fetch();
}
