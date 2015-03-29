<?php
function weixin_robot_get_custom_replies_table(){
	_deprecated_function(__FUNCTION__, '4.0', '$wpdb->weixin_custom_replies' );
	global $wpdb;
	return $wpdb->prefix.'weixin_custom_replies';
}

function weixin_robot_get_messages_table(){
	_deprecated_function(__FUNCTION__, '4.0', '$wpdb->weixin_messages' );
	global $wpdb;
	return apply_filters('weixin_messages_table',$wpdb->prefix.'weixin_messages');
}

function weixin_robot_get_users_table(){
	_deprecated_function(__FUNCTION__, '4.0', '$wpdb->weixin_users' );
	global $wpdb;
	return $wpdb->prefix.'weixin_users';
}

function weixin_robot_get_credits_table(){
	_deprecated_function(__FUNCTION__, '4.0', '$wpdb->weixin_credits' );
	global $wpdb;
	return $wpdb->prefix.'weixin_credits';
}

function weixin_robot_get_qrcodes_table(){
	_deprecated_function(__FUNCTION__, '4.0', '$wpdb->weixin_qrcodes' );
	global $wpdb;
	return $wpdb->prefix.'weixin_qrcodes';
}

function weixin_robot_user_get_openid($query_id){
	_deprecated_function(__FUNCTION__, '3.9', 'weixin_robot_get_user_openid' );
    $weixin_openid = substr($query_id, 2);
    if($query_id == weixin_robot_user_get_query_id($weixin_openid)){
        return $weixin_openid;
    }else{
        return false;
    }
}

function weixin_robot_user_get_query_id($weixin_openid){
	_deprecated_function(__FUNCTION__, '3.9', 'weixin_robot_get_user_query_id' );
	$weixin_robot_user_md5 = apply_filters('weixin_robot_user_md5','weixin');
    $check = substr(md5($weixin_robot_user_md5.$weixin_openid),0,2);
    return $check . $weixin_openid;
}

function weixin_robot_get_weixin_query_id(){
	_deprecated_function(__FUNCTION__, '3.9', 'weixin_robot_get_user_query_id' );
	$query_key = weixin_robot_get_user_query_key();

	if(isset($_GET[$query_key])){
		return $_GET[$query_key];
	}elseif(isset($_COOKIE[$query_key])){
		return $_COOKIE[$query_key];
	}else{
		return '';
	}
}
