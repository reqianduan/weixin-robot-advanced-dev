<?php

function weixin_robot_get_credit($weixin_openid,$type='credit'){
	$credit = wp_cache_get($weixin_openid, 'weixin_user_'.$type);

	if($credit === false){
		global $wpdb;
		$credit = $wpdb->get_var($wpdb->prepare("SELECT {$type} FROM {$wpdb->weixin_credits} WHERE weixin_openid=%s ORDER BY id DESC LIMIT 0,1",$weixin_openid));
		if(!$credit) $credit = 0;
		wp_cache_set($weixin_openid,$credit,'weixin_user_'.$type);
	}

	return $credit;
}

function weixin_robot_get_exp($weixin_openid){
	return weixin_robot_get_credit($weixin_openid,$type='exp');
}

function weixin_robot_add_credit($arg){
	if(!is_array($arg) || count($arg)<1) wp_die('系统错误(1000)，请通知管理员。');

	global $wpdb;

	$default_args = array(
		'type'			=> '', 		// 类型
		'post_id'		=> 0, 		// 动作是否和日志有关
		'weixin_openid'	=> 0, 		// 微信 ID
		'operator_id'	=> 0, 		// 默认为0
		'credit_change'	=> 0, 		// 改动的积分
		'exp_change'	=> false, 	// 改动的经验值
		'note'			=> '', 		// 注释
		'multiple'		=> 1 		// 删除的倍数
	);

	extract(wp_parse_args($arg, $default_args));

	if(!$type) wp_die('未知动态类型。');

	if(!$weixin_openid )  wp_die('weixin_openid 为空或非法。');

	$weixin_user = weixin_robot_get_user($weixin_openid); 

	$old_credit	= weixin_robot_get_credit($weixin_openid);
	$old_exp 	= weixin_robot_get_exp($weixin_openid);;

	$credit_change = intval($credit_change) * intval($multiple);
	if($exp_change === false){ // 传递进来 0 就不加
		$exp_change = $credit_change;
	}

	$limit = 0;

	if($credit_change > 0 && $operator_id == 0 ){ // 有 operator_id 就不检测每日上限
		$today_credit_sum =  (int)$wpdb->get_var($wpdb->prepare("SELECT SUM(credit_change) FROM {$wpdb->weixin_credits} WHERE weixin_openid=%s AND time<=%s AND time>=%s AND credit_change > 0 AND operator_id = 0",$weixin_openid,date('Y-m-d', current_time('timestamp')).' 23:59:59',date('Y-m-d', current_time('timestamp')).' 00:00:00'));

		if($today_credit_sum >= weixin_robot_get_setting('weixin_day_credit_limit')){
			$credit_change = 0;
			$limit = 1;
		}
	}

	$credit = $old_credit + $credit_change;
	$exp 	= $old_exp + $exp_change;

	// 积分变化，需要清理用户缓存
	wp_cache_delete($weixin_openid, 'weixin_user_credit'); 
	wp_cache_delete($weixin_openid, 'weixin_user_exp'); 

	$data = array(
		'weixin_openid'		=> $weixin_openid,
		'operator_id'		=> $operator_id,
		'credit_change'		=> $credit_change,
		'credit'			=> $credit,
		'exp_change'		=> $exp_change,
		'exp'				=> $exp,
		'type'				=> $type,
		'post_id'			=> $post_id,
		'note'				=> $note,
		'limit'				=> $limit,
		'time'				=> current_time('mysql'),
		'url'				=> $_SERVER['REQUEST_URI']
	);

	$format = array( '%s', '%d', '%d', '%d', '%d', '%d', '%s', '%d', '%s', '%s', '%s' );

	$wpdb->insert($wpdb->weixin_credits, $data, $format);

	do_action('weixin_credit',$arg);

	return $credit_change;
}

// 回复用户现有的积分
function weixin_robot_credit_reply(){
	global $wechatObj;
	$weixin_openid = $wechatObj->get_fromUsername();
	$query_id = weixin_robot_get_user_query_id($weixin_openid);

	$query_key = weixin_robot_get_user_query_key();

	$profile_link = home_url('/?weixin_user&profile&'.$query_key.'='.$query_id);
	
	$credit = weixin_robot_get_credit($weixin_openid);
	$credit_reply = apply_filters('weixin_credit_reply','你现在共有[credit]积分，点击这里查看<a href="[profile_link]">积分历史</a>。',$weixin_openid);

	$credit_reply = str_replace(array('[credit]','[profile_link]'), array($credit,$profile_link), $credit_reply);
	echo sprintf($wechatObj->get_textTpl(), $credit_reply);
	$wechatObj->set_response('credit');
}

