<?php
register_activation_hook( WEIXIN_ROBOT_PLUGIN_FILE,'weixin_robot_create_table');
function weixin_robot_create_table() {	
	global $wpdb;

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	if($wpdb->get_var("show tables like '$wpdb->weixin_messages'") != $wpdb->weixin_messages) {
		$sql = "
		CREATE TABLE IF NOT EXISTS ".$wpdb->weixin_messages." (
			`id` bigint(20) NOT NULL auto_increment,
			`MsgId` bigint(64) NOT NULL,
			`FromUserName` varchar(30)  NOT NULL,
			`MsgType` varchar(10)  NOT NULL,
			`CreateTime` int(10) NOT NULL,

			`Content` longtext  NOT NULL,

			`PicUrl` varchar(255)  NOT NULL,

			`Location_X` double(10,6) NOT NULL,
			`Location_Y` double(10,6) NOT NULL,
			`Scale` int(10) NOT NULL,
			`label` varchar(255)  NOT NULL,

			`Title` text  NOT NULL,
			`Description` longtext  NOT NULL,
			`Url` varchar(255)  NOT NULL,

			`Event` varchar(255)  NOT NULL,
			`EventKey` varchar(255)  NOT NULL,

			`Format` varchar(255)  NOT NULL,
			`MediaId` text  NOT NULL,
			`Recognition` text  NOT NULL,
		 
			`Response` varchar(255)  NOT NULL,
			
			`Ticket` text  NOT NULL,
			
			`ip` varchar(100)  NOT NULL,
			
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
		";
 
		dbDelta($sql);
	}

	if($wpdb->get_var("show tables like '{$wpdb->weixin_users}'") != $wpdb->weixin_users) {
		$sql = "
		CREATE TABLE IF NOT EXISTS `{$wpdb->weixin_users}` (
		  `id` bigint(20) NOT NULL auto_increment,
		  `openid` varchar(30) NOT NULL,
		  `nickname` varchar(50) NOT NULL COMMENT '昵称',
		  `name` varchar(50) NOT NULL COMMENT '姓名',
		  `phone` varchar(20) NOT NULL COMMENT '电话号码',
		  `id_card` varchar(18) NOT NULL COMMENT '身份证',
		  `address` text NOT NULL COMMENT '地址',
		  `subscribe` int(1) NOT NULL default '1',
		  `subscribe_time` int(10) NOT NULL,
		  `sex` int(1) NOT NULL,
		  `city` varchar(255) NOT NULL,
		  `country` varchar(255) NOT NULL,
		  `province` varchar(255) NOT NULL,
		  `language` varchar(255) NOT NULL,
		  `headimgurl` varchar(255) NOT NULL,
		  `unionid` varchar(30) NOT NULL,
		  `last_update` int(10) NOT NULL,
		  PRIMARY KEY  (`id`),
		  UNIQUE KEY `weixin_openid` (`openid`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
		";
 
		dbDelta($sql);
	}

	if($wpdb->get_var("show tables like '{$wpdb->weixin_custom_replies}'") != $wpdb->weixin_custom_replies) {
		$sql = "
		CREATE TABLE IF NOT EXISTS `{$wpdb->weixin_custom_replies}` (
			`id` bigint(20) NOT NULL AUTO_INCREMENT,
			`keyword` varchar(255)  NOT NULL,
			`match` varchar(10)  NOT NULL DEFAULT 'full',
			`reply` text  NOT NULL,
			`status` int(1) NOT NULL DEFAULT '1',
			`time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			`type` varchar(10)  NOT NULL DEFAULT 'text',
			PRIMARY KEY (`id`),
			UNIQUE KEY `keyword` (`keyword`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
		";
 
		dbDelta($sql);
	}

	if($wpdb->get_var("show tables like '{$wpdb->weixin_credits}'") != $wpdb->weixin_credits) {
		$sql = "
		CREATE TABLE IF NOT EXISTS `{$wpdb->weixin_credits}` (
		  `id` bigint(20) NOT NULL auto_increment,
		  `weixin_openid` varchar(30) NOT NULL,
		  `operator_id` bigint(20) default NULL,
		  `credit_change` int(10) NOT NULL COMMENT '本次变动的积分',
		  `credit` int(10) NOT NULL COMMENT '变动后的总积分',
		  `exp_change` int(10) NOT NULL COMMENT '本次变动的经验值',
		  `exp` int(10) NOT NULL COMMENT '变动后的总经验值',
		  `type` varchar(20) NOT NULL COMMENT '积分变动类型',
		  `post_id` bigint(20) NOT NULL default '0',
		  `note` varchar(255) NOT NULL COMMENT '备注',
		  `limit` int(1) NOT NULL default '0' COMMENT '是否到每日积分上限',
		  `time` datetime NOT NULL COMMENT '+8时区',
		  `url` char(255) NOT NULL COMMENT '操作的相关 URL',
		  PRIMARY KEY  (`id`),
		  KEY `type` (`type`),
		  KEY `weixin_openid` (`weixin_openid`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
		";
 
		dbDelta($sql);
	}

	if($wpdb->get_var("show tables like '$wpdb->weixin_qrcodes'") != $wpdb->weixin_qrcodes) {
		$sql = "
		CREATE TABLE IF NOT EXISTS " . $wpdb->weixin_qrcodes . " (
			`id` bigint(20) NOT NULL AUTO_INCREMENT,
			`scene` int(6)  NOT NULL,
			`name` varchar(255)  NOT NULL,
			`type` varchar(31)  NOT NULL,
			`ticket` text  NOT NULL,
			`expire` int(10) NOT NULL,
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
		";
 
		dbDelta($sql);
	}
}

// 写数据到微信信息表
add_action('weixin_robot','wpjam_stats_weixin_robot');
function wpjam_stats_weixin_robot($wechatObj){
	if(empty($is_IIS)){
		$response	= $wechatObj->get_response();
		$postObj	= $wechatObj->get_postObj();
		if($response && $postObj){
			weixin_robot_insert_message($postObj, $response);
		}
	}
}

add_action('parse_query','weixin_robot_parse_query');
function weixin_robot_parse_query($query){
	if(isset($_GET['weixin']) || isset($_GET['yixin']) || isset($_GET['signature'])){
		$query->is_home 	= false;
		$query->is_search 	= false;
		$query->is_weixin 	= true;
	}
}

//自定义回复，内置回复，函数回复，关键字太长处理等。
add_filter('weixin_custom_keyword','weixin_robot_custom_keyword',1,2);
function weixin_robot_custom_keyword($false,$keyword){
	
	global $wechatObj;

	if(empty( $keyword ) || strpos($keyword, '#') !== false ) {
		echo "";
		$wechatObj->set_response('need-manual');
		return true;
	}elseif($keyword == 'subscribe'){
		$weixin_openid = $wechatObj->get_fromUsername();
		if($weixin_openid){
			$weixin_user = array('subscribe'=>1);
			weixin_robot_update_user($weixin_openid,$weixin_user);
		}
	}

	// 前缀匹配，只支持2个字
	$prefix_keyword = mb_substr($keyword, 0,2);

	$weixin_custom_keywords = weixin_robot_get_custom_keywords();
	$weixin_custom_keywords_prefix = weixin_robot_get_custom_keywords('prefix');

	if(isset($weixin_custom_keywords[$keyword]) ){
		$weixin_custom_reply = $weixin_custom_keywords[$keyword];
	}elseif(isset($weixin_custom_keywords_prefix[$prefix_keyword])) {
		$weixin_custom_reply = $weixin_custom_keywords_prefix[$prefix_keyword];
	}else{
		$weixin_custom_reply = '';
	}

	if($weixin_custom_reply){
		weixin_robot_custom_reply($weixin_custom_reply,$keyword);
		return true;
	}

	//内置回复 -- 完全匹配
	$weixin_builtin_replies = weixin_robot_get_builtin_replies('full');
	//内置回复 -- 前缀匹配
	$weixin_builtin_replies_prefix = weixin_robot_get_builtin_replies('prefix');

	if(isset($weixin_builtin_replies[$keyword])) {
		$weixin_reply_function = $weixin_builtin_replies[$keyword]['function'];
	}elseif(isset($weixin_builtin_replies_prefix[$prefix_keyword])){
		$weixin_reply_function = $weixin_builtin_replies_prefix[$prefix_keyword]['function'];
	}

	if(isset($weixin_reply_function)){
		call_user_func($weixin_reply_function, $keyword);
		return true;
	}

	if(weixin_robot_get_setting('weixin_disable_search')){
		weixin_robot_not_found_reply($keyword);
		return true;
	}else{
		// 检测关键字是不是太长了
		if(!weixin_robot_get_setting('weixin_3rd_search')){
			$keyword_length = mb_strwidth(preg_replace('/[\x00-\x7F]/','',$keyword),'utf-8')+str_word_count($keyword)*2;

			$weixin_keyword_allow_length = weixin_robot_get_setting('weixin_keyword_allow_length');
			
			if($keyword_length > $weixin_keyword_allow_length){

				$weixin_keyword_too_long = weixin_robot_str_replace(weixin_robot_get_setting('weixin_keyword_too_long'),$wechatObj);

				if($weixin_keyword_too_long){
					echo sprintf($wechatObj->get_textTpl(), $weixin_keyword_too_long);
				}
				$wechatObj->set_response('too-long');

				return true;
			}
		}
	}
	
	return $false;
}

function weixin_robot_custom_reply($weixin_custom_reply, $keyword){
	global $wechatObj;
	if($weixin_custom_reply->type == 'text'){	
		$wechatObj->set_response('custom-text');
		$weixin_text_reply =  weixin_robot_str_replace($weixin_custom_reply->reply, $wechatObj);
		echo sprintf($wechatObj->get_textTpl(), $weixin_text_reply);
	}elseif($weixin_custom_reply->type == 'img'){
		add_filter('weixin_query','weixin_robot_img_reply_query');
		$wechatObj->set_response('custom-img');
		$wechatObj->query($keyword);
	}elseif($weixin_custom_reply->type == 'function'){
		call_user_func($weixin_custom_reply->reply, $keyword);
	}elseif($weixin_custom_reply->type == '3rd'){
		weixin_robot_3rd_reply();
	}elseif($weixin_custom_reply->type == 'image'){
		$wechatObj->set_response('custom-image');
		echo sprintf($wechatObj->get_imageTpl(), $weixin_custom_reply->reply);
	}elseif($weixin_custom_reply->type == 'voice'){
		$wechatObj->set_response('custom-voice');
		echo sprintf($wechatObj->get_voiceTpl(), $weixin_custom_reply->reply);
	}elseif($weixin_custom_reply->type == 'voice'){
		$wechatObj->set_response('custom-music');
		//echo sprintf($wechatObj->get_musicTpl(), $weixin_custom_reply->reply);
	}elseif($weixin_custom_reply->type == 'voice'){
		$wechatObj->set_response('custom-video');
		//echo sprintf($wechatObj->get_videoTpl(), $weixin_custom_reply->reply);
	}
}

//获取自定义回复列表
function weixin_robot_get_custom_keywords($match='full'){
	global $wpdb;

	$weixin_custom_keywords = get_transient('weixin_custom_keywords_'.$match);

	if($weixin_custom_keywords === false){
		$sql = "SELECT keyword,reply,type FROM $wpdb->weixin_custom_replies WHERE {$wpdb->weixin_custom_replies}.match = '{$match}' AND status = 1";
		$weixin_custom_original_keywords = $wpdb->get_results($sql,OBJECT_K);
		
		$weixin_custom_keywords = array(); 
		if($weixin_custom_original_keywords){
			foreach ($weixin_custom_original_keywords as $key => $value) {
				if(strpos($key,',')){
					foreach (explode(',', $key) as $new_key) {
						$new_key = strtolower(trim($new_key));
						if($new_key){
							$weixin_custom_keywords[$new_key] = $value;
						}
					}
				}else{
					$weixin_custom_keywords[strtolower($key)] = $value;
				}
			}
		}

		set_transient('weixin_custom_keywords_'.$match,$weixin_custom_keywords,3600);
	}
	return $weixin_custom_keywords;
}
//获取内置回复列表
function weixin_robot_get_builtin_replies($type = ''){

	$weixin_builtin_replies = get_transient('weixin_builtin_replies');

	if($weixin_builtin_replies === false){
		$weixin_builtin_replies = array();
		
		$weixin_builtin_replies['[voice]'] 			= array('type'=>'full',	'reply'=>'默认语音回复',		'function'=>'weixin_robot_default_reply');
		$weixin_builtin_replies['[location]'] 		= array('type'=>'full',	'reply'=>'默认地理位置回复',	'function'=>'weixin_robot_default_reply');
		$weixin_builtin_replies['[image]'] 			= array('type'=>'full',	'reply'=>'默认图片回复',		'function'=>'weixin_robot_default_reply');
		$weixin_builtin_replies['[link]'] 			= array('type'=>'full',	'reply'=>'默认链接回复',		'function'=>'weixin_robot_default_reply');
		$weixin_builtin_replies['[video]'] 			= array('type'=>'full',	'reply'=>'默认视频回复',		'function'=>'weixin_robot_default_reply');
		
		
		$weixin_builtin_replies['[default]'] 		= array('type'=>'full',	'reply'=>'没有匹配时回复',	'function'=>'weixin_robot_not_found_reply');
		$weixin_builtin_replies['[view]'] 			= array('type'=>'full',	'reply'=>'查看网页时候回复',	'function'=>'weixin_robot_view_event_reply');
		

		if(weixin_robot_get_setting('weixin_advanced_api') ){
			$weixin_builtin_replies['[event-location]']	= array('type'=>'full',	'reply'=>'获取用户地理位置',	'function'=>'weixin_robot_location_event_reply');
			$weixin_builtin_replies[weixin_robot_get_setting('weixin_wkd')]	= array('type'=>'full',	'reply'=>'进入多客服',	'function'=>'weixin_robot_wkd_reply');
		}

		foreach (array( 'hi', 'h', 'help', '帮助', '您好', '你好') as $welcome_keyword) {
			$weixin_builtin_replies[$welcome_keyword] = array('type'=>'full', 'reply'=>'欢迎回复', 'function'=>'weixin_robot_welcome_reply');
		}

		$weixin_builtin_replies['subscribe']	= array('type'=>'full',	'reply'=>'用户订阅',		'function'=>'weixin_robot_subscribe_reply');
		$weixin_builtin_replies['unsubscribe']	= array('type'=>'full',	'reply'=>'用户取消订阅',	'function'=>'weixin_robot_unsubscribe_reply');

		$weixin_builtin_replies[weixin_robot_get_setting('new')] 		= array('type'=>'full',	'reply'=>'最新日志',			'function'=>'weixin_robot_new_posts_reply');
		$weixin_builtin_replies[weixin_robot_get_setting('rand')] 		= array('type'=>'full',	'reply'=>'随机日志',			'function'=>'weixin_robot_rand_posts_reply');
		$weixin_builtin_replies[weixin_robot_get_setting('hot')] 		= array('type'=>'full',	'reply'=>'最热日志',			'function'=>'weixin_robot_hot_posts_reply');
		$weixin_builtin_replies[weixin_robot_get_setting('comment')] 	= array('type'=>'full',	'reply'=>'留言最多日志',		'function'=>'weixin_robot_comment_posts_reply');
		$weixin_builtin_replies[weixin_robot_get_setting('hot-7')] 	= array('type'=>'full',	'reply'=>'一周内最热日志',		'function'=>'weixin_robot_hot_7_posts_reply');
		$weixin_builtin_replies[weixin_robot_get_setting('comment-7')]	= array('type'=>'full',	'reply'=>'一周内留言最多日志',	'function'=>'weixin_robot_comment_7_posts_reply');

		if( weixin_robot_get_setting('weixin_credit')){
			$weixin_builtin_replies['checkin']	= $weixin_builtin_replies['签到']	= array('type'=>'full', 'reply'=>'签到', 	'function'=>'weixin_robot_checkin_reply');
			$weixin_builtin_replies['credit']	= $weixin_builtin_replies['积分']	= array('type'=>'full', 'reply'=>'获取积分',	'function'=>'weixin_robot_credit_reply');
		}

		$weixin_builtin_replies = apply_filters('weixin_builtin_reply', $weixin_builtin_replies);

		set_transient('weixin_builtin_replies',$weixin_builtin_replies,3600);
	}

	if($type){
		$weixin_builtin_replies_new = get_transient('weixin_builtin_replies_new');
		if($weixin_builtin_replies_new === false){
			$weixin_builtin_replies_new = array();
			foreach ($weixin_builtin_replies as $key => $weixin_builtin_reply) {
				$weixin_builtin_replies_new[$weixin_builtin_reply['type']][$key] = $weixin_builtin_reply;
			}
			set_transient('weixin_builtin_replies_new',$weixin_builtin_replies_new,3600);
		}
		return $weixin_builtin_replies_new[$type];
	}else{
		return $weixin_builtin_replies;
	}
}

// 把微信的 XML 提交给第三方微信平台
function weixin_robot_3rd_reply(){
	global $wechatObj;

	$third_token	= weixin_robot_get_setting('weixin_3rd_token');
	$timestamp		= (string)time();
	$nonce 			= (string)(time()-rand(1000,10000));

	$signature		= array($third_token, $timestamp, $nonce);
	sort($signature,SORT_STRING);
	$signature		= implode( $signature );
	$signature		= sha1( $signature );

	$third_url		= weixin_robot_get_setting('weixin_3rd_url');
	$third_url		= add_query_arg(array('timestamp'=>$timestamp,'nonce'=>$nonce,'signature'=>$signature),$third_url);

	$postStr		= (isset($GLOBALS["HTTP_RAW_POST_DATA"]))?$GLOBALS["HTTP_RAW_POST_DATA"]:'';

	$response = wp_remote_post(
		$third_url, 
		array( 
			'headers' => array( 'Content-Type' => 'text/xml' ),
			'body'=>$postStr
		)
	);

	//file_put_contents(WP_CONTENT_DIR.'/uploads/test.html',var_export($postStr,true));
	//file_put_contents(WP_CONTENT_DIR.'/uploads/test.html',var_export($response,true));

	echo $response['body'];
	$wechatObj->set_response('3rd');
}

// 欢迎回复
function weixin_robot_welcome_reply($keyword){
	global $wechatObj;
	$weixin_welcome = weixin_robot_str_replace(weixin_robot_get_setting('weixin_welcome'),$wechatObj);
	echo sprintf($wechatObj->get_textTpl(), $weixin_welcome);
	$wechatObj->set_response('welcome');
}

function weixin_robot_wkd_reply($keyword){
	global $wechatObj;
	echo $wechatObj->get_transfer_customer_serviceTpl();
	$wechatObj->set_response('wkd');
}

// 订阅回复
function weixin_robot_subscribe_reply($keyword){
	global $wechatObj;
	$weixin_openid = $wechatObj->get_fromUsername();
	if($weixin_openid){
		$weixin_user = array('subscribe'=>1);
		weixin_robot_update_user($weixin_openid,$weixin_user);
	}
	weixin_robot_welcome_reply($keyword);
}

// 取消订阅回复
function weixin_robot_unsubscribe_reply($keyword){
	global $wechatObj;
	$weixin_unsubscribe = "你怎么忍心取消对我的订阅？";
	echo sprintf($wechatObj->get_textTpl(), $weixin_unsubscribe);
	$wechatObj->set_response('byebye');

	$weixin_openid = $wechatObj->get_fromUsername();
	if($weixin_openid){
		$weixin_user = array('subscribe'=>0);
		weixin_robot_update_user($weixin_openid,$weixin_user);
	}
}

// 语音，图像，地理信息默认处理
function weixin_robot_default_reply($keyword){
	global $wechatObj;
	$keyword = str_replace(array('[',']'), '', $keyword);
	$weixin_default = weixin_robot_str_replace(weixin_robot_get_setting('weixin_default_'.$keyword),$wechatObj);
	if($weixin_default){
		echo sprintf($wechatObj->get_textTpl(), $weixin_default);
	}
	$wechatObj->set_response($keyword);
}

function weixin_robot_view_event_reply($keyword){
	global $wechatObj;
	$wechatObj->set_response('view');
}

function weixin_robot_not_found_reply($keyword){

	if(weixin_robot_get_setting('weixin_3rd_search')){
		weixin_robot_3rd_reply();
	}else{
		global $wechatObj;

		if(isset($weixin_custom_keywords['[default]'])){
			$weixin_custom_reply = $weixin_custom_keywords['[default]'];
	        weixin_robot_custom_reply($weixin_custom_reply,'[default]');
		}else{
			$weixin_not_found = weixin_robot_str_replace(str_replace('[keyword]', '【'.$keyword.'】', weixin_robot_get_setting('weixin_not_found')),$wechatObj);
			if($weixin_not_found){
				echo sprintf($wechatObj->get_textTpl(), $weixin_not_found);
			}
			$wechatObj->set_response('not-found');
		}
	}
}

// 用户自动上传地理位置时的回复
function weixin_robot_location_event_reply($keyword){
	global $wechatObj, $wpdb;

    $weixin_openid = $wechatObj->get_fromUsername();

    $last_enter_reply = wp_cache_get($weixin_openid,'weixin_enter_reply');
    if($last_enter_reply === false) {
    	$last_enter_reply = $wpdb->get_var($wpdb->prepare("SELECT CreateTime FROM {$wpdb->weixin_messages} WHERE MsgType='event' AND Event = 'LOCATION' AND Response='enter-reply' AND FromUserName=%s ORDER BY CreateTime DESC LIMIT 0,1;",$weixin_openid)); // 24 小时内写过的，就不再写入了。
    	if($last_enter_reply){
        	wp_cache_set($weixin_openid,$last_enter_reply,'weixin_enter_reply',60*60*24);
    	}else{
    		$last_enter_reply = 0;
    	}
    }

    if(current_time('timestamp') - $last_enter_reply > apply_filters('weixin_enter_time',60*60*24)+3600*8)  {
    	if(isset($weixin_custom_keywords['[event-location]'])){
			$weixin_custom_reply = $weixin_custom_keywords['[event-location]'];
	        weixin_robot_custom_reply($weixin_custom_reply,'[event-location]');
		}else{
			$weixin_enter = weixin_robot_str_replace(weixin_robot_get_setting('weixin_enter'),$wechatObj);
    		echo sprintf($wechatObj->get_textTpl(), $weixin_enter);
		}
    	wp_cache_set($weixin_openid, current_time('timestamp'), 'weixin_enter_reply', 60*60*24);
    	$wechatObj->set_response('enter-reply');
	}else{
		$wechatObj->set_response('location');
	}
}


//设置时间为最近7天
function weixin_robot_posts_where_7( $where = '' ) {
	return $where . " AND post_date > '" . date('Y-m-d', strtotime('-7 days')) . "'";
}

//设置时间为最近30天
function weixin_robot_posts_where_30( $where = '' ) {
	return $where . " AND post_date > '" . date('Y-m-d', strtotime('-60 days')) . "'";
}

function weixin_robot_advanced_reply($keyword){
	global $wechatObj;
	$wechatObj->set_response('advanced');
	$wechatObj->query();
}

//按照时间排序
function weixin_robot_new_posts_reply($keyword){
	add_filter('weixin_query','weixin_robot_new_query');
	weixin_robot_advanced_reply($keyword);
}
function weixin_robot_new_query($weixin_query_array){
	unset($weixin_query_array['s']);
	return $weixin_query_array;
}
//随机排序
function weixin_robot_rand_posts_reply($keyword){
	add_filter('weixin_query','weixin_robot_rand_query');
	weixin_robot_advanced_reply($keyword);
}
function weixin_robot_rand_query($weixin_query_array){
	unset($weixin_query_array['s']);
	$weixin_query_array['orderby']		= 'rand';
	return $weixin_query_array;
}
//按照浏览排序
function weixin_robot_hot_posts_reply($keyword){
	add_filter('weixin_query','weixin_robot_hot_query');
	weixin_robot_advanced_reply($keyword);
}
function weixin_robot_hot_query($weixin_query_array){
	unset($weixin_query_array['s']);
	$weixin_query_array['meta_key']		= 'views';
	$weixin_query_array['orderby']		= 'meta_value_num';
	return $weixin_query_array;
}
//按照留言数排序
function weixin_robot_comment_posts_reply($keyword){
	add_filter('weixin_query','weixin_robot_comment_query');
	weixin_robot_advanced_reply($keyword);
}
function weixin_robot_comment_query($weixin_query_array){
	unset($weixin_query_array['s']);
	$weixin_query_array['orderby']		= 'comment_count';
	return $weixin_query_array;
}
//7天内最热
function weixin_robot_hot_7_posts_reply($keyword){
	add_filter('weixin_query','weixin_robot_hot_query');
	add_filter('posts_where', 'weixin_robot_posts_where_7' );
	weixin_robot_advanced_reply($keyword);
}
//7天内留言最多 
function weixin_robot_comment_7_posts_reply($keyword){
	add_filter('weixin_query','weixin_robot_comment_query');
	add_filter('posts_where', 'weixin_robot_posts_where_7' );
	weixin_robot_advanced_reply($keyword);
}
//30天内最热
function weixin_robot_hot_30_posts_reply($keyword){
	add_filter('weixin_query','weixin_robot_hot_query');
	add_filter('posts_where', 'weixin_robot_posts_where_30' );
	weixin_robot_advanced_reply($keyword);
}
//30天内留言最多
function weixin_robot_comment_30_posts_reply($keyword){
	add_filter('weixin_query','weixin_robot_comment_query');
	add_filter('posts_where', 'weixin_robot_posts_where_30' );
	weixin_robot_advanced_reply($keyword);
}
//如果搜索关键字是分类名或者 tag 名，直接返回该分类或者tag下最新日志
add_filter('weixin_query','weixin_robot_taxonomy_query', 99);
function weixin_robot_taxonomy_query($weixin_query_array){
	if(isset($weixin_query_array['s'])){
		global $wpdb;
		$keyword = $weixin_query_array['s'];
		$term = $wpdb->get_row("SELECT term_id, slug, taxonomy FROM {$wpdb->prefix}term_taxonomy tt INNER JOIN {$wpdb->prefix}terms t USING ( term_id ) WHERE lower(t.name) = '{$keyword}' OR t.slug = '{$keyword}' LIMIT 0 , 1");

		if($term){
			if($term->taxonomy == 'category'){
				unset($weixin_query_array['s']);
				$weixin_query_array['cat']		= $term->term_id;
			}elseif ($term->taxonomy == 'post_tag') {
				unset($weixin_query_array['s']);
				$weixin_query_array['tag_id']	= $term->term_id;
			}else{
				unset($weixin_query_array['s']);
				$weixin_query_array[$term->taxonomy]	= $term->slug;
			}
			$weixin_query_array = apply_filters('weixin_taxonomy_query',$weixin_query_array,$term);
		}
	}
	return $weixin_query_array;
}
//自定义图文日志查询
function weixin_robot_img_reply_query($weixin_query_array){
	$weixin_custom_keywords = weixin_robot_get_custom_keywords();
	$weixin_custom_reply = $weixin_custom_keywords[$weixin_query_array['s']];
	$post_ids = explode(',', $weixin_custom_reply->reply);

	$weixin_query_array['post__in']		= $post_ids;
	$weixin_query_array['orderby']		= 'post__in';

	unset($weixin_query_array['s']);
	$weixin_query_array['post_type']	= 'any';

	return $weixin_query_array;
}

// 通过自定义字段设置改变图文的链接
// 给用户添加 query_id 或者 openid，用于访问页面时，获取当前用户
add_filter('weixin_url','weixin_robot_url_add_query_id', 99);
function weixin_robot_url_add_query_id($url){
	if($weixin_url = get_post_meta(get_the_ID(), 'weixin_url', true)){
		$url = $weixin_url;
	}

	global $wechatObj;

	if(isset($wechatObj)){
		$weixin_openid = $wechatObj->get_fromUsername();

		if($use_openid = get_post_meta(get_the_ID(), 'use_openid', true)){
			return add_query_arg('weixin_openid', $weixin_openid, $url);	
		}else{
			$query_id = weixin_robot_get_user_query_id($weixin_openid);

			$query_key = weixin_robot_get_user_query_key();

			return add_query_arg($query_key, $query_id, $url);	
		}
	}else{
		return $url;
	}
}

// 设置如果系统安装了七牛存储或者 WPJAM Thumbnail 高级缩略图插件，则使用它们截图
add_filter('weixin_pre_thumb','wpjam_weixin_pre_get_thumb',10,3);
function wpjam_weixin_pre_get_thumb($thumb,$size,$post){
	if(function_exists('wpjam_get_post_thumbnail_src')){
		if(wpjam_has_post_thumbnail()){
			$thumb = wpjam_get_post_thumbnail_src($post, $size);
		}	
	}
	return $thumb;
}

add_action('wp','weixin_robot_wp');
function weixin_robot_wp(){
	if(is_singular() && is_weixin()){
		add_action( 'wp_enqueue_scripts', 'weixin_robot_enqueue_scripts' );
	}
}

function weixin_robot_enqueue_scripts() {
	global $post;

	$img			= apply_filters('weixin_share_img',	get_post_weixin_thumb($post,array(120,120)));
	$link			= apply_filters('weixin_share_url',	get_permalink());
	$title			= apply_filters('weixin_share_title', get_the_title());
	$desc			= apply_filters('weixin_share_desc', get_post_excerpt($post));
	$weixin_openid 	= weixin_robot_get_user_openid();

	wp_enqueue_script('jquery');
	
	wp_enqueue_script( 'weixin', WEIXIN_ROBOT_PLUGIN_URL.'/static/weixin-share.js', array('jquery') );
	wp_localize_script('weixin', 'weixin_data', array(
			'appid' 		=> '',
			'fakeid'		=> '',
			'img'			=> $img,
			'link'			=> $link,
			'title'			=> $title,
			'desc'			=> $desc,
			'credit'		=> $is_IIS? 0 : weixin_robot_get_setting('weixin_credit'),
			'ajax_url'		=> admin_url('admin-ajax.php'),
			'nonce'			=> wp_create_nonce( 'weixin_share' ),
			'post_id'		=> get_the_ID(),
			'weixin_openid'	=> $weixin_openid,
			'notify'		=> weixin_robot_get_setting('weixin_share_notify')
		)	
	);
}

/*
function wpjam_basic_filter($original){
	$weixin_robot_basic = weixin_robot_get_basic_option();

	global $wp_current_filter;

	//最后一个才是当前的 filter
	$wpjam_current_filter = $wp_current_filter[count($wp_current_filter)-1];

	if(isset($weixin_robot_basic[$wpjam_current_filter])){
		if($weixin_robot_basic[$wpjam_current_filter ]){
			return $weixin_robot_basic[$wpjam_current_filter];
		}
	}else{
		return $original;
	}
}
*/
/*function weixin_robot_get_welcome_keywords(){
	return array( 'hi', 'h', 'help', '帮助', '您好', '你好');
}*/