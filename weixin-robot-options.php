<?php 
//后台菜单
add_action( 'admin_menu', 'weixin_robot_admin_menu' );
function weixin_robot_admin_menu() {
	$weixin_robot_name = apply_filters('weixin_robot_name','微信机器人');
	add_menu_page($weixin_robot_name, $weixin_robot_name,	'manage_options',	'weixin-robot',	'weixin_robot_basic_page',	'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4IiB3aWR0aD0iNDAwIiBoZWlnaHQ9IjQwMCIgdmlld0JveD0iMCwgMCwgNDAwLCA0MDAiPgogIDxnIGlkPSJMYXllciAxIj4KICAgIDxwYXRoIGQ9Ik0xMjAuNjAxLDQ2LjU3NiBDOS4yNDEsNjYuNDY2IC0yNy44NzksMTkyLjI4MSA2MC43LDI0OS44NjkgQzY1LjU1NywyNTIuOTkxIDY1LjU1NywyNTIuNjQ1IDU4LjI3MSwyNzQuMzg1IEw1Mi4wMjcsMjkzLjAwMiBMNzQuNDYxLDI4MC45NzYgTDk2Ljg5NSwyNjguOTUgTDEwOC44MDYsMjcxLjg0MSBDMTIxLjI5NCwyNzQuOTYzIDEzNy4yNTMsMjc3LjE2IDE0Ny44OTEsMjc3LjE2IEwxNTQuMjUyLDI3Ny4xNiBMMTUyLjA1NCwyNjguNzE4IEMxMzQuNTkzLDIwNC40MjMgMTk0Ljk1NiwxNDAuNzA2IDI3My40NzUsMTQwLjcwNiBMMjg0LjExNCwxNDAuNzA2IEwyODEuOTE3LDEzMy4wNzQgQzI2NC42ODYsNzIuODI2IDE5MS45NSwzMy44NTYgMTIwLjYwMSw0Ni41NzYgeiBNMTEwLjg4NywxMDIuODkyIEMxMjIuNjgyLDExMC44NzIgMTIzLjM3NiwxMjguMTAyIDExMi4wNDMsMTM1LjUwMyBDOTMuNjU3LDE0Ny41MjkgNzIuMTQ4LDEyNi4zNjcgODQuNTIxLDEwOC4zMjcgQzg5Ljk1NiwxMDAuMjMzIDEwMy4wMjQsOTcuNTczIDExMC44ODcsMTAyLjg5MiB6IE0yMDUuNzExLDEwMi44OTIgQzIyNS4xMzgsMTE1Ljk2IDIxMC41NjgsMTQ2LjE0MSAxODguODI3LDEzNy44MTUgQzE3My4xMDEsMTMxLjgwMiAxNzEuMjUsMTEwLjE3OCAxODUuOTM2LDEwMi40MyBDMTkxLjcxOCw5OS4zMDggMjAwLjczOCw5OS41MzkgMjA1LjcxMSwxMDIuODkyIHogTTI0OC42MTMsMTUwLjUzNiBDMTkzLjQ1MywxNjAuNTk2IDE1NS4xNzcsMjAyLjQ1NyAxNTcuMzc0LDI1MC41NjMgQzE2MC4yNjUsMzE0Ljk3NCAyMzUuNzc3LDM1OS4zNzkgMzA4LjI4MiwzMzkuNDg5IEwzMTYuODM5LDMzNy4xNzYgTDMzNC44NzksMzQ2Ljg5IEMzNDQuODI0LDM1Mi4zMjUgMzUzLjE1LDM1Ni4yNTcgMzUzLjM4MSwzNTUuNzk0IEMzNTMuNjEzLDM1NS4yMTYgMzUxLjY0NywzNDguMjc4IDM0OS4xMDMsMzQwLjI5OSBDMzQzLjMyMSwzMjIuNDkgMzQzLjIwNSwzMjMuNzYyIDM1MC45NTMsMzE4LjIxMiBDNDM4LjE0NCwyNTUuNjUxIDM2MS41OTIsMTMwLjA2OCAyNDguNjEzLDE1MC41MzYgeiBNMjQ2LjQxNiwyMDIuNDU3IEMyNTEuMjcyLDIwNS42OTUgMjUzLjgxNiwyMTMuNzkgMjUxLjczNSwyMTkuNjg4IEMyNDcuMzQxLDIzMi4yOTIgMjI4LjQ5MiwyMzMuMjE3IDIyMy40MDMsMjIxLjA3NSBDMjE3LjYyMSwyMDcuMDgzIDIzMy41OCwxOTQuMTMxIDI0Ni40MTYsMjAyLjQ1NyB6IE0zMjMuNjYyLDIwMy44NDUgQzMzMS4yOTQsMjExLjEzIDMzMC4wMjIsMjIzLjUwNCAzMjEuMTE4LDIyOC4xMjkgQzMwNy40NzMsMjM1LjA2NyAyOTMuMTM0LDIyMS4xOTEgMzAwLjE4OCwyMDcuODkyIEMzMDQuODEzLDE5OS4zMzUgMzE2LjcyNCwxOTcuMjU0IDMyMy42NjIsMjAzLjg0NSB6IE0yMjAuNDMsMzI4Ljc3MiIgZmlsbD0iI2ZmZiIvPgogIDwvZz4KICA8ZGVmcy8+Cjwvc3ZnPg==');

	weixin_robot_add_submenu_page('basic', '设置', 'weixin-robot');

	if(wpjam_net_check_domain()){
		weixin_robot_add_submenu_page('reply', '自定义回复');

		if((weixin_robot_get_setting('weixin_app_id') && weixin_robot_get_setting('weixin_app_secret'))||(weixin_robot_get_setting('yixin_app_id') && weixin_robot_get_setting('yixin_app_secret'))) {
			weixin_robot_add_submenu_page('custom-menu', '自定义菜单');
		}

		if(weixin_robot_get_setting('weixin_advanced_api')) {
			weixin_robot_add_submenu_page('qrcode', '带参数二维码');
			//weixin_robot_add_submenu_page('bulk-send-message','群发消息');
		}

		if(empty($is_IIS)){
			if(weixin_robot_get_setting('weixin_disable_stats')==false ){
				weixin_robot_add_submenu_page('messages', '微信最新消息');
				weixin_robot_add_submenu_page('stats2', '微信统计分析');
			}
			if(weixin_robot_get_setting('weixin_advanced_api') || weixin_robot_get_setting('weixin_credit')){
				weixin_robot_add_submenu_page('user', 	'微信用户列表');
			}

			if(weixin_robot_get_setting('weixin_credit')){
				weixin_robot_add_submenu_page('credit', '微信积分记录');
			}
		}

		do_action('weixin_admin_menu');
		//weixin_robot_add_submenu_page('extends','扩展管理');
		weixin_robot_add_submenu_page('datas','数据检测和清理');
	}
}

