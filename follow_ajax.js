function post_by_ajax_follow(e) {
	/* イベントの伝播を止めるメソッド。意味を理解できていない。
	ajax通信後のリロードをfollow_btnだけにするため？ */
	e.stopPropagation();

	var data = {
		'follower_id': php_LoginUsrId,// 渡すデータ（ログインユーザのid）を記述
		'followee_id': php_ProfileUsrId// 渡すデータ（プロフィールユーザのid）を記述
	};

	var request = $.ajax({
		url: "register_follow_info.php",
		type: "POST",
		data: data,
	});

	request.done(function () {
		location.reload();
	});

	request.fail(function () {
		alert("\u{26a0}error : 通信に失敗しました");
		location.reload();
	});

}

function post_by_ajax_unfollow(e) {
	/* イベントの伝播を止めるメソッド。意味を理解できていない。
	ajax通信後のリロードをfollow_btnだけにするため？ */
	e.stopPropagation();

	var data = {
		'follower_id': php_LoginUsrId,// 渡すデータ（ログインユーザのid）を記述
		'followee_id': php_ProfileUsrId// 渡すデータ（プロフィールユーザのid）を記述
	};

	var request = $.ajax({
		url: "delete_follow_info.php",
		type: "POST",
		data: data,
	});

	request.done(function () {
		location.reload();
	});

	request.fail(function () {
		alert("\u{26a0}error : 通信に失敗しました");
		location.reload();
	});

}




// followボタンが押された時
$(document).on('click', '.follow_btn', post_by_ajax_follow);


// unfollowボタンが押された時
$(document).on('click', '.unfollow_btn', post_by_ajax_unfollow);