/*
自定义hook 用于积分处理
*/
// 签到回复
function weixin_robot_checkin_reply(){
	
	if(isset($_GET['yixin']) ){
		global $wechatObj;
		echo sprintf($wechatObj->get_textTpl(), '易信不支持签到和积分系统。');
		$wechatObj->set_response('checkin');
		wpjam_do_weixin_custom_keyword();
	}

	global $wechatObj;
	$weixin_openid = $wechatObj->get_fromUsername();

	$credit_change = weixin_robot_daily_credit_checkin($weixin_openid);

	$credit = weixin_robot_get_credit($weixin_openid);

	if($credit_change === false){
		$checkin_reply = apply_filters('weixin_checkined','你在24小时内已经签到过了。你现在共有[credit]积分！',$weixin_openid);
	}else{
		$checkin_reply = apply_filters('weixin_checkin_success','签到成功，添加 [credit_change]积分。你现在共有[credit]积分！',$weixin_openid);
	}
	
	$checkin_reply = str_replace(array('[credit_change]','[credit]'), array($credit_change, $credit), $checkin_reply);
	echo sprintf($wechatObj->get_textTpl(), $checkin_reply);
	$wechatObj->set_response('checkin');

	do_action('weixin_checkin',$credit_change);
}

function weixin_robot_daily_credit_checkin($weixin_openid){ //过了 0 点就能签到

	if(!$weixin_openid) wp_die('weixin_openid 为空。');

	$type = 'checkin';
	$current_time = current_time('timestamp');

	$current_date = date('Ymd',$current_time);

	$has_checkin = wp_cache_get($weixin_openid, 'has_checkin_'.$current_date);

	if($has_checkin === false){
		global $wpdb;

		$last_checkin_time = $wpdb->get_var($wpdb->prepare("SELECT `time` FROM {$wpdb->weixin_credits} WHERE `type`=%s AND weixin_openid=%s AND ( ( YEAR( time ) = %d AND MONTH( time ) = %d AND DAYOFMONTH( time ) = %d ) )  ORDER BY id DESC LIMIT 1", $type, $weixin_openid, date('Y',$current_time), date('m',$current_time), date('d',$current_time) ));
		
		if($last_checkin_time){	
			$has_checkin = 1;
			wp_cache_set($weixin_openid, 1, 'has_checkin_'.$current_date, 60*60*24);
		}else{
			$has_checkin = 0;
		}
	}

	if($has_checkin == 0){
		$credit_change = weixin_robot_get_setting('weixin_checkin_credit');
		$credit_change =  weixin_robot_add_credit(array('type'=>$type, 'weixin_openid'=>$weixin_openid, 'credit_change'=>$credit_change, 'note'=>'每日签到'));
		wp_cache_set($weixin_openid, 1, 'has_checkin_'.$current_date, 60*60*24);
		return $credit_change;
	}else{
		return false;
	}

}

add_action('wp_ajax_weixin_share', 'weixin_robot_credit_share_action_callback');
add_action('wp_ajax_nopriv_weixin_share', 'weixin_robot_credit_share_action_callback');

function weixin_robot_credit_share_action_callback(){
	check_ajax_referer( "weixin_share" );

	$weixin_openid 	= $_POST['weixin_openid'];

	if($weixin_openid == false){
		exit;
	}

	$share_type		= $_POST['share_type'];
	$post_id		= $_POST['post_id'];

	if($weixin_openid && $share_type && $post_id){
		if($share_type == 'SendAppMessage'){
			$credit_change = weixin_robot_get_setting('weixin_SendAppMessage_credit');
			$share_message = '发送文章给朋友';
		}elseif($share_type == 'ShareTimeline'){
			$credit_change = weixin_robot_get_setting('weixin_ShareTimeline_credit');
			$share_message = '分享文章到朋友圈';
		}elseif($share_type == 'ShareWeibo'){
			$credit_change = weixin_robot_get_setting('weixin_ShareWeibo_credit');
			$share_message = '分享文章到腾讯微博';
		}elseif($share_type == 'ShareFB'){
			$credit_change = weixin_robot_get_setting('weixin_SendAppMessage_credit');
			$share_message = '分享文章到Facebook';
		}

		global $wpdb;

		if($wpdb->query($wpdb->prepare("SELECT * FROM {$wpdb->weixin_credits} WHERE weixin_openid=%s AND type=%s AND post_id=%d",$weixin_openid,$share_type,$post_id))){
			$share_message = '你已经执行过该操作了';
		}else{
			$credit_change = weixin_robot_add_credit(array('type'=>$share_type, 'weixin_openid'=>$weixin_openid, 'post_id'=>$post_id, 'credit_change'=>$credit_change, 'note'=>$share_message));

			if($credit_change == 0 ){
				$share_message = '你当日加分已经超过'.weixin_robot_get_setting('weixin_day_credit_limit').'分了。';
			}else{
				$share_message = $share_message .'，获取 '.$credit_change.' 积分！';
			}
		}

		echo $share_message;
	}
	exit;
}