function weixin_robot_add_submenu_page($key, $title, $slug='', $cap='manage_options'){
	if(!$slug) $slug = 'weixin-robot-'.$key;
	add_submenu_page( 'weixin-robot', $title.' &lsaquo; 微信机器人', $title, $cap, $slug, 'weixin_robot_'.str_replace('-', '_', $key).'_page');
}

add_action('wpjam_net_item_ids','weixin_robot_wpjam_net_item_id');
function weixin_robot_wpjam_net_item_id($item_ids){
	$item_ids['56'] = WEIXIN_ROBOT_PLUGIN_FILE;
	return $item_ids;
}

add_action( 'admin_init', 'weixin_robot_admin_init' );
function weixin_robot_admin_init() {
	wpjam_add_settings(weixin_robot_get_option_labels(), weixin_robot_get_default_option());
}

function weixin_robot_basic_page() {
	if(isset($_POST['weixin_robot_options_nonce']) && wp_verify_nonce($_POST['weixin_robot_options_nonce'], 'weixin_robot' )){
		update_option('wpjam_net_domain_check_56',$_POST['wpjam_net_domain_check_56']);
	}
	if(wpjam_net_check_domain(56)){
		settings_errors();
		$labels = weixin_robot_get_option_labels();
		wpjam_option_page($labels, $title='设置', $type='tab');
	}else{
		global $plugin_page;

		?>
		<div class="wrap">
			<h2>微信机器人</h2>
			<p>商城更换了授权模式，已经购买用户，请到这里 <a href="http://wpjam.net/wp-admin/admin.php?page=orders&domain_limit=1&product_id=56" class="button">获取授权码</a>。<br />未购买用户，请联系 QQ 11497107 购买。</p>
			<!--<p>你还没有授权域名，点击这里：<a href="http://wpjam.net/wp-admin/admin.php?page=orders&domain_limit=1&product_id=56" class="button">授权域名</a></p>-->
			<form method="post" action="<?php echo admin_url('admin.php?page='.$plugin_page); ?>" enctype="multipart/form-data" id="form">
				<input type="text" id="wpjam_net_domain_check_56" name="wpjam_net_domain_check_56" value="" class="regular-text" />
				<?php wp_nonce_field('weixin_robot','weixin_robot_options_nonce'); ?>
				<p class="submit"><input class="button-primary" type="submit" value="提交" /></p>
			</form>
		</div>
		<?php
	}
}

/* 基本设置的字段 */
function weixin_robot_get_option_labels(){
	global $plugin_page;

	$sections					=	array();
	$option_group               =   'weixin-robot-basic-group';
    $option_name = $option_page =   'weixin-robot-basic';
    $field_validate				=	'weixin_robot_basic_validate';

    if($plugin_page == 'weixin-robot'){
    	$basic_section_fields = array(
			'weixin_token'					=> array('title'=>'微信 Token',		'type'=>'text'),
			'weixin_default'				=> array('title'=>'默认缩略图',		'type'=>'image'),
			'weixin_keyword_allow_length'	=> array('title'=>'搜索关键字最大长度','type'=>'text',		'description'=>'一个汉字算两个字节，一个英文单词算两个字节，空格不算，搜索多个关键字可以用空格分开！'),
			'weixin_count'					=> array('title'=>'返回结果最大条数',	'type'=>'text',		'description'=>'微信接口最多支持返回10个。'), 
			'weixin_disable_search'			=> array('title'=>'关闭搜索',			'type'=>'checkbox',	'description'=>'关闭搜索，则只有定义在自定义回复和内置回复的关键字有效，不会去搜索博客文章。'), 
			'weixin_disable_stats'			=> array('title'=>'屏蔽统计',			'type'=>'checkbox',	'description'=>'屏蔽统计之后，就无法统计用户发的信息和系统的回复。'), 
	    );

	    $app_section_fields = array(
			'weixin_app_id'					=> array('title'=>'微信AppID',		'type'=>'text',		'description'=>'设置自定义菜单的所需的 AppID，如果没申请，可不填！'),
			'weixin_app_secret'				=> array('title'=>'微信APPSecret',	'type'=>'text',		'description'=>'设置自定义菜单的所需的 APPSecret，如果没申请，可不填！'),
			'weixin_advanced_api'			=> array('title'=>'开启微信高级接口',	'type'=>'checkbox',	'description'=>'如果你申请了服务号的高级接口，才开启该功能，否则会出错'),
		);

	    $third_party_section_fields = array(
			'weixin_3rd_url'				=> array('title'=>'第三方微信平台链接',	'type'=>'url',		'description'=>'推荐使用<a href="http://weixin.digirepub.com/">微信共和</a>！'),
			'weixin_3rd_token'				=> array('title'=>'第三方微信平台 Token',	'type'=>'text',		'description'=>''),
			'weixin_3rd_search'				=> array('title'=>'第三方微信平台搜索',	'type'=>'checkbox',	'description'=>'所有在WordPress找不到内容的关键词都提交到第三方微信平台处理。')
		);

	    $credit_section_fields = array(
			'weixin_credit'					=> array('title'=>'开启微信积分系统',	'type'=>'checkbox',	'description'=>'开启积分系统，用户既可以签到和分享文章来获取积分'),
			'weixin_day_credit_limit'		=> array('title'=>'每日积分上限',		'type'=>'text',		'description'=>'设置每日积分上限，防止用户刷分。'),
			'weixin_checkin_credit'			=> array('title'=>'签到积分',			'type'=>'text',		'description'=>'用户点击签到菜单，或者发送签单之后获取的积分。'),
			'weixin_SendAppMessage_credit'	=> array('title'=>'发送给好友积分',	'type'=>'text',		'description'=>'用户每次发送文章给好友所能获取的积分，每篇文章只能获取一次。'),
			'weixin_ShareTimeline_credit'	=> array('title'=>'分享到朋友圈积分',	'type'=>'text',		'description'=>'用户每次分享文章到朋友圈所能获取的积分，每篇文章只能获取一次。'),
			'weixin_ShareWeibo_credit'		=> array('title'=>'分享到腾讯微博积分','type'=>'text',		'description'=>'用户每次分享文章到腾讯微博所能获取的积分，每篇文章只能获取一次。'),
			'weixin_share_notify'			=> array('title'=>'积分提醒',			'type'=>'checkbox',	'description'=>'分享成功获取积分之后是否提醒用户。'),
	    );

    	$sections = array(
	    	'basic'			=> array('title'=>'基本设置',		'fields'=>$basic_section_fields,			'callback'=>'weixin_robot_basic_section_callback' ),
	    	'app'			=> array('title'=>'接口设置',		'fields'=>$app_section_fields,				'callback'=>''),
	    	'3rd_party'		=> array('title'=>'第三方平台',	'fields'=>$third_party_section_fields,		'callback'=>''),
	    	'credit'		=> array('title'=>'积分设置',		'fields'=>$credit_section_fields,			'callback'=>'weixin_robot_credit_section_callback')
		);
		if(!empty($is_IIS)){
			unset($sections['credit']);
			unset($sections['basic']['weixin_disable_stats']);
		}
		$sections = apply_filters('weixin_setting',$sections);
    }elseif($plugin_page == 'weixin-robot-reply'){
    	if(isset($_GET['tab']) && $_GET['tab'] == 'advanced-reply'){
    		$advanced_reply_section_fields = array(
				'new'			=> array('title'=>'返回最新日志关键字',			'type'=>'text'),
				'rand'			=> array('title'=>'返回随机日志关键字',			'type'=>'text'),
				'hot'			=> array('title'=>'返回浏览最高日志关键字',		'type'=>'text',	'description'=>'博客必须首先安装 <a href="http://blog.wpjam.com/article/wp-postviews/">Postviews</a> 插件！'),
				'comment'		=> array('title'=>'返回留言最高日志关键字',		'type'=>'text'),
				'hot-7'			=> array('title'=>'返回7天内浏览最高日志关键字',	'type'=>'text',	'description'=>'博客必须首先安装 <a href="http://blog.wpjam.com/article/wp-postviews/">Postviews</a> 插件！'),
				'comment-7'		=> array('title'=>'返回7天内留言最高日志关键字',	'type'=>'text'),
			);

			$advanced_reply_section_fields = apply_filters('weixin_advanced_reply',$advanced_reply_section_fields);
	    	$sections = array(
		    	'advanced_reply'	=> array('title'=>'高级回复',		'fields'=>$advanced_reply_section_fields,	'callback'=>''),
		    );
    	}elseif(isset($_GET['tab']) && $_GET['tab'] == 'default-reply'){
	    	$default_reply_section_fields = array(
		    	'weixin_welcome'				=> array('title'=>'用户关注时',		'type'=>'textarea', 'rows'=>7),
				'weixin_wkd'					=> array('title'=>'多客服',			'type'=>'text', 	'description'=>'设置用户进入多客服系统直接咨询客户的关键字'),
				'weixin_enter'					=> array('title'=>'进入服务号',		'type'=>'textarea', 'rows'=>7,	'description'=>'用户进入微信服务号之后的默认回复，一天内只回复一次（你可以通过 <code>weixin_enter_time</code> 这个 filter 来更改时长）。<br />这个功能只有开通了高级接口的服务号才能使用，并且在用户确认允许公众号使用其地理位置才可使用。'),
				'weixin_not_found'				=> array('title'=>'搜索没有匹配时',	'type'=>'textarea', 'rows'=>5,	'description'=>'可以使用 [keyword] 代替相关的搜索关键字，留空则不回复！'),
				'weixin_keyword_too_long'		=> array('title'=>'发送的文本太长',	'type'=>'textarea',	'rows'=>5,	'description'=>'设置超过最大长度提示语，留空则不回复！'),
				'weixin_default_voice'			=> array('title'=>'发送语音',			'type'=>'textarea', 'rows'=>5,	'description'=>'设置语言的默认回复文本，留空则不回复！'),
		    	'weixin_default_location'		=> array('title'=>'发送位置',			'type'=>'textarea', 'rows'=>5,	'description'=>'设置位置的默认回复文本，留空则不回复！'),
		    	'weixin_default_image'			=> array('title'=>'发送图片',			'type'=>'textarea', 'rows'=>5,	'description'=>'设置图片的默认回复文本，留空则不回复！'),
		    	'weixin_default_link'			=> array('title'=>'发送链接',			'type'=>'textarea', 'rows'=>5,	'description'=>'设置链接的默认回复文本，留空则不回复！'),
		    );

			if(weixin_robot_get_setting('weixin_advanced_api') == '') {
				unset($default_reply_section_fields['weixin_wkd']);
				unset($default_reply_section_fields['weixin_enter']);
			}

    		$default_reply_section_fields = apply_filters('weixin_default_reply',	$default_reply_section_fields);
	    	$sections = array(
		    	'default_reply'	=> array('title'=>'默认回复',		'fields'=>$default_reply_section_fields,	'callback'=>''),
		    );
    	}	
    }

	return compact('option_group','option_name','option_page','sections','field_validate');
}

function weixin_robot_basic_section_callback(){
	echo '
<ol style="font-weight:bold;">
	<li><a href="http://blog.wpjam.com/m/weixin-robot-advanced-faq/">微信机器人高级版常见问题汇总</a>列出了你使用当中碰到的绝大多数问题。</li>
	<li>点击这里下载<a href="http://wpjam.com/go/weixin">微信机器人 WordPress 插件高级版使用说明</a>。每个大版本更新，使用文档都会更新，请重新下载。</li>
</ol>
';
}

function weixin_robot_credit_section_callback(){
	echo '
<p><strong>根据<a href="https://mp.weixin.qq.com/cgi-bin/readtemplate?t=business/faq_operation_tmpl&type=info&lang=zh_CN&token=">微信公众平台运营规范</a>，诱导分享行为（以奖励或其他方式，强制或诱导用户将消息分享至朋友圈的行为。奖励的方式包括但不限于：实物奖品、虚拟奖品（积分、信息）等。）一经发现将根据违规程度对该公众帐号采取相应的处理措施。所以使用的时候请注意尺度，<span style="color:red;">由此造成封号，结果由微信公众号运营者本人承担。</a></strong></p>
';
}

function weixin_robot_basic_validate( $weixin_robot_basic ) {

	$current = get_option( 'weixin-robot-basic' );

	if(isset($weixin_robot_basic['weixin_token'])){
		if ( !is_numeric( $weixin_robot_basic['weixin_keyword_allow_length'] ) ){
			$weixin_robot_basic['weixin_keyword_allow_length'] = $current['weixin_keyword_allow_length'];
			add_settings_error( 'weixin-robot-basic', 'invalid-int', '搜索关键字最大长度必须为数字。' );
		}

		if ( !is_numeric( $weixin_robot_basic['weixin_count'] ) ){
			$weixin_robot_basic['weixin_count'] = $current['weixin_count'];
			add_settings_error( 'weixin-robot-basic', 'invalid-int', '返回结果最大条数必须为数字。' );
		}elseif($weixin_robot_basic['weixin_count'] > 10){
			$weixin_robot_basic['weixin_count'] = 10;
			add_settings_error( 'weixin-robot-basic', 'invalid-int', '返回结果最大条数不能超过10。' );
		}

		$checkbox_keys = array('weixin_disable_stats','weixin_disable_search','weixin_credit','weixin_advanced_api','weixin_3rd_search','weixin_share_notify');

		foreach ($checkbox_keys as $checkbox_key) {
			if(empty($weixin_robot_basic[$checkbox_key])){ //checkbox 未选，Post 的时候 $_POST 中是没有的，
				$weixin_robot_basic[$checkbox_key] = 0;
			}
		}
	}

	weixin_robot_delete_transient_cache($echo = false);

	return wp_parse_args($weixin_robot_basic,$current);
}



function weixin_robot_datas_page() {
	?>
	<div class="wrap">
		<div id="icon-weixin-robot" class="icon-users icon32"><br></div>
		<h2>数据检测和清理</h2>
		<p>
			微信机器人 WordPress 插件高级版已经尽量做好了自动创建数据库和缓存的自动更新，但是还是会不可避免出现一些不可知的问题和异常<br />
			点击该页面会自动创建或者检测微信机器人所需的数据库表，和清理微信机器人高级版用到的缓存。
		</p>
		<p>
			<strong>所以建议每次升级或者安装附加组件之后，或者出现一些不可知的问题，请点击该页面</strong>：
		</p>
		<h3>数据表</h3>
		<ol>
		<?php 
		
		$weixin_tables = array('weixin_robot_create_table' => array('自定义回复','微信用户','微信用户积分','微信消息'));
		$weixin_tables = apply_filters('weixin_tables',$weixin_tables);

		?>
		<?php 
		foreach ($weixin_tables as $function => $names) {
			call_user_func($function);
			foreach ($names as $name) {
				echo '<li><strong>'.$name.'</strong>表已经创建</li>';	
			}	
		}

		global $wpdb;
		$sql = "DESCRIBE " . $wpdb->weixin_custom_replies . " 'match'";
		if($wpdb->query($sql) == 0){
			$sql = "ALTER TABLE  " . $wpdb->weixin_custom_replies . " ADD  `match` VARCHAR( 10 ) NOT NULL DEFAULT  'full' AFTER  `keyword`";
			$wpdb->query($sql);
			echo '<li><strong>自定义回复</strong>表已经已经升级</li>';
		}

		$sql = "DESCRIBE " . $wpdb->weixin_messages . " 'Ticket'";
		if($wpdb->query($sql) == 0){
			$sql = "ALTER TABLE  " . $wpdb->weixin_messages . " ADD  `Ticket` TEXT NOT NULL AFTER `Recognition`";
			$wpdb->query($sql);
		
			$sql = "ALTER TABLE  " . $wpdb->weixin_messages . " ADD  `ip` VARCHAR( 100 ) NOT NULL AFTER  `Ticket`";
			$wpdb->query($sql);

			echo '<li><strong>自定义回复</strong>表已经已经升级</li>';
		}

		$sql = "DESCRIBE " . $wpdb->weixin_users . " 'access_token'";
		if($wpdb->query($sql) == 0){

			$sql = "ALTER TABLE  " . $wpdb->weixin_users . " ADD  `access_token` VARCHAR( 255 )  NOT NULL AFTER  `headimgurl`";
			$wpdb->query($sql);

			$sql = "ALTER TABLE  " . $wpdb->weixin_users . " ADD  `expires_in` INT( 10 )  NOT NULL AFTER  `access_token`";
			$wpdb->query($sql);

			$sql = "ALTER TABLE  " . $wpdb->weixin_users . " ADD  `refresh_token` VARCHAR( 255 )  NOT NULL AFTER  `expires_in`";
			$wpdb->query($sql);

			$sql = "ALTER TABLE  " . $wpdb->weixin_users . " ADD  `privilege` TEXT NOT NULL AFTER  `refresh_token`";
			$wpdb->query($sql);
		}

		$sql = "DESCRIBE " . $wpdb->weixin_users . " 'unionid'";
		if($wpdb->query($sql) == 0){

			$sql = "ALTER TABLE  " . $wpdb->weixin_users . " ADD  `unionid` VARCHAR( 30 )  NOT NULL AFTER  `headimgurl`";
			$wpdb->query($sql);
		}

		$sql = "DESCRIBE " . $wpdb->weixin_users . " 'remark'";
		if($wpdb->query($sql) == 0){

			$sql = "ALTER TABLE  " . $wpdb->weixin_users . " ADD  `remark` VARCHAR( 30 )  NOT NULL AFTER  `headimgurl`";
			$wpdb->query($sql);

			echo '<li><strong>微信用户表</strong>表已经已经升级</li>';
		}

		?>
		</ol>
		<h3>缓存</h3>
		<?php weixin_robot_delete_transient_cache(); ?>
		</ol>
	</div>
	<?php		
}

function weixin_robot_delete_transient_cache($echo = true){
	$weixin_transient_caches = array(
		'自定义回复'			=> array('weixin_custom_keywords_full','weixin_custom_keywords_prefix'),
		'内置回复'			=> array('weixin_builtin_replies','weixin_builtin_replies_new'),
		'微信 Access Token '	=> array('weixin_robot_access_token'),
	);
	$weixin_transient_caches = apply_filters('weixin_transient_caches',$weixin_transient_caches);
	
	foreach ($weixin_transient_caches as $name => $cache_keys) {
		foreach ($cache_keys as $cache_key) {
			delete_transient($cache_key);
		}
		if($echo) echo '<li><strong>'.$name.'缓存</strong>已经清除</li>';
	}
}

// 用户列表

function weixin_robot_user_page(){
	global $wpdb, $plugin_page;
	
	global $wpdb;
	$current_page 		= isset($_GET['paged']) ? $_GET['paged'] : 1;
	$number_per_page	= 50;
	$start_count		= ($current_page-1)*$number_per_page;
	$limit				= 'LIMIT '.$start_count.','.$number_per_page;

	if(weixin_robot_get_setting('weixin_credit')){
		//$sql = "SELECT SQL_CALC_FOUND_ROWS wut.*, wct.credit FROM  $wpdb->weixin_users wut LEFT JOIN $wpdb->weixin_credits wct ON wut.openid = wct.weixin_openid WHERE  subscribe = '1' AND wct.id in (SELECT MAX( id ) FROM $wpdb->weixin_credits GROUP BY weixin_openid) ORDER BY wct.credit desc $limit ";
		$sql = "SELECT SQL_CALC_FOUND_ROWS wut.*, wct.credit FROM  $wpdb->weixin_users wut LEFT JOIN (SELECT * FROM $wpdb->weixin_credits ORDER BY id DESC) wct ON wut.openid = wct.weixin_openid WHERE  subscribe = '1' GROUP BY weixin_openid ORDER BY wct.credit desc $limit ";
	
	}else{
		$sql = "SELECT SQL_CALC_FOUND_ROWS wut.* FROM  $wpdb->weixin_users wut WHERE subscribe = '1' ORDER BY id DESC $limit";
	}

	$sql = apply_filters('weixin_user_admin_sql',$sql);

	if(isset($_GET['debug'])){
		echo $sql;
	}

	$weixin_users = $wpdb->get_results($sql);
	$total_count = $wpdb->get_var("SELECT FOUND_ROWS();");

?>
<div class="wrap">
	<div id="icon-weixin-robot" class="icon-users icon32"><br></div>
	<h2>微信用户列表</h2>
	<?php if($weixin_users) { ?>
	<table class="widefat" cellspacing="0">
		<thead>
			<tr>
				<th>微信 OpenID</th>
				<?php if(weixin_robot_get_setting('weixin_credit')){ ?>
				<th>积分</th>
				<?php } ?>
				<?php if(weixin_robot_get_setting('weixin_advanced_api')) {?>
				<th colspan="2">用户</th>
				<th>性别</th>
				<th>地址</th>
				<th>订阅时间</th>
				<?php }else{ ?>
				<th>姓名</th>
				<th>电话</th>
				<th>地址</th>
				<?php } ?>
				<?php do_action('weixin_user_admin_fileds');?>
				<th>详细</th>
			</tr>
		</thead>

		<tbody>
		<?php $alternate = '';?>
		<?php foreach($weixin_users as $weixin_user){ $alternate = $alternate?'':'alternate';?>
			<tr class="<?php echo $alternate;?>">
				<td><?php echo $weixin_user->openid; ?></td>
				<?php if(weixin_robot_get_setting('weixin_credit')){ ?>
				<td><?php echo $weixin_user->credit; ?></td>
				<?php } ?>
				<?php if(weixin_robot_get_setting('weixin_advanced_api')) {?>
				<td>
				<?php 
				$weixin_user_avatar = '';
				if(!empty($weixin_user->headimgurl)){
					$weixin_user_avatar = $weixin_user->headimgurl;
				?>
					<img src="<?php echo $weixin_user_avatar; ?>" width="32" />
				<?php }?>
				</td>
				<td><?php echo $weixin_user->nickname; ?></td>
				<td><?php if($weixin_user->sex == 1) { echo '男'; }else{ echo '女'; } ?></td>
				<td><?php echo $weixin_user->country.' '.$weixin_user->province.' '.$weixin_user->city; ?></td>
				<td><?php echo date( 'Y-m-d H:m:s', $weixin_user->subscribe_time+get_option('gmt_offset')*3600 ); ?></td>
				<?php }else{ ?>
				<td><?php echo $weixin_user->name; ?></td>
				<td><?php echo $weixin_user->phone; ?></td>
				<td><?php echo $weixin_user->address; ?></td>
				<?php } ?>
				<?php do_action('weixin_user_admin_details',$weixin_user);?>
				<td>
					<?php if(weixin_robot_get_setting('weixin_credit')){ ?><a href="<?php echo admin_url('admin.php?page=weixin-robot-credit&openid='.$weixin_user->openid)?>">积分历史</a> | <?php } ?>
					<a href="<?php echo admin_url('admin.php?page=weixin-robot-messages&openid='.$weixin_user->openid)?>">消息历史</a>
				</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
	<?php wpjam_admin_pagenavi($total_count,$number_per_page); ?>
	<?php } ?>
	
</div>
<?php 
}

// 积分记录
function weixin_robot_credit_page(){
	global $plugin_page, $current_user;

	if(isset($_POST['weixin_robot_credit_nonce']) && wp_verify_nonce($_POST['weixin_robot_credit_nonce'], 'weixin_robot' )){
		$weixin_openid	= stripslashes( trim( $_POST['weixin_openid'] ) );
		$credit_change	= stripslashes( trim( $_POST['credit_change'] ) );
		$note			= stripslashes( trim( $_POST['note'] ) );
		
		if( empty($weixin_openid) || empty($credit_change)){
			$err_msg = '微信 OpenID 和 积分不能为空';
		}elseif(weixin_robot_get_user($weixin_openid,'local') === false){
			$err_msg = '微信OpenID不存在';
		}elseif (!is_numeric($credit_change)) {
			$err_msg = '积分必须为数字';
		}

		if(empty($err_msg)){
			$args = array(
				'type'			=> 'manual', 
				'weixin_openid'	=> $weixin_openid,
				'operator_id'	=> $current_user->ID,
				'credit_change'	=> $credit_change,
				'exp_change'	=> 0,
				'note'			=> $note,
			);
			weixin_robot_add_credit($args);	
			$succeed_msg = '修改成功';
		}
		
	}

?>
	<div class="wrap">
		<div id="icon-weixin-robot" class="icon32"><br></div>
			<h2>
				<?php if(isset($_GET['action']) && $_GET['action'] == 'add'){ ?>
					手工修改积分
					<a href="<?php echo admin_url('admin.php?page='.$plugin_page); ?>" class="add-new-h2">返回列表</a>
				<?php } else { ?>
					微信积分记录 
					<a href="<?php echo admin_url('admin.php?page='.$plugin_page); ?>&amp;action=add" class="add-new-h2">手工修改</a>
				<?php } ?>
			</h2>

			<?php if(!empty($succeed_msg)){?>
			<div class="updated">
				<p><?php echo $succeed_msg;?></p>
			</div>
			<?php }?>
			<?php if(!empty($err_msg)){?>
			<div class="error" style="color:red;">
				<p>错误：<?php echo $err_msg;?></p>
			</div>
			<?php }?>
		<?php 
			if(isset($_GET['action']) && $_GET['action'] == 'add'){
				weixin_robot_credit_add();
			}else{
				weixin_robot_credit_list();
			}
		 ?>
	</div>
<?php
}

function weixin_robot_credit_add(){
	global $plugin_page;	
?>
<?php 
$form_fields = array(
	'weixin_openid'	=> array( 'title'=>'微信 OpenID',	'value'=>'',	'type'=>'text',		'description'=>''),
	'credit_change'	=> array( 'title'=>'积分',			'value'=>'',	'type'=>'text',		'description'=>''),
	'note'			=> array( 'title'=>'备注',			'value'=>'',	'type'=>'textarea',	'description'=>''),
);

?>
<form method="post" action="<?php echo admin_url('admin.php?page='.$plugin_page); ?>" enctype="multipart/form-data" id="form">
	<?php wpjam_admin_display_fields($form_fields); ?>
	<?php wp_nonce_field('weixin_robot','weixin_robot_credit_nonce'); ?>
	<p class="submit"><input class="button-primary" type="submit" value="手工修改" /></p>
</form>
<?php 
}

function weixin_robot_credit_list(){
	global $plugin_page, $succeed_msg,$plugin_page;

	global $wpdb;
	$current_page 		= isset($_GET['paged']) ? $_GET['paged'] : 1;
	$number_per_page	= 50;
	$start_count		= ($current_page-1)*$number_per_page;
	$limit				= 'LIMIT '.$start_count.','.$number_per_page;

	$where = '';
	if(isset($_GET['openid'])){
		$where = "AND wct.weixin_openid = '{$_GET['openid']}'";	
	}

    $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM $wpdb->weixin_credits as wct LEFT JOIN $wpdb->weixin_users wut ON wct.weixin_openid = wut.openid WHERE wut.subscribe = '1'  $where ORDER BY wct.id DESC $limit";

    $weixin_robot_credits = $wpdb->get_results($sql);
    
    $total_count = $wpdb->get_var("SELECT FOUND_ROWS();");
?>
	<?php if($weixin_robot_credits) { ?>
	<form action="<?php echo admin_url('admin.php?page='.$plugin_page); ?>" method="POST">
		<table class="widefat" cellspacing="0">
		<thead>
			<tr>
				<th>微信 OpenID</th>
				<th<?php if(weixin_robot_get_setting('weixin_advanced_api')) { echo ' colspan="2"'; }?>>用户</th>
				<th>积分</th>
				<th>变动</th>
				<th>积分类型</th>
				<th>时间</th>
				<th>备注</th>
			</tr>
		</thead>

		<tbody>
		<?php $alternate = '';?>
		<?php foreach($weixin_robot_credits as $weixin_robot_credit){ $alternate = $alternate?'':'alternate'; ?>
			<tr class="<?php echo $alternate;?>">
				<td><a href="<?php echo admin_url('admin.php?page='.$plugin_page.'&openid='.$weixin_robot_credit->weixin_openid)?>"><?php echo $weixin_robot_credit->weixin_openid; ?></a></td>
			<?php if(weixin_robot_get_setting('weixin_advanced_api')) {?>
				<?php if($weixin_robot_credit->subscribe){ ?>
				<td>
				<?php 
				$weixin_user_avatar = '';
				if(!empty($weixin_robot_credit->headimgurl)){
					$weixin_user_avatar = $weixin_robot_credit->headimgurl;
				?>
					<img src="<?php echo $weixin_user_avatar; ?>" width="32" />
				<?php }?>
				</td>
				<td><?php echo $weixin_robot_credit->nickname;?></td>
				<?php } else { ?>
				<td colspan="2"><span style="color:red">*取消关注*</td>
				<?php }?>
			<?php }elseif($weixin_robot_credit->name){ ?>
				<td><?php echo $weixin_robot_credit->name; ?></td>
			<?php }else{ ?>
				<td></td>
			<?php } ?>	
				<td><?php echo $weixin_robot_credit->credit; ?></td>
				<td><?php echo $weixin_robot_credit->credit_change; ?></td>
				<td><?php echo $weixin_robot_credit->type; ?>
				<?php if($weixin_robot_credit->operator_id){
					$operator_user = get_userdata($weixin_robot_credit->operator_id);
					echo '<br />操作人：'.$operator_user->display_name;
				}?></td>
				<td><?php echo $weixin_robot_credit->time; ?></td>
				<td><?php echo $weixin_robot_credit->note; ?></td>
			</tr>
		<?php } ?>
		</tbody>
		</table>
	</form>
	<?php wpjam_admin_pagenavi($total_count,$number_per_page); ?>
	<?php } else{ ?>
		<p>还没有积分历史记录</p>
	<?php } ?>
<?php
}

function weixin_robot_extends_page(){
	$weixin_extends = array();
	$weixin_extend_dir = WEIXIN_ROBOT_PLUGIN_DIR.'/extends';
	if (is_dir($weixin_extend_dir)) {
		if ($weixin_extend_handle = opendir($weixin_extend_dir)) {   
			while (($weixin_extend_file = readdir($weixin_extend_handle)) !== false) {
				if ($weixin_extend_file!="." && $weixin_extend_file!=".." && is_file($weixin_extend_dir.'/'.$weixin_extend_file)) {
					if(pathinfo($weixin_extend_file, PATHINFO_EXTENSION) == 'php'){
						if($data = get_plugin_data($weixin_extend_dir.'/'.$weixin_extend_file)){
							$weixin_extends[$weixin_extend_file] = $data;
						}
					}
				}
			}   
			closedir($weixin_extend_handle);   
		}   
	}

	?>
	<table class="wp-list-table widefat plugins" cellspacing="0">
	<thead>
	<tr>
		<th scope="col" id="cb" class="manage-column column-cb check-column" style="">
			<label class="screen-reader-text" for="cb-select-all-1">全选</label><input id="cb-select-all-1" type="checkbox">
		</th>
		<th scope="col" id="name" class="manage-column column-name" style="">扩展</th>
		<th scope="col" id="description" class="manage-column column-description" style="">描述</th>	
	</tr>
	</thead>

	<tfoot>
	<tr>
		<th scope="col" class="manage-column column-cb check-column" style="">
			<label class="screen-reader-text" for="cb-select-all-2">全选</label><input id="cb-select-all-2" type="checkbox">
			</th>
		<th scope="col" class="manage-column column-name" style="">扩展</th>
		<th scope="col" class="manage-column column-description" style="">描述</th>	
	</tr>
	</tfoot>

	<tbody id="the-list">
	<?php foreach ($weixin_extends as $file => $data) {?>
		<?php if($data['Name']){?>
		<tr id="<?php echo urlencode($data['Name']);?>" class="active">
			<th scope="row" class="check-column">
				<label class="screen-reader-text" for="checkbox_c3be8bd167d586a7392fe165136df594">选择<?php echo $data['Name'];?></label>
				<input type="checkbox" name="checked[]" value="<?php echo $file;?>" id="checkbox_c3be8bd167d586a7392fe165136df594">
			</th>
			<td class="plugin-title"><strong><?php echo $data['Name']?></strong>
			<div class="row-actions visible"><span class="deactivate">
			<a href="plugins.php?action=deactivate&amp;plugin=admin-color-schemes%2Fadmin-color-schemes.php&amp;plugin_status=all&amp;paged=1&amp;s&amp;_wpnonce=070de3970a" title="停用该插件">停用</a></span></div>
			<div class="row-actions visible"><span class="activate"><a href="plugins.php?action=activate&amp;plugin=order-categories%2Fcategory-order.php&amp;plugin_status=all&amp;paged=1&amp;s&amp;_wpnonce=81b6e19778" title="启用这个插件" class="edit">启用</a></span></div>
			</td>
			<td class="column-description desc">
				<div class="plugin-description"><?php echo wpautop($data['Description']);?></div>
				<div class="active second plugin-version-author-uri"><?php echo $data['Version'];?>版本 
				| 作者为<?php echo $data['Author'];?> 
				| <a href="<?php echo $data['PluginURI'];?>" title="访问插件主页">访问插件主页</a></div>
			</td>
		</tr>
		<?php } ?>
	<?php }?>
	</tbody>
</table>
	<?php
}
